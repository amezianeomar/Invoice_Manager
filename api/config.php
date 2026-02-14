<?php
// api/config.php
// Allow requests from Vite dev server
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/database.php';
require_once '../auth.php';

// Helper to send JSON response
function sendResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// Helper to get JSON input
function getJsonInput() {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}
?>
