'use strict';

// Uses already installed runtimes only. No downloads, service control or global config writes.
// node tests/Acceptance/local.cjs <playwright-directory> <chrome.exe> <apache-directory> <php-directory> [evidence-label]
const fs = require('node:fs');
const path = require('node:path');
const net = require('node:net');
const { spawn, spawnSync } = require('node:child_process');
const [playwrightDirectory, chrome, apache, php, label = 'final'] = process.argv.slice(2);
if (![playwrightDirectory, chrome, apache, php].every(Boolean) || !/^[a-z0-9-]+$/.test(label)) {
    throw new Error('Supply installed Playwright, Chrome, Apache and PHP paths plus an optional evidence label.');
}
const { chromium, request } = require(path.resolve(playwrightDirectory));
const root = path.resolve(__dirname, '../..');
const scratch = path.join(root, 'storage/cache/acceptance');
const output = path.join(root, 'docs/evidence/acceptance', label);
fs.mkdirSync(scratch, { recursive: true });
fs.mkdirSync(output, { recursive: true });
const slash = value => value.replaceAll('\\', '/');
const result = { date: new Date().toISOString(), checks: [], responsive: [], failures: [] };
function check(ok, name) {
    result.checks.push({ name, pass: !!ok });
    if (!ok) result.failures.push(name);
    console.log(`${ok ? 'PASS' : 'FAIL'}: ${name}`);
}
async function port() {
    const server = net.createServer();
    await new Promise((resolve, reject) => server.once('error', reject).listen(0, '127.0.0.1', resolve));
    const selected = server.address().port;
    await new Promise(resolve => server.close(resolve));
    return selected;
}
async function ready(url, processHandle) {
    for (let i = 0; i < 50; i++) {
        if (processHandle.exitCode !== null) throw new Error(`Server exited: ${processHandle.exitCode}`);
        try { if ((await fetch(url)).ok) return; } catch {}
        await new Promise(resolve => setTimeout(resolve, 100));
    }
    throw new Error(`Server did not start: ${url}`);
}
const env = { ...process.env, DB_DSN: '', SESSION_SECURE: 'false', APP_ENV: 'production',
    PATH: `${php};${path.join(apache, 'bin')};${process.env.PATH}` };
const children = [];
const streams = [];
const probes = [];
function start(executable, args, logName) {
    const log = fs.openSync(path.join(scratch, logName), 'a');
    streams.push(log);
    const child = spawn(executable, args, { cwd: root, env, windowsHide: true, stdio: ['ignore', log, log] });
    children.push(child);
    return child;
}
let browser;
let api;
(async () => {
    const apachePort = await port();
    const base = `http://127.0.0.1:${apachePort}`;
    const config = path.join(scratch, 'httpd.conf');
    fs.writeFileSync(config, `ServerRoot "${slash(apache)}"
Listen 127.0.0.1:${apachePort}
ServerName 127.0.0.1
PidFile "${slash(path.join(scratch, 'httpd.pid'))}"
ErrorLog "${slash(path.join(scratch, 'apache-error.log'))}"
LogLevel warn
ServerTokens Prod
ServerSignature Off
LoadModule authz_core_module modules/mod_authz_core.so
LoadModule authz_host_module modules/mod_authz_host.so
LoadModule dir_module modules/mod_dir.so
LoadModule mime_module modules/mod_mime.so
LoadModule rewrite_module modules/mod_rewrite.so
LoadModule env_module modules/mod_env.so
LoadModule alias_module modules/mod_alias.so
LoadFile "${slash(path.join(php, 'php8ts.dll'))}"
LoadModule php_module "${slash(path.join(php, 'php8apache2_4.dll'))}"
PHPIniDir "${slash(php)}"
TypesConfig conf/mime.types
DirectoryIndex index.php
DocumentRoot "${slash(path.join(root, 'public'))}"
<Directory />
    AllowOverride None
    Require all denied
</Directory>
<Directory "${slash(path.join(root, 'public'))}">
    Options -Indexes
    AllowOverride All
    Require all granted
</Directory>
<FilesMatch "\\.php$">
    SetHandler application/x-httpd-php
</FilesMatch>
# A test-only exception fixture; this alias exists only in this isolated process.
Alias /throw "${slash(path.join(root, 'tests/Integration/router.php'))}"
<Directory "${slash(path.join(root, 'tests/Integration'))}">
    AllowOverride None
    Require local
</Directory>
SetEnv DB_DSN ""
SetEnv SESSION_SECURE false
php_admin_flag display_errors Off
php_admin_flag log_errors On
php_admin_value error_log "${slash(path.join(scratch, 'php-error.log'))}"
`);
    const syntax = spawnSync(path.join(apache, 'bin/httpd.exe'), ['-t', '-f', config], { env, windowsHide: true, encoding: 'utf8' });
    result.apacheSyntax = { status: syntax.status, output: syntax.stdout + syntax.stderr };
    check(syntax.status === 0, 'Isolated Apache config syntax');
    if (syntax.status !== 0) throw new Error(result.apacheSyntax.output);
    const apacheProcess = start(path.join(apache, 'bin/httpd.exe'), ['-X', '-f', config], 'apache-process.log');
    await ready(base + '/health', apacheProcess);
    result.server = { type: 'Apache mod_php', address: base, documentRoot: 'public/', globalConfigChanged: false };
    api = await request.newContext({ baseURL: base });
    const pages = [['home', '/', 200], ['movies', '/movies', 200], ['movie', '/movies/sample', 200],
        ['showtimes', '/showtimes', 200], ['login', '/login', 200], ['register', '/register', 200],
        ['booking', '/booking', 200], ['admin-demo', '/admin/demo', 200], ['error', '/missing', 404]];
    for (const [, url, status] of pages) {
        const response = await api.get(url);
        const body = await response.text();
        check(response.status() === status && body.includes('<html lang="vi">'), `Apache route ${url} => ${status}`);
        check(!body.includes('Stack trace') && !body.includes('<?php') && !body.includes(root), `Apache response no source/trace ${url}`);
    }
    for (const url of ['/app/', '/config/', '/database/', '/storage/', '/.env', '/app/Core/Database.php',
        '/config/database.example.php', '/database/README.md', '/storage/logs/application.log', '/.git/config']) {
        const response = await api.get(url);
        check(response.status() === 404 && !(await response.text()).includes('<?php'), `Apache private ${url}`);
    }
    for (const [url, type] of [['/assets/css/app.css', 'text/css'], ['/assets/js/app.js', 'text/javascript']]) {
        const response = await api.get(url);
        const mime = response.headers()['content-type'].split(';')[0];
        check(response.status() === 200 && (mime === type || (type === 'text/javascript' && mime === 'application/javascript')), `Apache asset ${url}`);
    }
    check((await api.get('/admin')).status() === 403, 'Apache real admin denied');
    const wrongMethod = await api.post('/');
    check(wrongMethod.status() === 405 && wrongMethod.headers().allow === 'GET, HEAD', 'Apache 405 and Allow');
    check((await api.get('/bad%ZZ')).status() === 400, 'Apache malformed URI 400');
    const head = await api.head('/');
    check(head.status() === 200 && (await head.body()).length === 0, 'Apache HEAD empty body');
    for (const url of ['/login', '/register']) {
        const form = await api.get(url);
        const token = (await form.text()).match(/name="_token" value="([a-f0-9]{64})"/)?.[1];
        const sessionCookie = (await api.storageState()).cookies.find(cookie => cookie.name === 'viet_cinema_session');
        check(sessionCookie?.httpOnly && sessionCookie?.sameSite === 'Lax' && !sessionCookie?.secure, `Apache cookie flags ${url}`);
        check((await api.post(url, { form: { email: 'test@example.com' } })).status() === 403, `Apache missing CSRF ${url}`);
        check((await api.post(url, { form: { _token: 'bad' } })).status() === 403, `Apache invalid CSRF ${url}`);
        check((await api.post(url, { form: { _token: token, email: 'bad', password: 'x' } })).status() === 422, `Apache validation ${url}`);
        const valid = await api.post(url, { form: { _token: token, email: 'test@example.com', password: 'demo-only-123' } });
        const body = await valid.text();
        check(valid.status() === 200 && body.includes('alert-success') && !body.includes('demo-only-123'), `Apache session and valid CSRF ${url}`);
    }
    check((await api.get('/admin')).status() === 403, 'Apache demo forms grant no admin');
    const error = await api.get('/throw');
    const errorBody = await error.text();
    check(error.status() === 500 && !errorBody.includes('PRIVATE_SENTINEL') && !errorBody.includes('Stack trace')
        && !errorBody.includes(root), 'Apache fixture 500 hides details');
    const probe = path.join(root, 'public/assets', `acceptance-${Date.now()}.php`);
    probes.push(probe);
    fs.writeFileSync(probe, "<?php echo 'EXECUTION_SENTINEL';");
    const blockedProbe = await api.get('/assets/' + path.basename(probe));
    check(blockedProbe.status() === 404 && !(await blockedProbe.text()).includes('EXECUTION_SENTINEL'), 'Apache PHP asset cannot execute or leak');
    browser = await chromium.launch({ executablePath: path.resolve(chrome), headless: true });
    result.browser = { name: 'Google Chrome headless', version: browser.version(), playwright: require(path.join(path.resolve(playwrightDirectory), 'package.json')).version };
    for (const [width, height] of [[360, 800], [390, 844], [768, 1024], [1366, 768], [1920, 1080]]) {
        const viewport = `${width}x${height}`;
        const folder = path.join(output, viewport);
        fs.mkdirSync(folder, { recursive: true });
        const context = await browser.newContext({ viewport: { width, height }, deviceScaleFactor: 1, hasTouch: width < 800 });
        for (const [name, url, status] of pages) {
            const page = await context.newPage();
            const errors = [];
            const failedRequests = [];
            const resources = [];
            page.on('pageerror', error => errors.push(error.message));
            page.on('requestfailed', req => failedRequests.push({ url: req.url(), error: req.failure()?.errorText }));
            page.on('response', res => { if (['stylesheet', 'script', 'image'].includes(res.request().resourceType())) resources.push({ path: new URL(res.url()).pathname, status: res.status() }); });
            const response = await page.goto(base + url, { waitUntil: 'networkidle' });
            const metrics = await page.evaluate(() => {
                const visible = el => { const r = el.getBoundingClientRect(); const s = getComputedStyle(el); return r.width > 0 && r.height > 0 && s.visibility !== 'hidden' && s.display !== 'none'; };
                const outside = [...document.querySelectorAll('body *')].filter(el => {
                    if (el.closest('[aria-hidden="true"]') || el.classList.contains('skip-link') || !visible(el)) return false;
                    const r = el.getBoundingClientRect();
                    return r.left < -1 || r.right > innerWidth + 1;
                }).map(el => `${el.tagName}.${el.className}`);
                return { width: innerWidth, height: innerHeight, scrollWidth: document.documentElement.scrollWidth,
                    clientWidth: document.documentElement.clientWidth, outside,
                    header: visible(document.querySelector('header')), footer: visible(document.querySelector('footer')),
                    main: visible(document.querySelector('main')), styled: getComputedStyle(document.body).backgroundColor === 'rgb(16, 16, 20)' };
            });
            let interactions = true;
            try {
                // Trial clicks verify scrolling, hit targets and overlay interception on every visible link/button/input.
                for (const target of await page.locator('a:not(.skip-link), button, input:not([type=hidden])').all()) {
                    await target.click({ trial: true, timeout: 2500 });
                }
                await page.locator('nav a[href="/movies"]').click();
                await page.waitForURL(base + '/movies');
                await page.goto(base + url, { waitUntil: 'networkidle' });
                if (name === 'login' || name === 'register') {
                    await page.locator('#email').fill('test@example.com');
                    await page.locator('#password').fill('demo-only-123');
                    await page.locator('[data-password-toggle]').click();
                    if (await page.locator('#password').getAttribute('type') !== 'text') throw new Error('Password toggle did not reveal');
                    await page.locator('[data-password-toggle]').click();
                    await Promise.all([page.waitForNavigation(), page.locator('button[type=submit]').click()]);
                    if (!await page.locator('.alert-success').isVisible()) throw new Error('Form did not reach placeholder');
                    await page.goto(base + url, { waitUntil: 'networkidle' });
                }
                await page.locator('footer a[href="/admin/demo"]').scrollIntoViewIfNeeded();
                await page.locator('footer a[href="/admin/demo"]').click({ trial: true });
                await page.keyboard.press('Control+Home');
            } catch (error) { interactions = false; errors.push(`Interaction: ${error.message}`); }
            await page.screenshot({ path: path.join(folder, `${name}.png`), fullPage: true });
            const row = { viewport, page: name, path: url, status: response.status(), metrics, interactions, errors, failedRequests, resources };
            row.pass = row.status === status && metrics.scrollWidth <= metrics.clientWidth && metrics.outside.length === 0
                && metrics.header && metrics.footer && metrics.main && metrics.styled && interactions && errors.length === 0
                && failedRequests.length === 0 && resources.length > 0 && resources.every(r => r.status === 200);
            result.responsive.push(row);
            check(row.pass, `Responsive ${viewport} ${url}`);
            fs.writeFileSync(path.join(folder, `${name}.json`), JSON.stringify(row, null, 2) + '\n');
            await page.close();
        }
        await context.close();
    }
})().catch(error => { result.failures.push(error.stack); console.error(error); }).finally(async () => {
    if (browser) await browser.close();
    if (api) await api.dispose();
    // Only handles created by this runner, not other services or browser profiles.
    for (const child of children.reverse()) {
        if (child.exitCode === null) {
            const exited = new Promise(resolve => child.once('exit', resolve));
            child.kill();
            await exited;
        }
    }
    for (const descriptor of streams) fs.closeSync(descriptor);
    for (const probe of probes) fs.unlinkSync(probe);
    result.childProcessesStopped = children.every(child => child.exitCode !== null || child.signalCode !== null);
    fs.writeFileSync(path.join(output, 'results.json'), JSON.stringify(result, null, 2) + '\n');
    console.log(`TOTAL ACCEPTANCE: ${result.checks.filter(c => c.pass).length}/${result.checks.length}; failures ${result.failures.length}; children stopped ${result.childProcessesStopped}`);
    process.exitCode = result.failures.length ? 1 : 0;
});
