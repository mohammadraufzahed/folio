<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Template\TemplateEngine;

$engine = (new TemplateEngine())->enableFolio2Syntax(__DIR__ . '/templates');

$pdf = $engine->renderFile(__DIR__ . '/templates/invoice.folio', [
    'customerName' => 'Jane Smith',
    'customerEmail' => 'jane@example.com',
    'invoiceNumber' => 'INV-002',
    'invoiceDate' => '2024-01-16',
    'items' => [
        ['name' => 'Product A', 'quantity' => 2, 'price' => '$99.00', 'total' => '$198.00'],
        ['name' => 'Product B', 'quantity' => 1, 'price' => '$49.00', 'total' => '$49.00'],
    ],
]);

file_put_contents(__DIR__ . '/invoice-template.pdf', $pdf);

echo 'PDF generated: ' . __DIR__ . '/invoice-template.pdf' . "\n";
