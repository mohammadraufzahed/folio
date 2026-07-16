<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Template\PhpTemplateCompiler;

$compiler = new PhpTemplateCompiler();

$pdf = $compiler->renderFile(__DIR__ . '/templates/receipt.folio', [
    'store' => 'Byte Café',
    'address' => '123 Coffee Lane, Tech City',
    'date' => date('Y-m-d H:i'),
    'items' => [
        ['quantity' => 2, 'name' => 'Espresso', 'price' => '$3.50'],
        ['quantity' => 1, 'name' => 'Croissant', 'price' => '$2.75'],
        ['quantity' => 1, 'name' => 'Latte', 'price' => '$4.50'],
        ['quantity' => 3, 'name' => 'Muffin', 'price' => '$2.25'],
    ],
    'total' => '$18.00',
]);

$out = __DIR__ . '/receipt.pdf';
$pdf->save($out);

echo "Receipt PDF saved: {$out}\n";
