<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/ResponseHandler.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    if ($method === 'POST') {
        if (!isset($input['username']) || !isset($input['password'])) {
            ResponseHandler::error('Username and password are required', 400);
        }

        $auth = new Auth();
        $result = $auth->login($input['username'], $input['password']);
        
        ResponseHandler::success($result, 'Login successful');
    } else {
        ResponseHandler::error('Method not allowed', 405);
    }
} catch (Exception $e) {
    $code = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    ResponseHandler::error($e->getMessage(), $code);
}
