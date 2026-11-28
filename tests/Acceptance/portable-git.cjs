'use strict';

// Regression for portable Git auditing without touching this repository's index or refs.
const fs = require('node:fs');
const path = require('node:path');
const { spawnSync } = require('node:child_process');
const php = process.argv[2];
if (!php) throw new Error('Supply an existing PHP executable.');
const root = path.resolve(__dirname, '../..');
fs.mkdirSync(path.join(root, 'storage/cache/acceptance'), { recursive: true });
const fixture = fs.mkdtempSync(path.join(root, 'storage/cache/acceptance/portable-'));
fs.mkdirSync(path.join(fixture, 'tests/Integration'), { recursive: true });
for (const file of ['.gitignore', '.gitattributes', 'tests/Integration/git-audit.php']) {
    fs.copyFileSync(path.join(root, file), path.join(fixture, file));
}
function run(exe, args) {
    const result = spawnSync(exe, args, { cwd: fixture, windowsHide: true, encoding: 'utf8' });
    return { code: result.status, stdout: result.stdout, stderr: result.stderr };
}
const init = run('git', ['init', '--quiet', '--initial-branch=acceptance-fixture']);
if (init.code !== 0) throw new Error(init.stderr);
const portable = run(php, ['tests/Integration/git-audit.php', '--portable']);
const strict = run(php, ['tests/Integration/git-audit.php']);
console.log(portable.stdout);
const preserved = strict.code !== 0 && (strict.stdout + strict.stderr).includes('Required foundation branch');
console.log(`PASS original strict branch guard preserved: ${preserved}`);
console.log(`PASS portable audit without historical refs on another branch: ${portable.code === 0}`);
if (!preserved || portable.code !== 0) {
    console.error(portable.stderr, strict.stderr);
    process.exitCode = 1;
}
