<?php
require_once 'config/database.php';
$db = new Database();
$pdo = $db->getConnection();
$stmt = $pdo->query("SELECT id FROM invoices ORDER BY id");
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
?>
