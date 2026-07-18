<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$engine = new \Folio\Pdf\Template\TemplateEngine();

$pdf = $engine->renderFile(__DIR__ . '/templates/receipt.folio', [
    'store' => 'Byte Cafe',
    'address' => '456 Coffee Ave, Portland, OR 97204',
    'date' => date('Y-m-d H:i'),
    'items' => [
        ['quantity' => '1', 'name' => 'Espresso', 'price' => '$3.50'],
        ['quantity' => '2', 'name' => 'Croissant', 'price' => '$8.00'],
        ['quantity' => '1', 'name' => 'Latte', 'price' => '$4.50'],
    ],
    'total' => '$16.00',
]);

file_put_contents(__DIR__ . '/receipt.pdf', $pdf);

echo "Generated receipt.pdf\n";
