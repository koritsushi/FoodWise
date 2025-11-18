<?php
ob_start();
session_start();

// Load core and config
require_once '../app/core/router.php';
require_once '../config/db_connection.php';

// Load controllers
require_once '../app/controllers/HomeController.php';
require_once '../app/controllers/DashboardController.php';
require_once '../app/controllers/AuthController.php';
require_once '../app/controllers/InventoryController.php';
require_once '../app/controllers/NotificationController.php';

// Define BASE_URL if not already set
// if (!defined('BASE_URL')) {
//     define('BASE_URL', 'http://localhost/FoodWise/public');
// }

// Initialize router
$router = new Router();

// ---- DEBUG ONLY – REMOVE IN PRODUCTION ----
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');   // creates public/php_error.log
// -------------------------------------------

// Public Routes
$router->add('#^/$#', [new DashboardController(), 'index']);
$router->add('#^/register$#', [new AuthController(), 'register']);
$router->add('#^/login$#', [new AuthController(), 'login']);
$router->add('#^/logout$#', [new AuthController(), 'logout']);
$router->add('#^/verify-email$#', [new AuthController(), 'verifyEmail']);
$router->add('#^/verify-code$#', [new AuthController(), 'verifyCode']);
$router->add('#^/verify-pending$#', function () {
    include '../app/views/layout/header.php';
    echo '<div class="container mt-5 text-center"><h3>Please check your email for a verification link or code.</h3></div>';
    include '../app/views/layout/footer.php';
});
// Protected Routes
$router->add('#^/dashboard$#', [new DashboardController(), 'index']);
$router->add('#^/inventory$#', [new InventoryController(), 'index']);
$router->add('#^/inventory/add$#', [new InventoryController(), 'add']);
$router->add('#^/inventory/edit/(\d+)$#', [new InventoryController(), 'edit']);
$router->add('#^/inventory/delete/(\d+)$#', [new InventoryController(), 'delete']);
$router->add('#^/notification$#', [new NotificationController(), 'index']);

// Parse and clean URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = dirname($_SERVER['SCRIPT_NAME']); // automatically detects "/FoodWise/public"
$uri = preg_replace('#^' . preg_quote($basePath) . '#', '', $uri);

// For debugging (optional)
// echo "<pre>DEBUG URI: $uri</pre>";

$router->dispatch($uri);

ob_end_flush();
?>