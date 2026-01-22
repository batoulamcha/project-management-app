<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/ResponseHandler.php';

class Auth {
    private $conn;
    private $db;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    private function generateToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . TOKEN_EXPIRY_HOURS . ' hours'));

        try {
            $query = "INSERT INTO api_tokens (user_id, token, expires_at) 
                      VALUES (:user_id, :token, :expires_at)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':expires_at', $expiresAt);
            $stmt->execute();

            return $token;
        } catch (PDOException $e) {
            error_log("Token generation error: " . $e->getMessage());
            throw new Exception("Failed to generate token", 500);
        }
    }

    public function login($username, $password) {
        try {
            $query = "SELECT id, username, password_hash, email FROM users WHERE username = :username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password_hash'])) {
                throw new Exception("Invalid credentials", 401);
            }

            $token = $this->generateToken($user['id']);

            return [
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ]
            ];
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            throw new Exception("Login failed", 500);
        }
    }

    public function verifyToken($token) {
        if (empty($token)) {
            return false;
        }

        try {
            $query = "SELECT t.user_id, t.expires_at, u.username, u.email 
                      FROM api_tokens t
                      JOIN users u ON t.user_id = u.id
                      WHERE t.token = :token AND t.expires_at > NOW()";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':token', $token);
            $stmt->execute();

            $result = $stmt->fetch();

            if ($result) {
                return [
                    'user_id' => $result['user_id'],
                    'username' => $result['username'],
                    'email' => $result['email']
                ];
            }

            return false;
        } catch (PDOException $e) {
            error_log("Token verification error: " . $e->getMessage());
            return false;
        }
    }

    public static function getTokenFromRequest() {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                return $matches[1];
            }
        }

        if (isset($headers['X-API-Token'])) {
            return $headers['X-API-Token'];
        }

        return null;
    }

    public static function requireAuth() {
        $auth = new self();
        $token = self::getTokenFromRequest();

        if (!$token) {
            ResponseHandler::unauthorized('Authentication token required');
        }

        $user = $auth->verifyToken($token);

        if (!$user) {
            ResponseHandler::unauthorized('Invalid or expired token');
        }

        return $user;
    }
}
