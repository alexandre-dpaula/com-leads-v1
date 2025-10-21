<?php

declare(strict_types=1);

use App\Controllers\ApiLeadController;
use App\Controllers\ApiStageController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;
use App\Controllers\PasswordController;
use App\Controllers\ProfileController;
use App\Core\Router;
use App\Core\Session;

require __DIR__ . '/../app/config/config.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    if (str_starts_with($class, $prefix)) {
        $relative = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});

Session::start();

$router = new Router();

$router->get('/', [HomeController::class, 'index']);

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/password/forgot', [PasswordController::class, 'showForgot']);
$router->post('/password/forgot', [PasswordController::class, 'sendResetLink']);
$router->get('/password/reset', [PasswordController::class, 'showReset']);
$router->post('/password/reset', [PasswordController::class, 'reset']);

$router->get('/dashboard', [DashboardController::class, 'index']);

$router->get('/profile', [ProfileController::class, 'edit']);
$router->post('/profile', [ProfileController::class, 'update']);

$router->post('/api/stages', [ApiStageController::class, 'store']);
$router->put('/api/stages/{id}', [ApiStageController::class, 'update']);
$router->delete('/api/stages/{id}', [ApiStageController::class, 'delete']);
$router->put('/api/stages/reorder', [ApiStageController::class, 'reorder']);

$router->get('/api/leads', [ApiLeadController::class, 'index']);
$router->post('/api/leads', [ApiLeadController::class, 'store']);
$router->put('/api/leads/{id}', [ApiLeadController::class, 'update']);
$router->delete('/api/leads/{id}', [ApiLeadController::class, 'delete']);
$router->put('/api/leads/{id}/move', [ApiLeadController::class, 'move']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

