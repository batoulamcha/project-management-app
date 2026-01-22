<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/ResponseHandler.php';

class Task {
    private $conn;
    private $db;
    private $table = 'tasks';

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    public function getAll($filters = []) {
        try {
            $query = "SELECT t.*, p.name as project_name 
                      FROM {$this->table} t
                      LEFT JOIN projects p ON t.project_id = p.id
                      WHERE 1=1";
            $params = [];

            if (isset($filters['project_id']) && !empty($filters['project_id'])) {
                $query .= " AND t.project_id = :project_id";
                $params[':project_id'] = $filters['project_id'];
            }

            if (isset($filters['status']) && !empty($filters['status'])) {
                $query .= " AND t.status = :status";
                $params[':status'] = $filters['status'];
            }

            if (isset($filters['priority']) && !empty($filters['priority'])) {
                $query .= " AND t.priority = :priority";
                $params[':priority'] = $filters['priority'];
            }

            $query .= " ORDER BY t.created_at DESC";

            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get tasks error: " . $e->getMessage());
            throw new Exception("Failed to retrieve tasks", 500);
        }
    }

    public function getById($id) {
        try {
            $query = "SELECT t.*, p.name as project_name 
                      FROM {$this->table} t
                      LEFT JOIN projects p ON t.project_id = p.id
                      WHERE t.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get task error: " . $e->getMessage());
            throw new Exception("Failed to retrieve task", 500);
        }
    }

    public function create($data) {
        try {
            $required = ['project_id', 'title'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new Exception("Field '{$field}' is required", 400);
                }
            }

            $projectQuery = "SELECT id FROM projects WHERE id = :project_id";
            $projectStmt = $this->conn->prepare($projectQuery);
            $projectStmt->bindParam(':project_id', $data['project_id'], PDO::PARAM_INT);
            $projectStmt->execute();
            if (!$projectStmt->fetch()) {
                throw new Exception("Project not found", 404);
            }

            $query = "INSERT INTO {$this->table} (project_id, title, description, status, priority, due_date) 
                      VALUES (:project_id, :title, :description, :status, :priority, :due_date) 
                      RETURNING id, project_id, title, description, status, priority, due_date, created_at, updated_at";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':project_id', $data['project_id'], PDO::PARAM_INT);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindValue(':description', $data['description'] ?? null);
            $stmt->bindValue(':status', $data['status'] ?? 'pending');
            $stmt->bindValue(':priority', $data['priority'] ?? 'medium');
            $stmt->bindValue(':due_date', $data['due_date'] ?? null);
            $stmt->execute();

            $task = $stmt->fetch();
            return $task;
        } catch (PDOException $e) {
            error_log("Create task error: " . $e->getMessage());
            if ($e->getCode() == 23503) {
                throw new Exception("Invalid project_id", 400);
            }
            throw new Exception("Failed to create task", 500);
        }
    }

    public function update($id, $data) {
        try {
            $existing = $this->getById($id);
            if (!$existing) {
                throw new Exception("Task not found", 404);
            }

            $updateFields = [];
            $params = [':id' => $id];

            $allowedFields = ['project_id', 'title', 'description', 'status', 'priority', 'due_date'];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "{$field} = :{$field}";
                    $params[":{$field}"] = $data[$field];
                }
            }

            if (empty($updateFields)) {
                throw new Exception("No fields to update", 400);
            }

            $query = "UPDATE {$this->table} 
                      SET " . implode(', ', $updateFields) . ", updated_at = CURRENT_TIMESTAMP
                      WHERE id = :id 
                      RETURNING id, project_id, title, description, status, priority, due_date, created_at, updated_at";

            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Update task error: " . $e->getMessage());
            throw new Exception("Failed to update task", 500);
        }
    }

    public function delete($id) {
        try {
            $existing = $this->getById($id);
            if (!$existing) {
                throw new Exception("Task not found", 404);
            }

            $query = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Delete task error: " . $e->getMessage());
            throw new Exception("Failed to delete task", 500);
        }
    }
}
