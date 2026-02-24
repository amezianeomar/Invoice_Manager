<?php
// Handle CORS for Cross-Origin PDF Fetching
$origin = $_SERVER['HTTP_ORIGIN'] ?? (isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_SCHEME) . '://' . parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) . (parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PORT) ? ':' . parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PORT) : '') : 'http://localhost:5173');
header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= str_pad($invoice['id'], 3, '0', STR_PAD_LEFT) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @media print {
            @page { margin: 0; size: A4; }
            body { margin: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body { 
            font-family: 'Inter', sans-serif;
            font-size: 9pt;
            color: #1f2937;
            background: #f3f4f6;
            -webkit-font-smoothing: antialiased;
        }
        
        .page-container {
            width: 210mm;
            min-height: 297mm;
            background: #fff;
            margin: 40px auto;
            padding: 15mm 15mm;
            position: relative;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            display: flex;
            flex-direction: column;
        }

        @media print {
            .page-container {
                margin: 0;
                box-shadow: none;
                width: 100%;
                height: auto; /* Allow multi-page expansion */
                min-height: 100vh;
            }
            thead { display: table-header-group; }
            tr { page-break-inside: avoid; }
        }

        /* HEADER */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .logo-area img {
            height: 50px; 
            width: auto;
            margin-bottom: 10px;
        }

        .company-meta {
            font-size: 8pt;
            color: #6b7280;
            line-height: 1.4;
        }

        .company-name {
            font-weight: 700;
            font-size: 10pt;
            color: #111;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .invoice-title-area {
            text-align: right;
        }

        .invoice-badge {
            font-size: 24pt;
            font-weight: 800;
            color: #111;
            letter-spacing: -0.5px;
            line-height: 1;
        }

        .invoice-number {
            font-size: 10pt;
            color: #6b7280;
            margin-top: 5px;
            font-weight: 500;
        }

        /* GRID INFO */
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }

        .info-col {
            flex: 1;
        }

        .info-col.right {
            text-align: right;
            flex: 0 0 40%;
        }

        .label {
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #9ca3af;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .client-name {
            font-size: 12pt;
            font-weight: 700;
            color: #111;
            margin-bottom: 4px;
        }

        .client-details {
            font-size: 9pt;
            color: #4b5563;
            line-height: 1.5;
            white-space: pre-line;
        }

        .meta-grid {
            display: inline-grid;
            grid-template-columns: auto auto;
            gap: 4px 20px;
            text-align: right;
        }

        .meta-label {
            color: #9ca3af;
            font-weight: 500;
        }

        .meta-val {
            font-weight: 600;
            color: #1f2937;
        }

        /* TABLE */
        .table-container {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th {
            text-align: left;
            padding: 12px 0;
            font-size: 8pt;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: 600;
            border-bottom: 2px solid #e5e7eb;
        }

        td {
            padding: 16px 0;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
            font-size: 9.5pt;
        }

        .col-desc { width: 60%; padding-right: 20px; }
        .col-qty { width: 10%; text-align: center; }
        .col-date { width: 15%; text-align: right; color: #6b7280; }
        .col-amount { width: 15%; text-align: right; font-weight: 600; }

        .item-title {
            font-weight: 600;
            color: #111;
            margin-bottom: 4px;
        }

        .item-desc {
            font-size: 8.5pt;
            color: #6b7280;
            line-height: 1.4;
        }

        /* TOTALS */
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }

        .totals-box {
            width: 40%;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 9pt;
            color: #4b5563;
        }

        .total-row.grand {
            border-top: 2px solid #111;
            margin-top: 8px;
            padding-top: 15px;
            color: #111;
            font-weight: 700;
            font-size: 14pt;
        }

        .total-row.grand .label {
            color: #111;
            font-size: 10pt;
            margin: 0;
            align-self: center;
        }

        /* FOOTER */
        .footer {
            margin-top: auto;
            padding-top: 30px;
            border-top: 1px solid #e5e7eb;
            font-size: 8pt;
            color: #9ca3af;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .footer-info {
            line-height: 1.5;
        }

        .footer-info b {
            color: #374151;
        }

        .thanks {
            font-size: 14pt;
            font-weight: 700;
            color: #e5e7eb;
            text-transform: uppercase;
        }

        /* UTILS */
        .editable {
            cursor: pointer;
            border-radius: 4px;
            transition: background 0.2s;
        }
        .editable:hover {
            background: #fffbeb;
            box-shadow: 0 0 0 2px #fcd34d;
        }
        @media print {
            .editable:hover { background: none; box-shadow: none; }
        }

        /* Controls */
        .controls {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 12px;
            z-index: 50;
        }

        .btn {
            background: #111;
            color: white;
            padding: 10px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            border: 1px solid #111;
            cursor: pointer;
        }

        .btn-outline {
            background: white;
            color: #111;
        }
    </style>
     <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.editable').forEach(el => {
                el.addEventListener('click', function(e) {
                    if (this.querySelector('input, textarea')) return;
                    
                    const isTextArea = this.dataset.type === 'textarea';
                    const originalText = this.innerText;
                    const field = this.dataset.field;
                    
                    const input = document.createElement(isTextArea ? 'textarea' : 'input');
                    input.value = originalText;
                    // Styling
                    input.style.width = '100%';
                    input.style.font = getComputedStyle(this).font;
                    input.style.border = '1px solid #3b82f6';
                    input.style.padding = '4px';
                    input.style.borderRadius = '4px';
                    input.style.outline = 'none';
                    if (isTextArea) input.rows = 4;

                    const save = async () => {
                        const newVal = input.value;
                        if (newVal !== originalText) {
                            try {
                                await fetch('api/quick_update.php', {
                                    method: 'POST',
                                    headers: {'Content-Type': 'application/json'},
                                    credentials: 'include',
                                    body: JSON.stringify({
                                        entity: this.dataset.entity,
                                        id: this.dataset.id,
                                        field: field,
                                        value: newVal
                                    })
                                });
                                this.innerText = newVal;
                                if (field === 'unit_price' || field === 'quantity') location.reload();
                            } catch (e) {
                                alert('Save failed');
                                this.innerText = originalText;
                            }
                        } else {
                            this.innerText = originalText;
                        }
                    };

                    input.addEventListener('blur', save);
                    input.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' && !isTextArea) input.blur();
                    });

                    this.innerHTML = '';
                    this.appendChild(input);
                    input.focus();
                });
            });
        });
    </script>
</head>
<body>
    <div class="controls no-print">
        <a href="index.php" class="btn btn-outline">Back</a>
        <button onclick="window.print()" class="btn">Download PDF</button>
    </div>

    <div class="page-container">
        <!-- Header -->
        <header class="header">
            <div class="logo-area">
                <img src="eagle-logo.png" alt="Ameziane Tours">
                <div class="company-name">Ameziane Tours</div>
                <div class="company-meta">
                    <b>Morad AMEZIANE</b><br>
                    Premium Transport & Tourism
                </div>
            </div>
            <div class="invoice-title-area">
                <div class="invoice-badge">INVOICE</div>
                <div class="invoice-number">#<?= str_pad($invoice['id'], 3, '0', STR_PAD_LEFT) ?></div>
            </div>
        </header>

        <!-- Info -->
        <section class="info-section">
            <div class="info-col">
                <div class="label">Billed To</div>
                <div class="client-name editable" data-entity="client" data-id="<?= $client['id'] ?>" data-field="name"><?= htmlspecialchars($client['name']) ?></div>
                <div class="client-details editable" data-entity="client" data-id="<?= $client['id'] ?>" data-field="details" data-type="textarea"><?= nl2br(htmlspecialchars($client['details'])) ?></div>
                <?php if ($client['contact']): ?>
                   <div style="margin-top:4px; font-size:9pt; color:#6b7280;"><?= htmlspecialchars($client['contact']) ?></div>
                <?php endif; ?>
            </div>
            <div class="info-col right">
                <div class="meta-grid">
                    <div class="meta-label">Date Issued</div>
                    <div class="meta-val editable" data-entity="invoice" data-id="<?= $invoice['id'] ?>" data-field="invoice_date">
                        <?= date('M d, Y', strtotime($invoice['invoice_date'])) ?>
                    </div>
                    
                    <div class="meta-label">Due Date</div>
                    <div class="meta-val">Upon Receipt</div>
                    
                    <div class="meta-label">Currency</div>
                    <div class="meta-val">MAD (Moroccan Dirham)</div>
                </div>
            </div>
        </section>

        <!-- Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="col-desc">Description</th>
                        <th class="col-qty">Qty</th>
                        <th class="col-date">Date</th>
                        <th class="col-amount">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($items as $item): 
                        $qty = floatval($item['quantity'] ?? 1);
                        $price = floatval($item['unit_price']);
                        $total = $qty * $price;
                    ?>
                    <tr>
                        <td class="col-desc">
                            <div class="item-title"><?= htmlspecialchars($item['title']) ?></div>
                            <div class="item-desc editable" data-entity="item" data-id="<?= $item['id'] ?>" data-field="custom_desc" data-type="textarea">
                                <?php if (!empty($item['custom_desc'])): ?>
                                    <?= nl2br(htmlspecialchars($item['custom_desc'])) ?>
                                <?php else: ?>
                                    <?php if ($item['from_location']): ?>
                                        Transfer: <?= htmlspecialchars($item['from_location']) ?> &rarr; <?= htmlspecialchars($item['to_location']) ?>
                                    <?php elseif ($item['city']): ?>
                                        Service in <?= htmlspecialchars($item['city']) ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="col-qty">
                            <span class="editable" data-entity="item" data-id="<?= $item['id'] ?>" data-field="quantity">
                                <?= $qty == intval($qty) ? intval($qty) : $qty ?>
                            </span>
                        </td>
                        <td class="col-date">
                            <?= $item['service_date'] ? date('M d', strtotime($item['service_date'])) : '' ?>
                        </td>
                        <td class="col-amount">
                            <span class="value">
                                <?= number_format($total, 2) ?>
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
                    <span>Subtotal</span>
                    <span><?= number_format($invoice['total'], 2) ?></span>
                </div>
                <!-- Tax removed as per request -->
                <div class="total-row grand">
                    <span class="label">Total (MAD)</span>
                    <span><?= number_format($invoice['total'], 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-info">
                <b>AMEZIANE TOURS</b><br>
                Tangier, Morocco • ICE: 002886483000094 • IF: 50386259<br>
                +212 662 131 833 • www.amezianetours.com
            </div>
            <div class="thanks">Thank You</div>
        </footer>
    </div>
</body>
</html>
