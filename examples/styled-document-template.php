<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Template\PhpTemplateCompiler;

// Compile the template
$compiler = new PhpTemplateCompiler();
$template = $compiler->compile(file_get_contents(__DIR__ . '/templates/styled-document.folio'));

// Render with data
$pdf = $template([
    'title' => 'Styled Document Example',
    'content' => 'This demonstrates the @style directive in templates.'
]);

// Save the PDF
$pdf->save(__DIR__ . '/styled-document-template.pdf');

echo "PDF generated: " . __DIR__ . '/styled-document-template.pdf' . "\n";
