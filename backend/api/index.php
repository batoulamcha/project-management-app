<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/ResponseHandler.php';

$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$pathParts = array_filter(explode('/', trim($path, '/')));

$pathParts = array_values($pathParts);

if (count($pathParts) < 2 || $pathParts[0] !== 'api') {
    ResponseHandler::error('Invalid API endpoint', 404);
}

$resource = $pathParts[1] ?? null;

try {
    switch ($resource) {
        case 'auth':
            require_once __DIR__ . '/auth.php';
            break;
            
        case 'tasks':
            require_once __DIR__ . '/tasks_router.php';
            break;
            
        case 'projects':
            require_once __DIR__ . '/projects_router.php';
            break;
            
        case 'config':
            header('Content-Type: application/javascript');
            header('Access-Control-Allow-Origin: *');
            if (!defined('API_BASE_URL')) {
                require_once __DIR__ . '/../config/config.php';
            }
            require_once __DIR__ . '/config.php';
            exit;
            
        default:
            ResponseHandler::error('Resource not found', 404);
    }
} catch (Exception $e) {
    $code = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    ResponseHandler::error($e->getMessage(), $code);
}
