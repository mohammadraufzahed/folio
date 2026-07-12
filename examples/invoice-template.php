<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Template\PhpTemplateCompiler;

// Compile the template
$compiler = new PhpTemplateCompiler();
$template = $compiler->compile(file_get_contents(__DIR__ . '/templates/invoice.folio'));

// Render with data
$pdf = $template([
    'customerName' => 'Jane Smith',
    'customerEmail' => 'jane@example.com',
    'invoiceNumber' => 'INV-002',
    'invoiceDate' => '2024-01-16',
    'items' => [
        ['name' => 'Product A', 'quantity' => 2, 'price' => '99.00'],
        ['name' => 'Product B', 'quantity' => 1, 'price' => '49.00'],
    ]
]);

// Save the PDF
$pdf->save(__DIR__ . '/invoice-template.pdf');

echo "PDF generated: " . __DIR__ . '/invoice-template.pdf' . "\n";
