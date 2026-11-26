<?php
declare(strict_types=1);

chdir(dirname(__DIR__, 2));
$checks = 0;
function gitCommand(array $arguments): array
{
    $process = proc_open(array_merge(['git'], $arguments),
        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) {
        throw new RuntimeException('Git is unavailable.');
    }
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    return ['code' => proc_close($process), 'output' => trim($output), 'errors' => trim($errors)];
}
function audit(bool $condition, string $message): void
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException('FAIL: ' . $message);
    }
    $checks++;
    echo "PASS: $message\n";
}
// Original phase-2 guards remain the default; fresh clones and CI use --portable.
$portable = in_array('--portable', $argv, true);
$initialRefs = gitCommand(['for-each-ref', '--format=%(refname) %(objectname)', 'refs/heads', 'refs/remotes']);
if ($portable) {
    audit(gitCommand(['rev-parse', '--is-inside-work-tree'])['output'] === 'true', 'Git worktree available (portable audit)');
} else {
    audit(gitCommand(['branch', '--show-current'])['output'] === 'chore/mvc-foundation', 'Required foundation branch');
}
$sensitive = ['.env', '.env.local', '.env.production', 'config/database.php', 'vendor/autoload.php',
    'node_modules/example/index.js', 'composer.phar', 'public/uploads/photo.jpg', 'storage/uploads/file.pdf',
    'storage/logs/app.log', 'storage/cache/sessions/sample', 'file.tmp', 'file.temp', 'file.bak',
    'file.backup', '.vscode/settings.json', '.idea/workspace.xml', '.DS_Store', 'Thumbs.db', 'Desktop.ini'];
foreach ($sensitive as $path) {
    audit(gitCommand(['check-ignore', '-q', '--', $path])['code'] === 0, 'Ignore sensitive/generated path ' . $path);
}
foreach (['.env.example', 'config/database.example.php', 'storage/logs/.gitkeep',
    'storage/cache/.gitkeep', 'database/migrations/.gitkeep', 'database/seeds/.gitkeep'] as $path) {
    audit(gitCommand(['check-ignore', '-q', '--', $path])['code'] === 1, 'Keep example/placeholder ' . $path);
}
$tracked = explode("\n", gitCommand(['ls-files'])['output']);
$forbidden = array_filter($tracked, static fn (string $path): bool =>
    (preg_match('~(^|/)\.env($|\.)~', $path) && !str_ends_with($path, '.env.example'))
    || preg_match('~^config/database\.php$|^(vendor|node_modules)/|^storage/(logs|cache)/[^.].*|^public/uploads/.*\.(php|phtml)$~', $path));
audit($forbidden === [], 'Tracked files contain no forbidden sensitive/generated paths');
audit(gitCommand(['diff', '--check'])['code'] === 0, 'Git tracked diff whitespace');
$candidates = explode("\n", gitCommand(['ls-files', '--cached', '--others', '--exclude-standard'])['output']);
$secretPattern = '~-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----|AKIA[0-9A-Z]{16}|gh[pousr]_[A-Za-z0-9]{30,}|github_pat_[A-Za-z0-9_]{20,}|(?:mysql|https?)://[^/\s:]+:[^/\s@]+@~';
foreach ($candidates as $path) {
    if (preg_match($secretPattern, (string) file_get_contents($path))) {
        throw new RuntimeException('Potential credential pattern in candidate file: ' . $path);
    }
}
audit(true, 'Candidate source scan: no private-key/cloud-token/credential-URL patterns found');
$empty = tempnam(sys_get_temp_dir(), 'vc-empty');
try {
    foreach ($candidates as $path) {
        $result = gitCommand(['diff', '--no-index', '--check', '--', $empty, $path]);
        // --no-index implies --exit-code: 1 can mean content added, not a whitespace error.
        if (!in_array($result['code'], [0, 1], true) || $result['output'] !== '' || $result['errors'] !== '') {
            throw new RuntimeException('Whitespace check failed: ' . $path . ' ' . $result['output'] . $result['errors']);
        }
    }
    audit(true, 'All ' . count($candidates) . ' candidate files pass whitespace including untracked');
} finally {
    unlink($empty);
}
if (!$portable) {
    $refs = explode("\n", gitCommand(['for-each-ref', '--format=%(objectname)', 'refs/heads/main', 'refs/heads/develop',
        'refs/remotes/origin/PHUONG', 'refs/remotes/origin/HUY', 'refs/remotes/origin/QUY'])['output']);
    audit(count($refs) === 5 && count(array_unique($refs)) === 1 && $refs[0] === '7ccc69a9f939ba10bf8ba868a1cc8cb30cb34a00',
        'Protected refs unchanged from phase 2 baseline');
} else {
    $finalRefs = gitCommand(['for-each-ref', '--format=%(refname) %(objectname)', 'refs/heads', 'refs/remotes']);
    audit($initialRefs['code'] === 0 && $finalRefs['code'] === 0 && $initialRefs['output'] === $finalRefs['output'],
        'Branch and remote refs unchanged during portable audit');
}
echo "TOTAL GIT AUDIT: $checks checks passed.\n";
