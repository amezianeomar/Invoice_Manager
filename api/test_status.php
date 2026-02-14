<?php
// api/test_status.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$results = [
    'steps' => [],
    'errors' => []
];

function addStep($name, $status, $details = null) {
    global $results;
    $results['steps'][] = ['name' => $name, 'status' => $status, 'details' => $details];
}

function addError($msg) {
    global $results;
    $results['errors'][] = $msg;
}

// 1. Check File Structure
$requiredFiles = [
    '../config/database.php',
    '../auth.php',
    '../classes/InvoiceManager.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        addStep("Check File: $file", "OK");
    } else {
        addStep("Check File: $file", "MISSING");
        addError("File missing: $file");
    }
}

// 2. Check DB Connection
try {
    require_once '../config/database.php';
    $db = new Database();
    $pdo = $db->getConnection();
    addStep("Database Connection", "OK");

    // 3. Check Tables and Data
    $tables = ['clients', 'services', 'invoices'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = $row['count'];
            addStep("Table: $table", "OK", "Rows: $count");
            
            if ($count > 0) {
                // Fetch valid sample
                 $stmt = $pdo->query("SELECT * FROM $table LIMIT 1");
                 $data = $stmt->fetch(PDO::FETCH_ASSOC);
                 // Test encoding
                 if (json_encode($data) === false) {
                     addError("JSON Encoding failed for table $table. Check encodings (accents).");
                 }
            }
        } catch (PDOException $e) {
            addStep("Table: $table", "ERROR", $e->getMessage());
            addError("Table error: " . $e->getMessage());
        }
    }

} catch (Exception $e) {
    addStep("Database Connection", "FAILED", $e->getMessage());
    addError("DB Connection failed: " . $e->getMessage());
}

echo json_encode($results, JSON_PRETTY_PRINT);
?>
