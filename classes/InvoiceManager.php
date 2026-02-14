<?php
require_once __DIR__ . '/../auth.php';

$auth = new Auth();
$auth->requireAuth();

require_once __DIR__ . '/../config/database.php';
class InvoiceManager {
    private $pdo;
    
    public function __construct() {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }
    
    public function getAllClients() {
        $stmt = $this->pdo->query("SELECT * FROM clients ORDER BY name");
        return $stmt->fetchAll();
    }
    
    public function getAllServices() {
        $stmt = $this->pdo->query("SELECT * FROM services ORDER BY title");
        return $stmt->fetchAll();
    }

    public function createClient($name, $details = null, $contact = null) {
        $stmt = $this->pdo->prepare("INSERT INTO clients (name, details, contact) VALUES (?, ?, ?)");
        $stmt->execute([$name, $details, $contact]);
        return $this->pdo->lastInsertId();
    }
    
    public function createInvoice($clientId, $invoiceDate, $services, $customTotal = null) {
        try {
            $this->pdo->beginTransaction();
            
            $total = 0;
            if ($customTotal !== null) {
                $total = floatval($customTotal);
            } else {
                foreach ($services as $service) {
                    $total += floatval($service['unit_price']);
                }
            }
            
            $stmt = $this->pdo->prepare("INSERT INTO invoices (client_id, invoice_date, total) VALUES (?, ?, ?)");
            $stmt->execute([$clientId, $invoiceDate, $total]);
            $invoiceId = $this->pdo->lastInsertId();
            
            $stmt = $this->pdo->prepare("
                INSERT INTO invoice_items 
                (invoice_id, service_id, service_date, quantity, unit_price, from_location, to_location, city, custom_desc) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($services as $service) {
                
                $stmt->execute([
                    $invoiceId,
                    $service['service_id'],
                    $service['service_date'],
                    1,
                    $service['unit_price'],
                    !empty($service['from_location']) ? trim($service['from_location']) : null,
                    !empty($service['to_location']) ? trim($service['to_location']) : null,
                    !empty($service['city']) ? trim($service['city']) : null,
                    !empty($service['custom_desc']) ? trim($service['custom_desc']) : null
                ]);
            }
            
            $this->pdo->commit();
            return $invoiceId;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    public function updateInvoice($invoiceId, $clientId, $invoiceDate, $services, $customTotal = null) {
        try {
            $this->pdo->beginTransaction();
            
            $total = 0;
            if ($customTotal !== null) {
                $total = floatval($customTotal);
            } else {
                foreach ($services as $service) {
                    $total += floatval($service['unit_price']);
                }
            }
            
            $stmt = $this->pdo->prepare("UPDATE invoices SET client_id = ?, invoice_date = ?, total = ? WHERE id = ?");
            $stmt->execute([$clientId, $invoiceDate, $total, $invoiceId]);
            
            $stmt = $this->pdo->prepare("DELETE FROM invoice_items WHERE invoice_id = ?");
            $stmt->execute([$invoiceId]);
            
            $stmt = $this->pdo->prepare("
                INSERT INTO invoice_items 
                (invoice_id, service_id, service_date, quantity, unit_price, from_location, to_location, city, custom_desc) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($services as $service) {
                $serviceDate = null;
                if (!empty($service['service_date']) && $service['service_date'] !== '0000-00-00') {
                    $serviceDate = $service['service_date'];
                }
                
                $stmt->execute([
                    $invoiceId,
                    $service['service_id'],
                    $serviceDate, 
                    1,
                    $service['unit_price'],
                    !empty($service['from_location']) ? trim($service['from_location']) : null,
                    !empty($service['to_location']) ? trim($service['to_location']) : null,
                    !empty($service['city']) ? trim($service['city']) : null,
                    !empty($service['custom_desc']) ? trim($service['custom_desc']) : null
                ]);
            }
            
            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    public function deleteInvoice($invoiceId) {
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("DELETE FROM invoice_items WHERE invoice_id = ?");
            $stmt->execute([$invoiceId]);
            
            $stmt = $this->pdo->prepare("DELETE FROM invoices WHERE id = ?");
            $stmt->execute([$invoiceId]);
            
            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    public function getInvoiceDetails($invoiceId) {
        $stmt = $this->pdo->prepare("SELECT * FROM invoices WHERE id = ?");
        $stmt->execute([$invoiceId]);
        $invoice = $stmt->fetch();
        
        if (!$invoice) {
            return null;
        }
        
        $stmt = $this->pdo->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt->execute([$invoice['client_id']]);
        $client = $stmt->fetch();
        
        $stmt = $this->pdo->prepare("
            SELECT ii.*, s.title, s.description 
            FROM invoice_items ii 
            JOIN services s ON ii.service_id = s.id 
            WHERE ii.invoice_id = ?
        ");
        $stmt->execute([$invoiceId]);
        $items = $stmt->fetchAll();
        
        return [
            'invoice' => $invoice,
            'client' => $client,
            'items' => $items
        ];
    }
    
    public function getAllInvoices() {
        $stmt = $this->pdo->query("
            SELECT i.*, c.name as client_name 
            FROM invoices i 
            JOIN clients c ON i.client_id = c.id 
            ORDER BY i.created_at DESC
        ");
        return $stmt->fetchAll();
    }
    
    public function searchInvoices($searchTerm) {
        $stmt = $this->pdo->prepare("
            SELECT i.*, c.name as client_name 
            FROM invoices i 
            JOIN clients c ON i.client_id = c.id 
            WHERE c.name LIKE ? OR i.id LIKE ? OR i.invoice_date LIKE ?
            ORDER BY i.created_at DESC
        ");
        $searchTerm = "%$searchTerm%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
}
?>
