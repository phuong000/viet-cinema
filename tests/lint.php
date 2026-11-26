<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$files = [$root . '/bootstrap.php'];
foreach (['app', 'config', 'public', 'routes', 'tests'] as $directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $directory));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
}
foreach ($files as $file) {
    $process = proc_open([PHP_BINARY, '-l', $file], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) {
        throw new RuntimeException('Cannot run PHP lint.');
    }
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    if (proc_close($process) !== 0) {
        throw new RuntimeException($output);
    }
    echo 'PASS PHP syntax: ' . str_replace('\\', '/', substr($file, strlen($root) + 1)) . "\n";
}
echo 'TOTAL LINT: ' . count($files) . " files passed.\n";
