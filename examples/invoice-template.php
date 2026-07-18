<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$engine = new \Folio\Pdf\Template\TemplateEngine();

$items = [];
$total = 0.0;
for ($i = 1; $i <= 5; $i++) {
    $lineTotal = $i * (100 + ($i * 50));
    $total += $lineTotal;
    $items[] = [
        'name' => 'Premium Service ' . $i,
        'quantity' => (string) $i,
        'price' => '$' . number_format(100 + ($i * 50), 2),
        'total' => '$' . number_format($lineTotal, 2),
    ];
}

$pdf = $engine->renderFile(__DIR__ . '/templates/invoice.folio', [
    'customerName' => 'Alice Johnson',
    'customerEmail' => 'alice@example.com',
    'invoiceNumber' => 'INV-2024-0042',
    'invoiceDate' => date('F j, Y'),
    'items' => $items,
    'total' => '$' . number_format($total, 2),
]);

file_put_contents(__DIR__ . '/invoice-template.pdf', $pdf);

echo "Generated invoice-template.pdf\n";
