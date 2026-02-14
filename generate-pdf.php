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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture #<?= str_pad($invoice['id'], 2, '0', STR_PAD_LEFT) ?> - Ameziane Tours</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        @media print {
            @page { margin: 0; size: A4; }
            body { margin: 0; -webkit-print-color-adjust: exact; }
            .no-print { display: none !important; }
            .page-container { 
                box-shadow: none !important; 
                margin: 0 !important;
                width: 100% !important;
                height: 100vh !important;
                border: none !important;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: 'Montserrat', sans-serif;
            font-size: 12px;
            color: #111;
            background: #E5E5E5;
            padding: 40px 0;
            display: flex;
            justify-content: center;
        }
        
        .page-container {
            width: 210mm;
            min-height: 297mm;
            background: #fff;
            position: relative;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 0;
            display: flex;
            flex-direction: column;
        }

        .border-frame {
            position: absolute;
            top: 15mm;
            left: 15mm;
            right: 15mm;
            bottom: 15mm;
            border: 1px solid #000;
            pointer-events: none;
            z-index: 10;
        }

        .border-frame::after {
            content: "";
            position: absolute;
            top: 2px; left: 2px; right: 2px; bottom: 2px;
            border: 1px solid #000;
        }

        .content {
            padding: 25mm 25mm;
            flex: 1;
            position: relative;
            z-index: 20;
        }

        /* HEADER */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 60px;
            padding-bottom: 20px;
            border-bottom: 2px solid #000;
        }

        .company-branding {
            text-align: left;
        }

        .company-logo {
            width: 80px;
            margin-bottom: 1px;
            filter: grayscale(100%) contrast(120%);
        }

        h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 32px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 700;
            color: #000;
            margin-bottom: 5px;
            line-height: 1;
        }

        .subtitle {
            font-family: 'Montserrat', sans-serif;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: #555;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-big-text {
            font-family: 'Cormorant Garamond', serif;
            font-size: 48px;
            color: #000;
            line-height: 0.8;
            margin-bottom: 1px; /* Tweak spacing */
            font-weight: 400;
        }

        .invoice-number-row {
            font-size: 14px;
            font-weight: 600;
            font-family: 'Cormorant Garamond', serif;
            letter-spacing: 1px;
            margin-top: 5px;
        }

        /* INFO GRID */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 50px;
        }

        .info-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #666;
            margin-bottom: 8px;
            font-weight: 600;
            border-bottom: 1px solid #ddd;
            padding-bottom: 4px;
            display: inline-block;
            min-width: 150px;
        }

        .info-box {
            font-family: 'Cormorant Garamond', serif;
            font-size: 16px;
            font-weight: 600;
            color: #000;
            line-height: 1.4;
        }
        
        .info-box p {
            margin-bottom: 4px;
        }

        .text-regular {
            font-weight: 400;
            font-size: 15px;
            color: #333;
        }

        /* TABLE */
        .table-container {
            margin-bottom: 60px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            font-family: 'Montserrat', sans-serif;
            font-size: 9px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 12px 10px;
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
        }

        td {
            padding: 15px 10px;
            border-bottom: 1px solid #eee;
            font-family: 'Cormorant Garamond', serif;
            font-size: 15px;
            color: #111;
            vertical-align: top;
        }

        .col-qty { width: 5%; text-align: center; }
        .col-date { width: 15%; }
        .col-desc { width: 50%; }
        .col-price { width: 20%; text-align: right; }

        .service-title {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 4px;
        }
        
        .service-details {
            font-family: 'Montserrat', sans-serif;
            font-size: 10px;
            color: #555;
            line-height: 1.5;
        }

        .amount-cell {
            font-weight: 700;
            font-feature-settings: "tnum";
            font-variant-numeric: tabular-nums;
        }

        /* TOTALS */
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 60px;
        }

        .totals-box {
            width: 300px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .total-row.final {
            border-bottom: 4px double #000;
            border-top: 1px solid #000;
            padding: 15px 0;
            margin-top: 10px;
        }

        .total-label {
            font-family: 'Montserrat', sans-serif;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .total-value {
            font-family: 'Cormorant Garamond', serif;
            font-size: 24px;
            font-weight: 700;
        }

        /* FOOTER */
        .footer {
            margin-top: auto;
            text-align: center;
            font-family: 'Montserrat', sans-serif;
            font-size: 9px;
            color: #666;
            letter-spacing: 0.5px;
            line-height: 1.8;
            border-top: 1px solid #ddd;
            padding-top: 30px;
        }

        .footer strong {
            color: #000;
            text-transform: uppercase;
        }

        /* CONTROLS */
        .controls {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
        }

        .btn {
            background: #000;
            color: #fff;
            border: none;
            padding: 12px 24px;
            font-family: 'Montserrat', sans-serif;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            font-weight: 600;
        }

        .btn:hover {
            background: #333;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid #000;
            color: #000;
        }
        
        .btn-outline:hover {
            background: #000;
            color: #fff;
        }
    </style>
    <style>
        /* Inline Editing Styles */
        .editable {
            cursor: pointer;
            transition: background-color 0.2s, color 0.2s;
            border-bottom: 1px dashed #ccc;
        }
        
        .editable:hover {
            background-color: #fffde7;
            border-bottom-color: #f1c40f;
            color: #d35400;
        }

        .editing-input {
            font-family: inherit;
            font-size: inherit;
            font-weight: inherit;
            color: inherit;
            background: #fff;
            border: 1px solid #3498db;
            padding: 2px 4px;
            width: 100%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            outline: none;
        }

        @media print {
            .editable { cursor: default; border-bottom: none; }
            .editable:hover { background-color: transparent; color: inherit; border-bottom-color: transparent; }
            .no-print { display: none !important; }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editableElements = document.querySelectorAll('.editable');

            editableElements.forEach(el => {
                el.addEventListener('click', function(e) {
                    if (this.isContentEditable || this.querySelector('input, textarea')) return;

                    const entity = this.dataset.entity;
                    const id = this.dataset.id;
                    const field = this.dataset.field;
                    const originalText = this.innerText.trim();
                    const isTextarea = this.dataset.type === 'textarea';

                    //Create Input
                    const input = isTextarea ? document.createElement('textarea') : document.createElement('input');
                    input.value = originalText;
                    input.className = 'editing-input';
                    if (isTextarea) input.rows = 4;
                    
                    // Replace text with input
                    this.innerHTML = '';
                    this.appendChild(input);
                    input.focus();

                    // Save on Blur or Enter
                    const save = async () => {
                        const newValue = input.value.trim();
                        if (newValue === originalText) {
                            this.innerHTML = originalText; 
                            return;
                        }

                        try {
                            const response = await fetch('api/quick_update.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ entity, id, field, value: newValue })
                            });
                            
                            const res = await response.json();
                            
                            if (res.success) {
                                this.innerHTML = newValue.replace(/\n/g, '<br>');
                                this.style.backgroundColor = '#d4edda';
                                setTimeout(() => this.style.backgroundColor = '', 1000);
                                if (field === 'unit_price' || field === 'quantity') {
                                    location.reload(); 
                                }
                            } else {
                                alert('Error saving: ' + res.message);
                                this.innerHTML = originalText;
                            }
                        } catch (err) {
                            console.error(err);
                            alert('Failed to save.');
                            this.innerHTML = originalText;
                        }
                    };

                    input.addEventListener('blur', save);
                    input.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' && !isTextarea) {
                            input.blur(); 
                        }
                    });
                    e.stopPropagation();
                });
            });
        });
    </script>
</head>
<body>
    <div class="controls no-print">
        <a href="index.php" class="btn btn-outline">Retour</a>
        <button onclick="window.print()" class="btn">Imprimer PDF</button>
    </div>

    <div class="page-container">
        <div class="border-frame"></div>
        <div class="content">
            <!-- Header -->
            <header class="header">
                <div class="company-branding">
                    <img src="eagle-logo.png" alt="Logo" class="company-logo">
                    <h1>AMEZIANE TOURS</h1>
                    <div class="subtitle">PREMIUM TRANSPORT & TOURISM</div>
                </div>
                <div class="invoice-title">
                    <div class="invoice-big-text">INVOICE</div>
                    <div class="invoice-number-row">N° <?= str_pad($invoice['id'], 3, '0', STR_PAD_LEFT) ?> / <?= date('Y') ?></div>
                </div>
            </header>

            <!-- Info Grid -->
            <div class="info-grid">
                <div class="col">
                    <div class="info-label">BILLED TO</div>
                    <div class="info-box">
                        <div class="editable" data-entity="client" data-id="<?= $client['id'] ?>" data-field="name">
                            <?= htmlspecialchars($client['name']) ?>
                        </div>
                        <span class="text-regular">
                            <div class="editable" data-entity="client" data-id="<?= $client['id'] ?>" data-field="details" data-type="textarea">
                                <?= nl2br(htmlspecialchars($client['details'])) ?>
                            </div>
                            <?php if ($client['contact']): ?>
                                <br><?= htmlspecialchars($client['contact']) ?>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
                <div class="col" style="text-align: right;">
                    <div class="info-label">DETAILS</div>
                    <div class="info-box">
                        <p>Date: <span class="text-regular editable" data-entity="invoice" data-id="<?= $invoice['id'] ?>" data-field="invoice_date"><?= date('Y-m-d', strtotime($invoice['invoice_date'])) ?></span></p>
                        <p>Due Date: <span class="text-regular">Upon Receipt</span></p>
                        <p>Currency: <span class="text-regular">MAD (Moroccan Dirham)</span></p>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th class="col-qty">QTY</th>
                            <th class="col-desc">DESCRIPTION</th>
                            <th class="col-date">SERVICE DATE</th>
                            <th class="col-price">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="col-qty">
                                <span class="editable" data-entity="item" data-id="<?= $item['id'] ?>" data-field="quantity">
                                    <?= $item['quantity'] ?>
                                </span>
                            </td>
                            <td class="col-desc">
                                <div class="service-title"><?= htmlspecialchars($item['title']) ?></div>
                                <div class="service-details editable" data-entity="item" data-id="<?= $item['id'] ?>" data-field="custom_desc" data-type="textarea">
                                <?php if (!empty($item['custom_desc'])): ?>
                                    <?= nl2br(htmlspecialchars($item['custom_desc'])) ?>
                                <?php elseif ($item['from_location']): ?>
                                    Itinerary: <?= htmlspecialchars($item['from_location']) ?> &rarr; <?= htmlspecialchars($item['to_location']) ?>
                                <?php elseif ($item['city']): ?>
                                    Location: <?= htmlspecialchars($item['city']) ?>
                                <?php else: ?>
                                    Click to add description...
                                <?php endif; ?>
                                </div>
                            </td>
                            <td class="col-date">
                                <?= $item['service_date'] ? date('M d, Y', strtotime($item['service_date'])) : '-' ?>
                            </td>
                            <td class="col-price amount-cell">
                                <span class="editable" data-entity="item" data-id="<?= $item['id'] ?>" data-field="unit_price">
                                    <?= number_format($item['unit_price'], 2, '.', '') ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Totals -->
            <div class="totals-section">
                <div class="totals-box">
                    <div class="total-row">
                        <span class="total-label">Subtotal</span>
                        <span class="total-value" style="font-size: 18px;"><?= number_format($invoice['total'], 2) ?></span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">Tax (0%)</span>
                        <span class="total-value" style="font-size: 18px;">0.00</span>
                    </div>
                    <div class="total-row final">
                        <span class="total-label" style="font-size: 14px; align-self: center;">TOTAL (MAD)</span>
                        <span class="total-value"><?= number_format($invoice['total'], 2) ?></span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="footer">
                <p><strong>AMEZIANE TOURS MORAD</strong></p>
                <p>Registered in Tangier, Morocco • ICE: 002886483000094 • IF: 50386259</p>
                <p>Address: Tangier, Morocco • Phone: +212 662 131 833 • Web: www.amezianetours.com</p>
                <br>
                <p style="font-style: italic; font-family: 'Cormorant Garamond', serif;">"Quality in every mile"</p>
            </footer>
        </div>
    </div>
</body>
</html>
