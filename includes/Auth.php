<?php
class Auth {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function register($data) {
        // Basic validation
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            return ['success' => false, 'message' => 'All fields are required'];
        }

        // Create user
        $result = $this->db->createUser($data);
        
        if ($result['success']) {
            // Start session
            $this->startSession();
            $_SESSION['user_id'] = $result['id'];
            return ['success' => true];
        }

        return ['success' => false, 'message' => $result['message']];
    }

    public function login($email, $password) {
        // Debug log the login attempt
        error_log("Login attempt for email: $email");
        
        $user = $this->db->getUserByEmail($email);
        
        // Debug log the user data
        error_log("User data found: " . print_r($user, true));
        
        if ($user) {
            // Debug password verification
            $passwordMatch = password_verify($password, $user['password']);
            error_log("Password match: " . ($passwordMatch ? 'true' : 'false'));
            
            if ($passwordMatch) {
                $this->startSession();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'] ?? 'user'; // Default to 'user' if role not set
                
                // Debug session
                error_log("Session variables set: " . print_r($_SESSION, true));
                error_log("Session ID: " . session_id());
                
                return [
                    'success' => true,
                    'role' => $user['role'] ?? 'user',
                    'debug' => [
                        'user_id' => $user['id'],
                        'has_role' => isset($user['role'])
                    ],
                    'redirect' => $user['role'] === 'admin' || $user['role'] === 'super_admin' 
                        ? '../xxadmin/' 
                        : '../account/users/index/index.php'
                ];
            }
        }
        
        error_log("Login failed - " . ($user ? "Invalid password" : "User not found"));
        return ['success' => false];
    }

    public function logout() {
        // Start session if not already started
        $this->startSession();
        
        // Unset all session variables
        $_SESSION = array();
        
        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-3600, '/');
        }
        
        // Destroy the session
        session_destroy();
        
        // Redirect to login page
        header('Location: ../auth/login.php');
        exit;
    }

    public function isLoggedIn() {
        $this->startSession();
        return isset($_SESSION['user_id']);
    }

    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return $this->db->getUserById($_SESSION['user_id']);
        }
        return null;
    }

    // Add helper methods for role checking
    public function isAdmin() {
        $this->startSession();
        return isset($_SESSION['role']) && 
               ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'super_admin');
    }

    public function isSuperAdmin() {
        $this->startSession();
        return isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin';
    }

    public function generateToken() {
        if (!isset($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_token'];
    }
} 