<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Template\TemplateEngine;

$engine = (new TemplateEngine())->enableFolio2Syntax(__DIR__ . '/templates');

$templates = [
    'simple-table.folio' => 'simple-table.pdf',
    'multi-header-table.folio' => 'multi-header-table.pdf',
    'multi-level-table.folio' => 'multi-level-table.pdf',
    'nested-table.folio' => 'nested-table.pdf',
    'financial-table.folio' => 'financial-table.pdf',
];

foreach ($templates as $templateFile => $pdfFile) {
    echo "Rendering {$templateFile}...\n";
    $pdf = $engine->renderFile(__DIR__ . '/templates/' . $templateFile);
    file_put_contents(__DIR__ . '/' . $pdfFile, $pdf);
    echo "  -> {$pdfFile}\n";
}

echo "\nAll template-based table PDFs generated.\n";
