<?php
require_once 'auth.php';

$auth = new Auth();
$auth->requireAuth();

require_once 'classes/InvoiceManager.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

try {
    $invoiceManager = new InvoiceManager();
    
    $invoiceId = intval($_POST['invoice_id']);
    $clientId = $_POST['client_id'];
    $invoiceDate = $_POST['invoice_date'];
    $services = $_POST['services'];
    
    $cleanServices = [];
    foreach ($services as $service) {
        if (!empty($service['service_id']) && !empty($service['unit_price'])) {
            $cleanServices[] = [
                'service_id' => intval($service['service_id']),
                'unit_price' => floatval($service['unit_price']),
                'service_date' => !empty($service['service_date']) ? $service['service_date'] : null,
                'from_location' => !empty($service['from_location']) ? trim($service['from_location']) : null,
                'to_location' => !empty($service['to_location']) ? trim($service['to_location']) : null,
                'city' => !empty($service['city']) ? trim($service['city']) : null
            ];
        }
    }
    
    if (empty($cleanServices)) {
        throw new Exception("Aucun service valide trouvé");
    }
    
    $invoiceManager->updateInvoice($invoiceId, $clientId, $invoiceDate, $cleanServices);
    
    header("Location: view-invoice.php?id=$invoiceId&updated=1");
    exit;
    
} catch (Exception $e) {
    $error = "Erreur lors de la modification de la facture: " . $e->getMessage();
    include 'error.php';
}
?>
