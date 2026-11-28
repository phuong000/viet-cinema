<?php
declare(strict_types=1);

// Runs local PHP child servers and curl requests. Never opens a database connection.
if (!extension_loaded('curl')) {
    fwrite(STDERR, "BLOCKED: ext-curl is required for HTTP tests.\n");
    exit(2);
}
$root = dirname(__DIR__, 2);
$processes = [];
$cookies = [];
$checks = 0;
$phpProbe = null;
function verify(bool $condition, string $message): void
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException('FAIL: ' . $message);
    }
    $checks++;
    echo "PASS: $message\n";
}
function request(string $base, string $path, string $method = 'GET', ?array $data = null, ?string $cookie = null): array
{
    $curl = curl_init($base . $path);
    $headers = [];
    $options = [CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_TIMEOUT => 5, CURLOPT_PROXY => '', CURLOPT_PATH_AS_IS => true,
        CURLOPT_HEADERFUNCTION => static function ($handle, string $line) use (&$headers): int {
            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                $headers[strtolower($parts[0])] = trim($parts[1]);
            }
            return strlen($line);
        }];
    if ($method === 'HEAD') {
        $options[CURLOPT_NOBODY] = true;
    }
    if ($data !== null) {
        $options[CURLOPT_POSTFIELDS] = http_build_query($data);
        $options[CURLOPT_HTTPHEADER] = ['Content-Type: application/x-www-form-urlencoded'];
    }
    if ($cookie !== null) {
        $options[CURLOPT_COOKIEFILE] = $cookie;
        $options[CURLOPT_COOKIEJAR] = $cookie;
    }
    curl_setopt_array($curl, $options);
    $body = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    return ['status' => $status, 'body' => $body === false ? '' : $body, 'headers' => $headers, 'error' => $error];
}
function server(string $router, bool $secure = false): string
{
    global $root, $processes;
    // Let the OS select an available local port, then release it for PHP.
    $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
    if ($socket === false) {
        throw new RuntimeException($error);
    }
    $address = stream_socket_get_name($socket, false);
    fclose($socket);
    $base = 'http://' . $address;
    $environment = getenv();
    $environment['APP_ENV'] = 'production';
    $environment['DB_DSN'] = ''; // Even a developer's local .env cannot trigger a DB connection.
    $environment['SESSION_SECURE'] = 'false';
    $environment['VC_TEST_SECURE'] = $secure ? 'true' : 'false';
    $process = proc_open([PHP_BINARY, '-S', $address, '-t', 'public', $router],
        [0 => ['pipe', 'r'], 1 => ['file', $root . '/storage/logs/http-server.log', 'a'],
            2 => ['file', $root . '/storage/logs/http-server.log', 'a']], $pipes, $root, $environment);
    if (!is_resource($process)) {
        throw new RuntimeException('Cannot start HTTP server.');
    }
    fclose($pipes[0]);
    $processes[] = $process;
    for ($i = 0; $i < 40; $i++) {
        if (request($base, '/health')['status'] !== 0) {
            return $base;
        }
        usleep(100000);
    }
    throw new RuntimeException('Local server did not become ready.');
}
try {
    $base = server('public/index.php');
    $cookie = tempnam(sys_get_temp_dir(), 'vc-http');
    $foreignCookie = tempnam(sys_get_temp_dir(), 'vc-other');
    $cookies = [$cookie, $foreignCookie];
    $home = request($base, '/', 'GET', null, $cookie);
    verify($home['status'] === 200 && str_contains($home['body'], 'Viet Cinema'), 'REGRESSION HTTP home 200 without database');
    $health = request($base, '/health?probe=1');
    verify($health['status'] === 200 && json_decode($health['body'], true) === ['status' => 'ok'], 'REGRESSION HTTP health query JSON');
    $head = request($base, '/', 'HEAD');
    verify($head['status'] === 200 && $head['body'] === '', 'REGRESSION HTTP HEAD has empty body');
    verify(request($base, '/missing')['status'] === 404, 'REGRESSION HTTP unknown route 404');
    $method = request($base, '/', 'POST');
    verify($method['status'] === 405 && $method['headers']['allow'] === 'GET, HEAD', 'REGRESSION HTTP POST home 405 and Allow');
    verify(request($base, '/.env')['status'] === 404, 'REGRESSION HTTP env inaccessible');
    echo "HTTP REGRESSION: 6/6 passed.\n";
    foreach (['/movies', '/movies/sample', '/showtimes', '/login', '/register', '/booking', '/admin/demo'] as $path) {
        verify(request($base, $path)['status'] === 200, 'HTTP placeholder ' . $path);
    }
    verify(request($base, '/admin')['status'] === 403, 'HTTP real admin denied');
    verify(request($base, '/movies/unknown')['status'] === 404, 'HTTP unknown movie 404');
    verify(request($base, '/bad%ZZ')['status'] === 400, 'HTTP malformed URL 400');
    verify(request($base, '/%2e%2e/config/database.php')['status'] === 400, 'HTTP encoded traversal 400');
    verify(request($base, '/%5cconfig')['status'] === 400, 'HTTP Windows separator rejected');
    foreach (['/config/database.example.php', '/app/Core/Database.php', '/database/README.md',
        '/storage/cache/.gitkeep', '/storage/logs/application.log', '/bootstrap.php', '/composer.json',
        '/tests/Integration/router.php', '/.git/config', '/.env.local'] as $path) {
        $private = request($base, $path);
        verify($private['status'] === 404 && !str_contains($private['body'], '<?php'), 'HTTP private file blocked ' . $path);
    }
    foreach (['/assets/css/app.css' => 'text/css', '/assets/js/app.js' => 'text/javascript'] as $path => $type) {
        $asset = request($base, $path);
        verify($asset['status'] === 200 && str_starts_with($asset['headers']['content-type'], $type), 'HTTP static asset ' . $path);
    }
    verify(request($base, '/assets/css/app.css', 'POST')['status'] === 405, 'HTTP static asset POST rejected');
    $phpProbe = $root . '/public/assets/' . bin2hex(random_bytes(8)) . '.php';
    file_put_contents($phpProbe, "<?php echo 'EXECUTION_SENTINEL';");
    $probe = request($base, '/assets/' . basename($phpProbe));
    verify($probe['status'] === 404 && !str_contains($probe['body'], 'EXECUTION_SENTINEL'), 'HTTP PHP file in assets cannot execute or leak source');
    $cookieHeader = strtolower($home['headers']['set-cookie'] ?? '');
    verify(str_contains($cookieHeader, 'httponly') && str_contains($cookieHeader, 'samesite=lax')
        && !str_contains($cookieHeader, '; secure'), 'HTTP actual local session cookie flags');
    verify(($home['headers']['x-content-type-options'] ?? '') === 'nosniff'
        && str_contains($home['headers']['content-security-policy'] ?? '', "frame-ancestors 'none'"), 'HTTP security headers');
    $login = request($base, '/login', 'GET', null, $cookie);
    preg_match('/name="_token" value="([a-f0-9]{64})"/', $login['body'], $matches);
    $token = $matches[1] ?? '';
    verify(strlen($token) === 64, 'HTTP form contains CSRF token');
    verify(request($base, '/login', 'POST', ['email' => 'test@example.com'], $cookie)['status'] === 403, 'HTTP missing CSRF rejected');
    verify(request($base, '/login', 'POST', ['_token' => 'bad'], $cookie)['status'] === 403, 'HTTP invalid CSRF rejected');
    verify(request($base, '/login?_token=' . $token, 'POST', [], $cookie)['status'] === 403, 'HTTP query-string CSRF rejected');
    verify(request($base, '/login', 'POST', ['_token' => [$token]], $cookie)['status'] === 403, 'HTTP CSRF array rejected');
    verify(request($base, '/login', 'POST', ['_token' => $token], $foreignCookie)['status'] === 403, 'HTTP cross-session CSRF rejected');
    $invalid = request($base, '/login', 'POST', ['_token' => $token, 'email' => 'bad', 'password' => 'x'], $cookie);
    verify($invalid['status'] === 422 && str_contains($invalid['body'], 'role="alert"'), 'HTTP validation errors');
    $data = ['_token' => $token, 'email' => 'test@example.com', 'password' => 'demo-only-123'];
    $valid = request($base, '/login', 'POST', $data, $cookie);
    verify($valid['status'] === 200 && str_contains($valid['body'], 'alert-success'), 'HTTP valid CSRF reaches placeholder');
    verify(!str_contains($valid['body'], 'demo-only-123'), 'HTTP password never reflected');
    verify(request($base, '/admin', 'GET', null, $cookie)['status'] === 403, 'HTTP form does not grant admin');
    verify(request($base, '/register', 'POST', $data, $cookie)['status'] === 200, 'HTTP register placeholder valid POST');
    $fixture = server('tests/Integration/router.php');
    $error = request($fixture, '/throw');
    verify($error['status'] === 500 && !str_contains($error['body'], 'PRIVATE_SENTINEL')
        && !str_contains($error['body'], 'Stack trace') && !str_contains($error['body'], $root), 'HTTP production 500 hides sensitive details');
    $identity = request($fixture, '/session', 'GET', null, $cookie);
    verify(json_decode($identity['body'], true) === ['user' => null, 'role' => null], 'HTTP session has no fake identity');
    foreach (['POST', 'PUT', 'PATCH', 'DELETE'] as $verb) {
        verify(request($fixture, '/change', $verb)['status'] === 403, 'HTTP global CSRF guards ' . $verb);
        verify(request($fixture, '/change', $verb, ['_token' => $token], $cookie)['status'] === 200,
            'HTTP valid body CSRF accepted for ' . $verb);
    }
    $secure = server('tests/Integration/router.php', true);
    $secureResponse = request($secure, '/token');
    verify(str_contains(strtolower($secureResponse['headers']['set-cookie'] ?? ''), '; secure'), 'HTTP fixture HTTPS detection emits Secure cookie');
    echo "TOTAL HTTP: $checks checks passed.\n";
} finally {
    if ($phpProbe !== null && is_file($phpProbe)) {
        unlink($phpProbe);
    }
    foreach ($processes as $process) {
        proc_terminate($process);
        proc_close($process);
    }
    foreach ($cookies as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
}
