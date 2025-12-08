<?php
/**
 * FreelanceHub - Entry Point (VERSIONE SICURA)
 * 
 * CHANGELOG da versione originale:
 * - Aggiunta gestione sicura sessioni
 * - CSRF protection su tutte le route POST/PUT/DELETE
 * - Rate limiting su auth endpoints
 * - Error logging
 * - Exception handling globale
 */

// === STEP 1: CARICA ENVIRONMENT ===
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

// === STEP 2: AUTOLOAD ===
//spl_autoload_register(function ($class) {
//    $prefix = 'FreelanceHub\\';
//    $baseDir = __DIR__ . '/../src/';
//
//    $len = strlen($prefix);
//    if (strncmp($prefix, $class, $len) !== 0) {
//        return;
//    }
//
//    $relativeClass = substr($class, $len);
//    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
//
//    if (file_exists($file)) {
//        require $file;
//    }
//});

// Autoloader PSR-4 AGGIORNATO
// Autoloader PSR-4
spl_autoload_register(function ($class) {
    // Namespace base
    $prefix = 'FreelanceHub\\';
    $baseDir = __DIR__ . '/../src/';

    // Verifica che la classe usi il namespace
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return; // No match, next autoloader
    }

    // Ottieni nome classe relativo
    $relativeClass = substr($class, $len);

    // Converti namespace in path
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // DEBUG: Logga tentativo di caricamento (commentare in produzione)
    // error_log("Autoloader: Tentativo caricamento $class da $file");

    // Carica il file se esiste
    if (file_exists($file)) {
        require $file;
        // error_log("Autoloader: ✅ Caricato $class");
    } else {
        // error_log("Autoloader: ❌ NON trovato $file");
    }
});

// FALLBACK: Carica manualmente i file core essenziali
// (serve se l'autoloader ha problemi di case-sensitivity su Windows)
$coreFiles = [
    __DIR__ . '/../src/Core/Request.php',
    __DIR__ . '/../src/Core/Response.php',
    __DIR__ . '/../src/Core/Router.php',
    __DIR__ . '/../src/Core/Database.php',
    __DIR__ . '/../src/Core/Session.php',
    __DIR__ . '/../src/Core/Logger.php',  // <-- AGGIUNTO
];

foreach ($coreFiles as $file) {
    if (file_exists($file)) {
        require_once $file;
    } else {
        die("FATAL: File core mancante: $file");
    }
}

// Carica middleware essenziali
$middlewareFiles = [
    __DIR__ . '/../src/Middleware/CSRFMiddleware.php',
    __DIR__ . '/../src/Middleware/RateLimitMiddleware.php',
];

foreach ($middlewareFiles as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}

// === STEP 3: CONFIGURAZIONE ===
$config = require __DIR__ . '/../config/app.php';

// Timezone
date_default_timezone_set($config['timezone']);

// Error handling
if ($config['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../storage/logs/php-errors.log');
}

// === STEP 4: GESTIONE ERRORI GLOBALE ===
set_exception_handler(function (\Throwable $e) use ($config) {
    // Log eccezione
    \FreelanceHub\Core\Logger::exception($e);
    
    // Response appropriata
    if ($config['debug']) {
        http_response_code(500);
        echo json_encode([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ], JSON_PRETTY_PRINT);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error']);
    }
    exit;
});

set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    
    \FreelanceHub\Core\Logger::error("PHP Error: $message", [
        'severity' => $severity,
        'file' => $file,
        'line' => $line,
    ]);
    
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// === STEP 5: SESSIONE SICURA ===
use FreelanceHub\Core\Session;
Session::start();

use FreelanceHub\Core\Router;
use FreelanceHub\Core\Request;
use FreelanceHub\Core\Response;
use FreelanceHub\Middleware\CSRFMiddleware;
use FreelanceHub\Middleware\RateLimitMiddleware;

// === STEP 6: INIZIALIZZA ROUTER ===
$router = new Router();

// Middleware globale CSRF (applica a tutte le route)
$csrfMiddleware = new CSRFMiddleware();

// === STEP 7: WEB ROUTES ===
$router->get('/', function (Request $request) {
    if (!Session::has('user_id')) {
        return Response::redirect('./login');
    }
    $user = \FreelanceHub\Models\User::find(Session::get('user_id'));
    return Response::html(renderView('pages/dashboard', ['user' => $user ? $user->toArray() : []]));
});

$router->get('/login', function (Request $request) {
    if (Session::has('user_id')) {
        return Response::redirect('./');
    }
    return Response::html(renderView('pages/login'));
});

$router->get('/register', function (Request $request) {
    if (Session::has('user_id')) {
        return Response::redirect('./');
    }
    return Response::html(renderView('pages/register'));
});

// === STEP 8: API ROUTES ===
$router->get('/api/test', function (Request $request) {
    return Response::json([
        'status' => 'ok',
        'csrf_token' => Session::generateCSRFToken(), // Per JS
    ]);
});

$router->group('/api/v1', function (Router $router) use ($csrfMiddleware) {
    
    // === AUTH (con rate limiting) ===
    $authRateLimiter = new RateLimitMiddleware(5, 1); // 5 tentativi/minuto
    
    $router->post('/auth/register', function (Request $request) use ($authRateLimiter) {
        // Rate limit
        $limitResponse = $authRateLimiter($request);
        if ($limitResponse) return $limitResponse;
        
        // CSRF check
        $csrfResponse = (new CSRFMiddleware())($request);
        if ($csrfResponse) return $csrfResponse;
        
        $controller = new \FreelanceHub\Controllers\AuthController();
        $response = $controller->register($request);
        
        // Log registrazione
        if ($response->getStatusCode() === 200) {
            \FreelanceHub\Core\Logger::info('New user registered', [
                'email' => $request->getBody('email')
            ]);
        }
        
        return $response;
    });
    
    $router->post('/auth/login', function (Request $request) use ($authRateLimiter) {
        // Rate limit
        $limitResponse = $authRateLimiter($request);
        if ($limitResponse) return $limitResponse;
        
        // CSRF check
        $csrfResponse = (new CSRFMiddleware())($request);
        if ($csrfResponse) return $csrfResponse;
        
        $controller = new \FreelanceHub\Controllers\AuthController();
        $response = $controller->login($request);
        
        // Log accesso
        if ($response->getStatusCode() === 200) {
            \FreelanceHub\Core\Logger::access('login', Session::get('user_id'), [
                'email' => $request->getBody('email')
            ]);
        } else {
            \FreelanceHub\Core\Logger::security('Failed login attempt', [
                'email' => $request->getBody('email'),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        }
        
        return $response;
    });
    
    $router->post('/auth/logout', function (Request $request) {
        $userId = Session::get('user_id');
        $controller = new \FreelanceHub\Controllers\AuthController();
        $response = $controller->logout($request);
        
        if ($userId) {
            \FreelanceHub\Core\Logger::access('logout', $userId);
        }
        
        return $response;
    });
    
    // === PROTECTED ROUTES (richiedono autenticazione) ===
    
    // Middleware di autenticazione
    $authMiddleware = function(Request $request) {
        if (!Session::has('user_id')) {
            return Response::unauthorized('Authentication required');
        }
        return null;
    };
    
    // Clients
    $router->get('/clients', 'ClientController@index', [$authMiddleware]);
    $router->post('/clients', 'ClientController@store', [$authMiddleware, $csrfMiddleware]);
    $router->get('/clients/{id}', 'ClientController@show', [$authMiddleware]);
    $router->put('/clients/{id}', 'ClientController@update', [$authMiddleware, $csrfMiddleware]);
    $router->delete('/clients/{id}', 'ClientController@destroy', [$authMiddleware, $csrfMiddleware]);
    
    // Projects
    $router->get('/projects', 'ProjectController@index', [$authMiddleware]);
    $router->post('/projects', 'ProjectController@store', [$authMiddleware, $csrfMiddleware]);
    $router->get('/projects/{id}', 'ProjectController@show', [$authMiddleware]);
    $router->put('/projects/{id}', 'ProjectController@update', [$authMiddleware, $csrfMiddleware]);
    $router->delete('/projects/{id}', 'ProjectController@destroy', [$authMiddleware, $csrfMiddleware]);
    
    // Tasks
    $router->get('/tasks', 'TaskController@index', [$authMiddleware]);
    $router->post('/tasks', 'TaskController@store', [$authMiddleware, $csrfMiddleware]);
    $router->get('/tasks/{id}', 'TaskController@show', [$authMiddleware]);
    $router->put('/tasks/{id}', 'TaskController@update', [$authMiddleware, $csrfMiddleware]);
    $router->delete('/tasks/{id}', 'TaskController@destroy', [$authMiddleware, $csrfMiddleware]);
    $router->post('/tasks/{id}/complete', 'TaskController@complete', [$authMiddleware, $csrfMiddleware]);
    
    // Time Tracking
    $router->get('/time-entries', 'TimeTrackingController@index', [$authMiddleware]);
    $router->post('/time-entries', 'TimeTrackingController@store', [$authMiddleware, $csrfMiddleware]);
    $router->post('/time-entries/start', 'TimeTrackingController@start', [$authMiddleware, $csrfMiddleware]);
    $router->post('/time-entries/stop', 'TimeTrackingController@stop', [$authMiddleware, $csrfMiddleware]);
    $router->get('/time-entries/running', 'TimeTrackingController@running', [$authMiddleware]);
    
    // Calendar
    $router->get('/calendar/events', 'CalendarController@events', [$authMiddleware]);
    $router->post('/calendar/events', 'CalendarController@createEvent', [$authMiddleware, $csrfMiddleware]);
    
    // Integrations
    $router->get('/integrations', 'IntegrationController@index', [$authMiddleware]);
    $router->get('/integrations/{service}/auth', 'IntegrationController@auth', [$authMiddleware]);
    $router->post('/integrations/{accountId}/sync', 'IntegrationController@sync', [$authMiddleware, $csrfMiddleware]);
    $router->delete('/integrations/{accountId}', 'IntegrationController@disconnect', [$authMiddleware, $csrfMiddleware]);
    
    // AI
    $router->get('/ai/suggestions', 'AIController@suggestions', [$authMiddleware]);
    $router->post('/ai/suggestions/{id}/accept', 'AIController@accept', [$authMiddleware, $csrfMiddleware]);
    $router->post('/ai/suggestions/{id}/dismiss', 'AIController@dismiss', [$authMiddleware, $csrfMiddleware]);
    
    // Dashboard
    $router->get('/dashboard/stats', 'DashboardController@stats', [$authMiddleware]);
    $router->get('/dashboard/calendar', 'DashboardController@calendar', [$authMiddleware]);

    // Settings API
    $router->get('/settings', 'SettingsController@index');
    $router->put('/settings/profile', 'SettingsController@updateProfile');
    $router->post('/settings/password', 'SettingsController@changePassword');
    $router->put('/settings/preferences', 'SettingsController@updatePreferences');
    $router->delete('/settings/account', 'SettingsController@deleteAccount');
});

// OAuth callbacks (non sotto /api e senza CSRF perché vengono da servizi esterni)
$router->get('/integrations/{service}/callback', 'IntegrationController@callback');

// === STEP 9: DISPATCH ===
$request = new Request();

// Imposta user nel request se autenticato
if (Session::has('user_id')) {
    $user = \FreelanceHub\Models\User::find(Session::get('user_id'));
    if ($user) {
        $request->setUser($user->toArray());
    }
}

try {
    $response = $router->dispatch($request);
    $response->send();
} catch (\Exception $e) {
    // Log errore
    \FreelanceHub\Core\Logger::exception($e);
    
    // Response errore
    if ($config['debug']) {
        Response::error($e->getMessage(), 500)->send();
    } else {
        Response::error('Internal server error', 500)->send();
    }
}

// === HELPER FUNCTION ===
function renderView(string $view, array $data = []): string
{
    extract($data);
    ob_start();
    include __DIR__ . "/../views/{$view}.php";
    return ob_get_clean();
}
