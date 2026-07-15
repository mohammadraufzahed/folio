<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Template\PhpTemplateCompiler;

$compiler = new PhpTemplateCompiler();
$templatesDir = __DIR__ . '/templates';

$templates = [
    'simple-table.folio' => 'simple-table.pdf',
    'multi-header-table.folio' => 'multi-header-table.pdf',
    'nested-table.folio' => 'nested-table.pdf',
    'multi-level-table.folio' => 'multi-level-table.pdf',
    'financial-table.folio' => 'financial-table.pdf',
];

foreach ($templates as $templateFile => $pdfFile) {
    $path = $templatesDir . '/' . $templateFile;
    echo "Compiling {$templateFile}...\n";

    $pdf = $compiler->renderFile($path);
    $out = __DIR__ . '/' . $pdfFile;
    $pdf->save($out);
    echo "  -> {$out}\n";
}

echo "\nAll template-based table PDFs generated.\n";
echo "Templates: examples/templates/*.folio\n";
