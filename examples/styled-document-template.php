<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Template\PhpTemplateCompiler;

$compiler = new PhpTemplateCompiler();

$pdf = $compiler->render(file_get_contents(__DIR__ . '/templates/styled-document.folio'), [
    'title' => 'Styled Document Example',
    'content' => 'This demonstrates styling in templates.',
]);

$pdf->save(__DIR__ . '/styled-document-template.pdf');

echo 'PDF generated: ' . __DIR__ . '/styled-document-template.pdf' . "\n";
