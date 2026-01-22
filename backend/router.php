<?php

$requestUri = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($requestUri, PHP_URL_PATH);

if (strpos($requestPath, '/api') === 0) {
    chdir(__DIR__);
    require_once __DIR__ . '/api/index.php';
    exit;
}

if ($requestPath === '/' || $requestPath === '/index.html' || strpos($requestPath, '/frontend/') === 0) {
    $frontendFile = __DIR__ . '/../frontend/index.html';
    if (file_exists($frontendFile)) {
        readfile($frontendFile);
        exit;
    }
}

if (file_exists(__DIR__ . $requestPath) && is_file(__DIR__ . $requestPath)) {
    return false;
}

header('HTTP/1.0 404 Not Found');
echo '404 Not Found';
