<?php
require_once 'auth.php';

$auth = new Auth();
$auth->requireAuth();

require_once 'classes/InvoiceManager.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$invoiceId = intval($_GET['id']);

try {
    $invoiceManager = new InvoiceManager();
    $data = $invoiceManager->getInvoiceDetails($invoiceId);
    
    if (!$data) {
        throw new Exception("Facture introuvable");
    }
    
    $invoiceManager->deleteInvoice($invoiceId);
    
    header("Location: index.php?deleted=1&invoice_id=$invoiceId");
    exit;
    
} catch (Exception $e) {
    $error = "Erreur lors de la suppression de la facture: " . $e->getMessage();
    include 'error.php';
}
?>
