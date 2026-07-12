<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Template\PhpTemplateCompiler;

// Compile the template
$compiler = new PhpTemplateCompiler();
$template = $compiler->compile(file_get_contents(__DIR__ . '/templates/hello-world.folio'));

// Render with data
$pdf = $template([
    'title' => 'Hello, World!',
    'subtitle' => 'Welcome to Folio PDF'
]);

// Save the PDF
$pdf->save(__DIR__ . '/hello-world-template.pdf');

echo "PDF generated: " . __DIR__ . '/hello-world-template.pdf' . "\n";
