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

$clients = $invoiceManager->getAllClients();
$services = $invoiceManager->getAllServices();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Facture #<?= $invoice['id'] ?> - Ameziane Tours</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .edit-header {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .service-row {
            background: #f8f9fa;
            border-left: 4px solid #f39c12;
            margin-bottom: 15px;
        }
        .btn-custom {
            background: #f39c12;
            border-color: #f39c12;
            color: white;
        }
        .btn-custom:hover {
            background: #e67e22;
            border-color: #e67e22;
            color: white;
        }
    </style>
</head>
<body>
    <div class="edit-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-edit"></i> Modifier la Facture</h1>
                    <p class="mb-0">Facture #<?= $invoice['id'] ?>/<?= date('Y', strtotime($invoice['invoice_date'])) ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="view-invoice.php?id=<?= $invoice['id'] ?>" class="btn btn-light">
                        <i class="fas fa-eye"></i> Voir Facture
                    </a>
                    <a href="index.php" class="btn btn-outline-light">
                        <i class="fas fa-home"></i> Accueil
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-warning text-white">
                        <h4><i class="fas fa-file-invoice"></i> Modification de la Facture</h4>
                    </div>
                    <div class="card-body">
                        <form method="post" action="update-invoice.php" id="editInvoiceForm">
                            <input type="hidden" name="invoice_id" value="<?= $invoice['id'] ?>">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="invoice_date" class="form-label">Date de Facture</label>
                                    <input type="date" class="form-control" id="invoice_date" name="invoice_date" 
                                           value="<?= $invoice['invoice_date'] ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="client_id" class="form-label">Client</label>
                                    <select class="form-select" id="client_id" name="client_id" required>
                                        <option value="">Choisir un client...</option>
                                        <?php foreach($clients as $c): ?>
                                            <option value="<?= $c['id'] ?>" <?= $c['id'] == $invoice['client_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($c['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div id="servicesContainer">
                                <?php foreach($items as $index => $item): ?>
                                <div class="service-row border rounded p-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0">Service #<?= $index + 1 ?></h6>
                                        <?php if($index > 0): ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeThisRow(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Service</label>
                                            <select class="form-select" name="services[<?= $index ?>][service_id]" 
                                                    onchange="handleServiceChange(this, <?= $index ?>)" required>
                                                <option value="">Choisir un service...</option>
                                                <?php foreach($services as $service): ?>
                                                    <option value="<?= $service['id'] ?>" 
                                                            <?= $service['id'] == $item['service_id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($service['title']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Prix Unitaire (MAD)</label>
                                            <input type="number" class="form-control" 
                                                   name="services[<?= $index ?>][unit_price]" 
                                                   value="<?= $item['unit_price'] ?>"
                                                   step="0.01" min="0" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Détails / Description (Sur la Facture)</label>
                                            <textarea class="form-control" name="services[<?= $index ?>][custom_desc]" rows="2" 
                                                      placeholder="Description détaillée"><?= htmlspecialchars($item['custom_desc'] ?? '') ?></textarea>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Date du Service</label>
                                            <input type="date" class="form-control" 
                                                   name="services[<?= $index ?>][service_date]"
                                                   value="<?= $item['service_date'] ?? '' ?>">
                                            <small class="text-muted">Date à laquelle le service sera effectué</small>
                                        </div>
                                    </div>
                                    
                                    <div class="conditional-fields" id="conditional-<?= $index ?>">
                                        <div class="row transport-fields <?= $item['service_id'] == 1 ? '' : 'd-none' ?>">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">De (From)</label>
                                                <input type="text" class="form-control from-loc" 
                                                       name="services[<?= $index ?>][from_location]"
                                                       value="<?= htmlspecialchars($item['from_location'] ?? '') ?>"
                                                       placeholder="Ville de départ"
                                                       oninput="updateDescription(<?= $index ?>)">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">À (To)</label>
                                                <input type="text" class="form-control to-loc" 
                                                       name="services[<?= $index ?>][to_location]"
                                                       value="<?= htmlspecialchars($item['to_location'] ?? '') ?>"
                                                       placeholder="Ville d'arrivée"
                                                       oninput="updateDescription(<?= $index ?>)">
                                            </div>
                                        </div>
                                        
                                        <div class="row city-fields <?= ($item['service_id'] == 2 || $item['service_id'] == 3) ? '' : 'd-none' ?>">
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Ville</label>
                                                <input type="text" class="form-control city-input" 
                                                       name="services[<?= $index ?>][city]"
                                                       value="<?= htmlspecialchars($item['city'] ?? '') ?>"
                                                       placeholder="Ville du service"
                                                       oninput="updateDescription(<?= $index ?>)">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="d-flex gap-2 mb-3">
                                <button type="button" class="btn btn-outline-primary" onclick="addServiceRow()">
                                    <i class="fas fa-plus"></i> Ajouter un Service
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="removeServiceRow()">
                                    <i class="fas fa-minus"></i> Supprimer le dernier
                                </button>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-custom btn-lg">
                                    <i class="fas fa-save"></i> Sauvegarder les Modifications
                                </button>
                                <a href="view-invoice.php?id=<?= $invoice['id'] ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fas fa-info-circle"></i> Informations</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Attention:</strong> Les modifications seront appliquées immédiatement.
                        </div>
                        
                        <h6>Facture actuelle:</h6>
                        <ul class="list-unstyled">
                            <li><strong>Numéro:</strong> #<?= $invoice['id'] ?>/<?= date('Y', strtotime($invoice['invoice_date'])) ?></li>
                            <li><strong>Client:</strong> <?= htmlspecialchars($client['name']) ?></li>
                            <li><strong>Total:</strong> <?= number_format($invoice['total'], 2) ?> MAD</li>
                            <li><strong>Services:</strong> <?= count($items) ?></li>
                        </ul>

                        <h6 class="mt-3">Services actuels:</h6>
                        <?php foreach($items as $i => $item): ?>
                        <div class="border p-2 mb-2 rounded">
                            <small>
                                <strong><?= htmlspecialchars($item['title']) ?></strong><br>
                                Date: <?= $item['service_date'] ? date('d/m/Y', strtotime($item['service_date'])) : 'Non spécifiée' ?><br>
                                Prix: <?= number_format($item['unit_price'], 2) ?> MAD
                            </small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let serviceCount = <?= count($items) ?>;

        function addServiceRow() {
            const container = document.getElementById('servicesContainer');
            const newRow = document.createElement('div');
            newRow.className = 'service-row border rounded p-3 mb-3';
            newRow.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Service #${serviceCount + 1}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeThisRow(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Service</label>
                        <select class="form-select" name="services[${serviceCount}][service_id]" onchange="handleServiceChange(this, ${serviceCount})" required>
                            <option value="">Choisir un service...</option>
                            <?php foreach($services as $service): ?>
                                <option value="<?= $service['id'] ?>"><?= htmlspecialchars($service['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Prix Unitaire (MAD)</label>
                        <input type="number" class="form-control" name="services[${serviceCount}][unit_price]" 
                               step="0.01" min="0" required>
                    </div>
                </div>
                
                <!-- ALWAYS SHOW DATE FIELD -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Date du Service</label>
                        <input type="date" class="form-control" name="services[${serviceCount}][service_date]" 
                               value="<?= date('Y-m-d') ?>">
                        <small class="text-muted">Date à laquelle le service sera effectué</small>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Détails / Description (Sur la Facture)</label>
                        <textarea class="form-control" name="services[${serviceCount}][custom_desc]" rows="2" 
                                  placeholder="Description détaillée"></textarea>
                    </div>
                </div>

                <div class="conditional-fields" id="conditional-${serviceCount}">
                    <!-- PRIVATE TRANSPORT FIELDS -->
                    <div class="row transport-fields d-none">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">De (From)</label>
                            <input type="text" class="form-control from-loc" name="services[${serviceCount}][from_location]" 
                                   placeholder="Ville de départ"
                                   oninput="updateDescription(${serviceCount})">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">À (To)</label>
                            <input type="text" class="form-control to-loc" name="services[${serviceCount}][to_location]" 
                                   placeholder="Ville d'arrivée"
                                   oninput="updateDescription(${serviceCount})">
                        </div>
                    </div>
                    
                    <!-- CAR HOLD & EXCURSION FIELDS -->
                    <div class="row city-fields d-none">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Ville</label>
                            <input type="text" class="form-control city-input" name="services[${serviceCount}][city]" 
                                   placeholder="Ville du service"
                                   oninput="updateDescription(${serviceCount})">
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(newRow);
            serviceCount++;
        }

        function removeServiceRow() {
            const container = document.getElementById('servicesContainer');
            const rows = container.querySelectorAll('.service-row');
            if (rows.length > 1) {
                container.removeChild(rows[rows.length - 1]);
                serviceCount--;
            }
        }

        function removeThisRow(button) {
            const row = button.closest('.service-row');
            const container = document.getElementById('servicesContainer');
            const rows = container.querySelectorAll('.service-row');
            if (rows.length > 1) {
                row.remove();
            } else {
                alert('Vous devez garder au moins un service dans la facture.');
            }
        }

        function handleServiceChange(select, index) {
            const conditionalDiv = document.getElementById(`conditional-${index}`);
            const transportFields = conditionalDiv.querySelector('.transport-fields');
            const cityFields = conditionalDiv.querySelector('.city-fields');
            
            transportFields.classList.add('d-none');
            cityFields.classList.add('d-none');
            
            const serviceId = select.value;
            
            if (serviceId == '1') {
                transportFields.classList.remove('d-none');
            } else if (serviceId == '2' || serviceId == '3') {
                cityFields.classList.remove('d-none');
            }
            updateDescription(index);
        }
        
        function updateDescription(index) {
            // Need to select based on names because rows can be dynamic
            const serviceSelect = document.getElementsByName(`services[${index}][service_id]`)[0];
            const descField = document.getElementsByName(`services[${index}][custom_desc]`)[0];
            
            if (!serviceSelect || !descField) return;
            
            // Only auto-fill if empty? No, per plan we overwrite if user hasn't heavily customized. 
            // Simple logic: If user is typing in From/To/City inputs, we assume they want the auto-generated string.
            // If they type in the Description box directly, that's their choice (and we don't have a listener for that).
            // So this function is ONLY triggered by the From/To/City inputs.
            
            const serviceId = serviceSelect.value;
            
            if (serviceId == '1') { // Transport
                const fromInput = document.getElementsByName(`services[${index}][from_location]`)[0];
                const toInput = document.getElementsByName(`services[${index}][to_location]`)[0];
                if (fromInput && toInput && fromInput.value && toInput.value) {
                    descField.value = `Transfert ${fromInput.value} -> ${toInput.value}`;
                }
            } else if (serviceId == '3') { // Excursion
                const cityInput = document.getElementsByName(`services[${index}][city]`)[0];
                if (cityInput && cityInput.value) {
                    descField.value = `Excursion ${cityInput.value}`;
                }
            } else if (serviceId == '2') { // Mise a disposition
                const cityInput = document.getElementsByName(`services[${index}][city]`)[0];
                if (cityInput && cityInput.value) {
                     descField.value = `Mise a disposition (${cityInput.value})`;
                }
            }
        }
    </script>
</body>
</html>
