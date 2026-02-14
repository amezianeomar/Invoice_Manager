<?php
// api/login.php
require_once 'config.php';

$auth = new Auth();

// Login Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = getJsonInput();
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    if ($auth->login($email, $password)) {
        // Successful login
        $user = $auth->getCurrentUser();
        sendResponse([
            'success' => true,
            'message' => 'Login successful',
            'user' => $user
        ]);
    } else {
        // Failed login
        sendResponse([
            'success' => false,
            'message' => 'Email ou mot de passe incorrect'
        ], 401);
    }
}

// Check Session Status (for App load)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($auth->isLoggedIn()) {
        sendResponse([
            'authenticated' => true,
            'user' => $auth->getCurrentUser()
        ]);
    } else {
        sendResponse(['authenticated' => false]);
    }
}
?>
