<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Template\TemplateEngine;

$engine = (new TemplateEngine())->enableFolio2Syntax(__DIR__ . '/templates');

$pdf = $engine->renderFile(__DIR__ . '/templates/styled-document.folio', [
    'title' => 'Styled Document Example',
    'content' => 'This demonstrates styling in templates.',
]);

file_put_contents(__DIR__ . '/styled-document-template.pdf', $pdf);

echo 'PDF generated: ' . __DIR__ . '/styled-document-template.pdf' . "\n";
