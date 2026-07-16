<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Template\TemplateEngine;

$engine = (new TemplateEngine())->enableFolio2Syntax(__DIR__ . '/templates');

$pdf = $engine->renderFile(__DIR__ . '/templates/receipt.folio', [
    'store' => 'Byte Cafe',
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

file_put_contents(__DIR__ . '/receipt.pdf', $pdf);

echo 'Receipt PDF saved: ' . __DIR__ . '/receipt.pdf' . "\n";
