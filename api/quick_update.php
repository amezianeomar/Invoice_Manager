<?php
require_once '../auth.php';
require_once '../config/database.php';

$auth = new Auth();
$auth->requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $entity = $data['entity'] ?? '';
    $id = intval($data['id'] ?? 0);
    $field = $data['field'] ?? '';
    $value = $data['value'] ?? '';

    if (!$id || !$entity || !$field) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    try {
        $db = new Database();
        $pdo = $db->getConnection();
        
        $table = '';
        $allowedFields = [];

        switch ($entity) {
            case 'client':
                $table = 'clients';
                $allowedFields = ['name', 'details', 'contact'];
                break;
            case 'invoice':
                $table = 'invoices';
                $allowedFields = ['invoice_date', 'total']; // Total usually calculated, but manual override exists
                break;
            case 'item':
                $table = 'invoice_items';
                $allowedFields = ['custom_desc', 'quantity', 'unit_price', 'from_location', 'to_location', 'city'];
                break;
            default:
                throw new Exception('Invalid entity');
        }

        if (!in_array($field, $allowedFields)) {
            throw new Exception('Field not allowed for editing');
        }

        // Prepare Query
        $sql = "UPDATE $table SET $field = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$value, $id]);

        // If updating item price or quantity, we might want to trigger a invoice total update? 
        // For now, simpler to just update the field. Use frontend to reload if needed.

        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
