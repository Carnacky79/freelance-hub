<?php
/**
 * FreelanceHub - Entry Point
 */

// Carica .env se esiste
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue; // salta commenti
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

// Autoload
spl_autoload_register(function ($class) {
    $prefix = 'FreelanceHub\\';
    $baseDir = __DIR__ . '/../src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Carica configurazione
$config = require __DIR__ . '/../config/app.php';

// FORZA visualizzazione errori per debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set($config['timezone']);

// Error handling
if ($config['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Session
session_start();

use FreelanceHub\Core\Router;
use FreelanceHub\Core\Request;
use FreelanceHub\Core\Response;

// Inizializza Router
$router = new Router();

// ==========================================
// ROUTES - WEB
// ==========================================

// Home / Dashboard
$router->get('/', function (Request $request) {
    if (!isset($_SESSION['user_id'])) {
        return Response::redirect('./login');
    }
    $user = \FreelanceHub\Models\User::find($_SESSION['user_id']);
    return Response::html(renderView('pages/dashboard', ['user' => $user ? $user->toArray() : []]));
});

$router->get('/login', function (Request $request) {
    // Se già loggato, redirect a dashboard
    if (isset($_SESSION['user_id'])) {
        return Response::redirect('./');
    }
    return Response::html(renderView('pages/login'));
});

$router->get('/register', function (Request $request) {
    // Se già loggato, redirect a dashboard
    if (isset($_SESSION['user_id'])) {
        return Response::redirect('./');
    }
    return Response::html(renderView('pages/register'));
});

// ==========================================
// ROUTES - API
// ==========================================

// Test endpoint
$router->get('/api/test', function (Request $request) {
    return Response::json([
        'status' => 'ok',
        'path' => $request->getPath(),
        'method' => $request->getMethod(),
    ]);
});

$router->group('/api/v1', function (Router $router) {
    
    // Test dentro group
    $router->get('/test', function (Request $request) {
        return Response::json(['group' => 'works', 'path' => $request->getPath()]);
    });
    
    // Auth - uso closure diretta invece del controller string
    $router->post('/auth/register', function (Request $request) {
        $controller = new \FreelanceHub\Controllers\AuthController();
        return $controller->register($request);
    });
    
    $router->post('/auth/login', function (Request $request) {
        $controller = new \FreelanceHub\Controllers\AuthController();
        return $controller->login($request);
    });
    
    $router->post('/auth/logout', function (Request $request) {
        $controller = new \FreelanceHub\Controllers\AuthController();
        return $controller->logout($request);
    });
    
    // Auth
    // $router->post('/auth/login', 'AuthController@login');
    // $router->post('/auth/register', 'AuthController@register');
    // $router->post('/auth/logout', 'AuthController@logout');
    
    // Clients
    $router->get('/clients', 'ClientController@index');
    $router->post('/clients', 'ClientController@store');
    $router->get('/clients/{id}', 'ClientController@show');
    $router->put('/clients/{id}', 'ClientController@update');
    $router->delete('/clients/{id}', 'ClientController@destroy');
    
    // Projects
    $router->get('/projects', 'ProjectController@index');
    $router->post('/projects', 'ProjectController@store');
    $router->get('/projects/{id}', 'ProjectController@show');
    $router->put('/projects/{id}', 'ProjectController@update');
    $router->delete('/projects/{id}', 'ProjectController@destroy');
    
    // Tasks
    $router->get('/tasks', 'TaskController@index');
    $router->post('/tasks', 'TaskController@store');
    $router->get('/tasks/{id}', 'TaskController@show');
    $router->put('/tasks/{id}', 'TaskController@update');
    $router->delete('/tasks/{id}', 'TaskController@destroy');
    $router->post('/tasks/{id}/complete', 'TaskController@complete');
    
    // Time Tracking
    $router->get('/time-entries', 'TimeTrackingController@index');
    $router->post('/time-entries', 'TimeTrackingController@store');
    $router->post('/time-entries/start', 'TimeTrackingController@start');
    $router->post('/time-entries/stop', 'TimeTrackingController@stop');
    $router->get('/time-entries/running', 'TimeTrackingController@running');
    
    // Calendar
    $router->get('/calendar/events', 'CalendarController@events');
    $router->post('/calendar/events', 'CalendarController@createEvent');
    
    // Integrations
    $router->get('/integrations', 'IntegrationController@index');
    $router->get('/integrations/{service}/auth', 'IntegrationController@auth');
    $router->get('/integrations/{service}/callback', 'IntegrationController@callback');
    $router->post('/integrations/{accountId}/sync', 'IntegrationController@sync');
    $router->delete('/integrations/{accountId}', 'IntegrationController@disconnect');
    
    // AI Suggestions
    $router->get('/ai/suggestions', 'AIController@suggestions');
    $router->post('/ai/suggestions/{id}/accept', 'AIController@accept');
    $router->post('/ai/suggestions/{id}/dismiss', 'AIController@dismiss');
    $router->post('/ai/analyze', 'AIController@analyze');
    
    // Dashboard Stats
    $router->get('/dashboard/stats', 'DashboardController@stats');
    $router->get('/dashboard/calendar', 'CalendarController@events');
    
});

// ==========================================
// OAUTH CALLBACKS (non sotto /api)
// ==========================================

$router->get('/integrations/{service}/callback', 'IntegrationController@callback');

// ==========================================
// DISPATCH
// ==========================================

$request = new Request();

// Semplice middleware di autenticazione per API
if (str_starts_with($request->getPath(), '/api/')) {
    $token = $request->getBearerToken() ?? $_SESSION['user_id'] ?? null;
    
    if ($token && is_numeric($token)) {
        $user = \FreelanceHub\Models\User::find((int)$token);
        if ($user) {
            $request->setUser($user->toArray());
        }
    }
}

$response = $router->dispatch($request);
$response->send();

// ==========================================
// HELPER FUNCTIONS
// ==========================================

function renderView(string $view, array $data = []): string
{
    extract($data);
    ob_start();
    include __DIR__ . "/../views/{$view}.php";
    return ob_get_clean();
}