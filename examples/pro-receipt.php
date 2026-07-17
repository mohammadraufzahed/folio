<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$engine = new \Folio\Pdf\Template\TemplateEngine();

$items = [
    ['name' => 'Espresso', 'quantity' => '2', 'price' => '$5.00'],
    ['name' => 'Croissant', 'quantity' => '1', 'price' => '$4.50'],
    ['name' => 'Cold Brew', 'quantity' => '1', 'price' => '$6.50'],
];

$total = 21.0;

$pdf = $engine->renderFile(__DIR__ . '/templates/pro-receipt.folio', [
    'store' => 'Byte Cafe',
    'address' => '456 Coffee Ave, Portland, OR 97204',
    'phone' => '(503) 555-0100',
    'transaction' => 'TXN-' . time(),
    'date' => date('Y-m-d H:i:s'),
    'items' => $items,
    'total' => '$' . number_format($total, 2),
]);

file_put_contents(__DIR__ . '/pro-receipt.pdf', $pdf);

echo "Generated pro-receipt.pdf\n";
