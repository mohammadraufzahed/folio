<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$engine = (new \Folio\Pdf\Template\TemplateEngine())->enableFolio2Syntax(__DIR__ . '/templates');

$pdf = $engine->renderFile(__DIR__ . '/templates/styled-document.folio', [
    'title' => 'Styled Document',
    'content' => 'Demonstration of color, alignment, font sizes and spacing.',
]);

file_put_contents(__DIR__ . '/styled-document.pdf', $pdf);

echo "Generated styled-document.pdf\n";
