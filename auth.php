<?php
session_start(); 
require_once 'config/database.php';

class Auth {
    private $pdo;
    
    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }
    
    public function login($email, $password) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM comptes WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && $password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['logged_in'] = true;
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function logout() {
        session_destroy();
        header('Location: login.php');
        exit;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }
    
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['user_email']
            ];
        }
        return null;
    }
}
?>
