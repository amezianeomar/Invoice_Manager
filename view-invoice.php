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
$invoiceManager = new InvoiceManager();
$data = $invoiceManager->getInvoiceDetails($invoiceId);

if (!$data) {
    header('Location: index.php');
    exit;
}

$invoice = $data['invoice'];
$client = $data['client'];
$items = $data['items'];

error_log("Invoice items from database: " . print_r($items, true));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture #<?= $invoice['id'] ?> - Ameziane Tours</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .action-buttons {
            position: sticky;
            top: 20px;
            z-index: 100;
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .invoice-card {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: none;
            border-radius: 15px;
        }
        .invoice-header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .status-badge {
            font-size: 0.9em;
            padding: 8px 15px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <!-- Action Buttons -->
        <div class="action-buttons">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-0">
                        <i class="fas fa-file-invoice text-primary"></i> 
                        Facture #<?= $invoice['id'] ?>/<?= date('Y', strtotime($invoice['invoice_date'])) ?>
                        <span class="badge bg-success status-badge ms-2">
                            <i class="fas fa-check-circle"></i> Active
                        </span>
                    </h4>
                    <small class="text-muted">
                        Créée le <?= date('d/m/Y à H:i', strtotime($invoice['created_at'])) ?>
                    </small>
                </div>
                <div class="btn-group" role="group">
                    <a href="generate-pdf.php?id=<?= $invoice['id'] ?>" class="btn btn-primary" target="_blank">
                        <i class="fas fa-file-pdf"></i> PDF
                    </a>
                    <a href="edit-invoice.php?id=<?= $invoice['id'] ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Invoice Details -->
                <div class="card invoice-card">
                    <div class="card-header invoice-header">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-1">
                                    <i class="fas fa-building"></i> Ameziane Tours
                                </h5>
                                <small>Service Touristique & Transport Personnel</small>
                            </div>
                            <div class="col-md-6 text-end">
                                <h3 class="mb-0"><?= number_format($invoice['total'], 2) ?> MAD</h3>
                                <small>Total TTC</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-primary">
                                    <i class="fas fa-calendar"></i> Informations de la facture
                                </h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td><strong>Date de facture:</strong></td>
                                        <td><?= date('d/m/Y', strtotime($invoice['invoice_date'])) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Numéro:</strong></td>
                                        <td>#<?= $invoice['id'] ?>/<?= date('Y', strtotime($invoice['invoice_date'])) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Statut:</strong></td>
                                        <td><span class="badge bg-success">Payée</span></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">
                                    <i class="fas fa-user"></i> Informations client
                                </h6>
                                <div class="bg-light p-3 rounded">
                                    <h6 class="mb-2"><?= htmlspecialchars($client['name']) ?></h6>
                                    <p class="mb-1 small"><?= htmlspecialchars($client['details']) ?></p>
                                    <?php if ($client['contact']): ?>
                                        <p class="mb-0 small">
                                            <i class="fas fa-phone"></i> <?= htmlspecialchars($client['contact']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <h6 class="text-primary mb-3">
                            <i class="fas fa-list"></i> Services facturés
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th><i class="fas fa-cog"></i> Service</th>
                                        <th><i class="fas fa-calendar"></i> Date</th>
                                        <th><i class="fas fa-info-circle"></i> Détails</th>
                                        <th class="text-end"><i class="fas fa-euro-sign"></i> Prix (MAD)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($item['title']) ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars($item['description']) ?></small>
                                        </td>
                                        <td>
                                            <?php 
                                            // Better date handling - check for NULL, empty, or invalid dates
                                            if (!empty($item['service_date']) && $item['service_date'] !== '0000-00-00' && $item['service_date'] !== null): 
                                                try {
                                                    $dateObj = new DateTime($item['service_date']);
                                                    echo '<span class="badge bg-info">' . $dateObj->format('d/m/Y') . '</span>';
                                                } catch (Exception $e) {
                                                    echo '<span class="text-muted">Date invalide</span>';
                                                }
                                            else: 
                                            ?>
                                                <span class="text-muted">Non spécifiée</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($item['from_location'] && $item['to_location']): ?>
                                                <i class="fas fa-route text-primary"></i>
                                                <strong>De:</strong> <?= htmlspecialchars($item['from_location']) ?><br>
                                                <strong>À:</strong> <?= htmlspecialchars($item['to_location']) ?>
                                            <?php elseif ($item['city']): ?>
                                                <i class="fas fa-map-marker-alt text-success"></i>
                                                <strong>Ville:</strong> <?= htmlspecialchars($item['city']) ?>
                                            <?php else: ?>
                                                <span class="text-muted">Aucun détail spécifique</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <strong class="text-success"><?= number_format($item['unit_price'], 2) ?></strong>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="3" class="text-end">Total TTC:</th>
                                        <th class="text-end text-success">
                                            <?= number_format($invoice['total'], 2) ?> MAD
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-bolt"></i> Actions rapides</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="edit-invoice.php?id=<?= $invoice['id'] ?>" class="btn btn-outline-warning">
                                <i class="fas fa-edit"></i> Modifier cette facture
                            </a>
                            <a href="generate-pdf.php?id=<?= $invoice['id'] ?>" class="btn btn-outline-primary" target="_blank">
                                <i class="fas fa-download"></i> Télécharger PDF
                            </a>
                            <button class="btn btn-outline-success">
                                <i class="fas fa-envelope"></i> Envoyer par email
                            </button>
                            <button class="btn btn-outline-info">
                                <i class="fas fa-copy"></i> Dupliquer facture
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Statistiques</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <h4 class="text-primary"><?= count($items) ?></h4>
                                <small>Services</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success"><?= number_format($invoice['total'], 2) ?></h4>
                                <small>MAD</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-bug"></i> Debug Info</h6>
                    </div>
                    <div class="card-body">
                        <small>
                            <?php foreach ($items as $item): ?>
                                <strong>Service:</strong> <?= $item['title'] ?><br>
                                <strong>Date brute:</strong> <?= var_export($item['service_date'], true) ?><br>
                                <strong>Type:</strong> <?= gettype($item['service_date']) ?><br>
                                <hr>
                            <?php endforeach; ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle"></i> Confirmer la suppression
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                        <h5>Êtes-vous sûr de vouloir supprimer cette facture ?</h5>
                        <p class="text-muted">
                            Facture #<?= $invoice['id'] ?>/<?= date('Y', strtotime($invoice['invoice_date'])) ?><br>
                            Client: <?= htmlspecialchars($client['name']) ?><br>
                            Montant: <?= number_format($invoice['total'], 2) ?> MAD
                        </p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Attention:</strong> Cette action est irréversible !
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <a href="delete-invoice.php?id=<?= $invoice['id'] ?>" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Oui, supprimer
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
