<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$engine = new \Folio\Pdf\Template\TemplateEngine();

$items = [];
$total = 0.0;
for ($i = 1; $i <= 3; $i++) {
    $price = $i * 100.0;
    $qty = $i;
    $lineTotal = $price * $qty;
    $total += $lineTotal;
    $items[] = [
        'name' => 'Service ' . $i,
        'quantity' => (string) $qty,
        'price' => '$' . number_format($price, 2),
        'total' => '$' . number_format($lineTotal, 2),
    ];
}

$pdf = $engine->renderFile(__DIR__ . '/templates/themed-invoice.folio', [
    'customerName' => 'Acme Inc.',
    'invoiceNumber' => 'INV-2026-001',
    'invoiceDate' => date('Y-m-d'),
    'items' => $items,
    'total' => '$' . number_format($total, 2),
]);

file_put_contents(__DIR__ . '/themed-invoice.pdf', $pdf);

echo "Generated themed-invoice.pdf\n";
