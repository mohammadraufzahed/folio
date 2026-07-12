<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Template\PhpTemplateCompiler;

// Compile the template
$compiler = new PhpTemplateCompiler();
$template = $compiler->compile(file_get_contents(__DIR__ . '/templates/multi-page.folio'));

// Render with data
$pdf = $template([
    'reportTitle' => 'Annual Report 2024',
    'companyName' => 'Acme Corporation'
]);

// Save the PDF
$pdf->save(__DIR__ . '/multi-page-template.pdf');

echo "PDF generated: " . __DIR__ . '/multi-page-template.pdf' . "\n";
