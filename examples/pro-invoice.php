<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$engine = new \Folio\Pdf\Template\TemplateEngine();

$items = [];
$total = 0.0;
for ($i = 1; $i <= 4; $i++) {
    $price = 100.0 * $i;
    $qty = 1;
    $lineTotal = $price * $qty;
    $total += $lineTotal;
    $items[] = [
        'name' => 'Pro Service ' . $i,
        'quantity' => (string) $qty,
        'price' => '$' . number_format($price, 2),
        'total' => '$' . number_format($lineTotal, 2),
    ];
}

$pdf = $engine->renderFile(__DIR__ . '/templates/pro-invoice.folio', [
    'companyName' => 'Acme Corporation',
    'companyAddress' => '123 Business Street, New York, NY 10001',
    'documentLabel' => 'INVOICE',
    'documentNumber' => 'INV-2026-001',
    'customerName' => 'Acme Inc.',
    'customerEmail' => 'billing@acme.test',
    'invoiceDate' => date('Y-m-d'),
    'items' => $items,
    'total' => '$' . number_format($total, 2),
]);

file_put_contents(__DIR__ . '/pro-invoice.pdf', $pdf);

echo "Generated pro-invoice.pdf\n";
