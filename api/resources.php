<?php
// api/resources.php
require_once 'config.php';
require_once '../classes/InvoiceManager.php';

$manager = new InvoiceManager();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $type = $_GET['type'] ?? '';
    
    if ($type === 'clients') {
        sendResponse(['success' => true, 'data' => $manager->getAllClients()]);
    } elseif ($type === 'services') {
        sendResponse(['success' => true, 'data' => $manager->getAllServices()]);
    } else {
        sendResponse(['success' => false, 'message' => 'Invalid type'], 400);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_GET['type'] ?? '';
    $data = getJsonInput();

    if ($type === 'clients') {
        if (empty($data['name'])) {
            sendResponse(['success' => false, 'message' => 'Name is required'], 400);
        }
        try {
            $id = $manager->createClient($data['name'], $data['details'] ?? null, $data['contact'] ?? null);
            sendResponse(['success' => true, 'id' => $id, 'message' => 'Client created']);
        } catch (Exception $e) {
            sendResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    } else {
        sendResponse(['success' => false, 'message' => 'Invalid type'], 400);
    }
}
?>
