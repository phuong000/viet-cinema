<?php
declare(strict_types=1);

use App\Core\Application;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Environment;
use App\Core\HttpException;
use App\Core\Model;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Middleware\Middleware;
use App\Middleware\RequireAccess;
use App\Services\Auth\AccessPolicy;
use App\Services\Auth\DenyAccess;

check(class_exists(Application::class) && class_exists(Controller::class) && class_exists(Model::class), 'PSR-4 autoload core classes');
check(!class_exists('App\\MissingClass'), 'Unknown autoload class is safe');
$testRouter = new Router();
$testRouter->add('GET', '/item/{id}', static fn (Request $r): Response => new Response($r->parameter('id')));
$testRouter->add('POST', '/item/{id}', static fn (Request $r): Response => Response::json(['id' => $r->parameter('id')], 201));
$testRouter->add('GET', '/item/new', static fn (): Response => new Response('literal'));
check($testRouter->dispatch(new Request('GET', '/item/42'))->body === '42', 'Route parameters');
check($testRouter->dispatch(new Request('POST', '/item/42'))->status === 201, 'POST registration and response status');
check($testRouter->dispatch(new Request('GET', '/item/new'))->body === 'literal', 'Literal route precedence');
check($testRouter->dispatch(new Request('DELETE', '/item/42'))->status === 405, 'Unsupported method');
try {
    $testRouter->add('GET', '/item/{slug}', static fn (): Response => new Response(''));
    check(false, 'Equivalent parameter route rejected');
} catch (LogicException) {
    check(true, 'Equivalent parameter route rejected');
}
$request = new Request('POST', '/test', ['search' => 'film'], ['email' => ['bad'], 'name' => 'Viet']);
check($request->input('email') === '' && $request->input('search') === 'film', 'Request input rejects arrays and reads query');
check($request->all()['name'] === 'Viet', 'Request exposes POST data for validation');
$rules = ['email' => ['required', 'email'], 'password' => ['required', 'min:8', 'max:128']];
check(Validator::errors(['email' => 'test@example.com', 'password' => 'demo-only-123'], $rules) === [], 'Valid input accepted');
check(count(Validator::errors(['email' => 'bad', 'password' => 'x'], $rules)) === 2, 'Invalid email and short password rejected');
check(count(Validator::errors(['email' => [], 'password' => str_repeat('x', 129)], $rules)) === 2, 'Array and oversized input rejected');
check(count(Validator::errors([], $rules)) === 2, 'Required fields rejected');
check(session_status() === PHP_SESSION_ACTIVE, 'Session actually started');
Session::set('unit', 'value');
check(Session::get('unit') === 'value', 'Session read and write');
Session::flash('success', 'Saved sample');
check(Session::pullFlash()['message'] === 'Saved sample' && Session::pullFlash() === null, 'Flash consumed once');
$options = session_get_cookie_params();
check($options['httponly'] && $options['samesite'] === 'Lax', 'Actual session cookie parameters');
$_SERVER['HTTPS'] = 'on';
check(Session::cookieOptions()['secure'], 'HTTPS enables Secure cookie');
$_SERVER['HTTPS'] = 'off';
putenv('SESSION_SECURE=true');
check(Session::cookieOptions()['secure'], 'Explicit Secure option');
putenv('SESSION_SECURE=false');
check(!Session::cookieOptions()['secure'], 'Local HTTP cookie option');
$token = Csrf::token();
check(strlen($token) === 64 && Csrf::token() === $token && Csrf::valid($token), 'CSRF token generated and stable in session');
check(!Csrf::valid('') && !Csrf::valid('invalid'), 'Missing and invalid CSRF rejected');
$app = Application::boot();
check($app->handle(new Request('POST', '/login'))->status === 403, 'Global CSRF prevents POST');
check($app->handle(new Request('POST', '/login', ['_token' => $token]))->status === 403, 'CSRF token must be in body, not query string');
check($app->handle(new Request('POST', '/login', [], ['_token' => $token]))->status === 422, 'Valid CSRF reaches validation');
$valid = $app->handle(new Request('POST', '/login', [], ['_token' => $token, 'email' => 'test@example.com', 'password' => 'demo-only-123']));
check($valid->status === 200 && !Session::get('user') && !Session::get('role'), 'Placeholder never grants identity or role');
check($app->handle(new Request('GET', '/admin'))->status === 403, 'Real admin route denied by default');
foreach (['/movies', '/movies/sample', '/showtimes', '/login', '/register', '/booking', '/admin/demo'] as $path) {
    check($app->handle(new Request('GET', $path))->status === 200, 'Module placeholder ' . $path);
}
check($app->handle(new Request('GET', '/movies/not-real'))->status === 404, 'Unknown movie rejected');
$events = new ArrayObject();
$makeMiddleware = static fn (string $name): Middleware => new class ($events, $name) implements Middleware {
    public function __construct(private ArrayObject $events, private string $name)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        $this->events[] = $this->name . ':before';
        $response = $next($request);
        $this->events[] = $this->name . ':after';
        return $response;
    }
};
$pipeline = new Router();
$pipeline->use($makeMiddleware('global'));
$pipeline->add('GET', '/', static function () use ($events): Response {
    $events[] = 'handler';
    return new Response('ok');
}, [$makeMiddleware('route')]);
$pipeline->dispatch(new Request('GET', '/'));
check($events->getArrayCopy() === ['global:before', 'route:before', 'handler', 'route:after', 'global:after'], 'Middleware pipeline order');
$denier = new RequireAccess(new DenyAccess(), 'admin.access');
check($denier->handle(new Request('GET', '/'), static fn () => throw new RuntimeException('Must not run'))->status === 403, 'Authorization short circuits handler');
$policy = new class implements AccessPolicy {
    public function authenticated(): bool { return true; }
    public function allows(string $permission): bool { return $permission === 'test.read'; }
};
check((new RequireAccess($policy, 'test.read'))->handle(new Request('GET', '/'), static fn () => new Response('allowed'))->body === 'allowed', 'Authorization contract allows explicit policy in test only');
foreach ([400, 403, 404, 405, 500] as $status) {
    $error = Response::error($status);
    check($error->status === $status && str_contains($error->body, (string) $status), 'Rendered HTTP error ' . $status);
}
$broken = new Router();
$broken->add('GET', '/boom', static fn () => throw new RuntimeException('SECRET_SENTINEL'));
ini_set('error_log', dirname(__DIR__, 2) . '/storage/logs/unit-test.log');
putenv('APP_ENV=production');
$error = (new Application($broken))->handle(new Request('GET', '/boom'));
check($error->status === 500 && !str_contains($error->body, 'SECRET_SENTINEL') && !str_contains($error->body, 'Stack trace'), 'Production exception details hidden');
check(Application::exception(new HttpException(400))->status === 400, 'Bad request exception mapping');
check(str_contains(View::page('errors/http', ['status' => 400, 'title' => '<script>']), '&lt;script&gt;'), 'View layout escaping');
$level = ob_get_level();
try {
    View::render('missing');
} catch (RuntimeException) {
    check(ob_get_level() === $level, 'Missing view leaves output buffers intact');
}
$_SERVER['REQUEST_URI'] = '/bad%ZZ';
try {
    Request::capture();
    check(false, 'Malformed URI rejected');
} catch (HttpException $exception) {
    check($exception->status === 400, 'Malformed URI rejected');
}
$_SERVER['REQUEST_URI'] = '/%2e%2e/config';
try {
    Request::capture();
    check(false, 'Traversal URI rejected');
} catch (HttpException $exception) {
    check($exception->status === 400, 'Traversal URI rejected');
}
// PDO test doubles exercise binding without connecting to any database.
$statement = new class extends PDOStatement {
    public array $values = [];
    public function execute(?array $params = null): bool
    {
        $this->values = $params ?? [];
        return true;
    }
};
$pdo = new class ($statement) extends PDO {
    public string $sql = '';
    public function __construct(private PDOStatement $statement) {}
    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        $this->sql = $query;
        return $this->statement;
    }
};
$model = new class ($pdo) extends Model {
    public function lookup(string $input): PDOStatement
    {
        return $this->query('SELECT :value', ['value' => $input]);
    }
};
$attack = "' OR 1=1 --";
$model->lookup($attack);
check($pdo->sql === 'SELECT :value' && $statement->values === ['value' => $attack], 'Base Model uses prepare and binds untrusted input (PDO double)');
$envFile = tempnam(sys_get_temp_dir(), 'vc-env');
try {
    putenv('VC_UNIT_EXISTING=external');
    file_put_contents($envFile, "# sample\nVC_UNIT_EXISTING=file\nVC_UNIT_NEW=\"quoted value\"\n");
    Environment::load($envFile);
    check(getenv('VC_UNIT_EXISTING') === 'external' && getenv('VC_UNIT_NEW') === 'quoted value', 'Environment respects external values');
} finally {
    unlink($envFile);
    putenv('VC_UNIT_EXISTING');
    putenv('VC_UNIT_NEW');
}
putenv('DB_DSN');
check($app->handle(new Request('GET', '/'))->status === 200, 'Website renders with DB_DSN absent');
