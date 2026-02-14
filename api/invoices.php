<?php
// api/invoices.php
require_once 'config.php';
require_once '../classes/InvoiceManager.php';

$manager = new InvoiceManager();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id'])) {
        // Get single invoice details
        $details = $manager->getInvoiceDetails($_GET['id']);
        if ($details) {
            sendResponse(['success' => true, 'data' => $details]);
        } else {
            sendResponse(['success' => false, 'message' => 'Invoice not found'], 404);
        }
    } else {
        // List all invoices
        $invoices = $manager->getAllInvoices();
        sendResponse(['success' => true, 'data' => $invoices]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = getJsonInput();
    
    try {
        $clientId = $input['client_id'];
        $invoiceDate = $input['invoice_date'];
        $services = $input['services'];
        $total = isset($input['total']) ? $input['total'] : null;
        
        $newId = $manager->createInvoice($clientId, $invoiceDate, $services, $total);
        sendResponse(['success' => true, 'id' => $newId, 'message' => 'Invoice created']);
    } catch (Exception $e) {
        sendResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = getJsonInput();
    
    try {
        if (!isset($input['id'])) {
            throw new Exception('Invoice ID is required for update');
        }
        $id = $input['id'];
        $clientId = $input['client_id'];
        $invoiceDate = $input['invoice_date'];
        $services = $input['services'];
        $total = isset($input['total']) ? $input['total'] : null;
        
        $manager->updateInvoice($id, $clientId, $invoiceDate, $services, $total);
        sendResponse(['success' => true, 'message' => 'Invoice updated']);
    } catch (Exception $e) {
        sendResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'] ?? 0;
    try {
        $manager->deleteInvoice($id);
        sendResponse(['success' => true, 'message' => 'Invoice deleted']);
    } catch (Exception $e) {
        sendResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
?>
