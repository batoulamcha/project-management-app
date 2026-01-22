<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Task.php';
require_once __DIR__ . '/../classes/ResponseHandler.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = array_filter(explode('/', trim($path, '/')));
$pathParts = array_values($pathParts);

$id = isset($pathParts[2]) ? intval($pathParts[2]) : null;
$task = new Task();
$input = json_decode(file_get_contents('php://input'), true);

try {
    $user = Auth::requireAuth();
} catch (Exception $e) {
    ResponseHandler::error($e->getMessage(), $e->getCode() ?: 401);
}

switch ($method) {
    case 'GET':
        if ($id) {
            if ($id <= 0) {
                ResponseHandler::error('Invalid task ID', 400);
            }
            $taskData = $task->getById($id);
            if (!$taskData) {
                ResponseHandler::notFound('Task not found');
            }
            ResponseHandler::success($taskData, 'Task retrieved successfully');
        } else {
            $filters = [];
            if (isset($_GET['project_id'])) {
                $filters['project_id'] = $_GET['project_id'];
            }
            if (isset($_GET['status'])) {
                $filters['status'] = $_GET['status'];
            }
            if (isset($_GET['priority'])) {
                $filters['priority'] = $_GET['priority'];
            }
            $tasks = $task->getAll($filters);
            ResponseHandler::success($tasks, 'Tasks retrieved successfully');
        }
        break;

    case 'POST':
        if (empty($input)) {
            ResponseHandler::error('Request body is required', 400);
        }
        $newTask = $task->create($input);
        ResponseHandler::success($newTask, 'Task created successfully', 201);
        break;

    case 'PUT':
    case 'PATCH':
        if (!$id || $id <= 0) {
            ResponseHandler::error('Task ID is required', 400);
        }
        if (empty($input)) {
            ResponseHandler::error('Request body is required', 400);
        }
        $updatedTask = $task->update($id, $input);
        ResponseHandler::success($updatedTask, 'Task updated successfully');
        break;

    case 'DELETE':
        if (!$id || $id <= 0) {
            ResponseHandler::error('Task ID is required', 400);
        }
        $task->delete($id);
        ResponseHandler::success(null, 'Task deleted successfully');
        break;

    default:
        ResponseHandler::error('Method not allowed', 405);
}
