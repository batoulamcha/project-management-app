<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/ResponseHandler.php';
require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $user = Auth::requireAuth();
} catch (Exception $e) {
    ResponseHandler::error($e->getMessage(), $e->getCode() ?: 401);
}

try {
    if ($method === 'GET') {
        $db = new Database();
        $conn = $db->getConnection();

        $query = "SELECT id, name, description, status FROM projects ORDER BY name";
        $stmt = $conn->prepare($query);
        $stmt->execute();

        $projects = $stmt->fetchAll();
        ResponseHandler::success($projects, 'Projects retrieved successfully');
    } else {
        ResponseHandler::error('Method not allowed', 405);
    }
} catch (Exception $e) {
    $code = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    ResponseHandler::error($e->getMessage(), $code);
}
