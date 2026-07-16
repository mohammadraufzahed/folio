<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Template\TemplateEngine;

$engine = (new TemplateEngine())->enableFolio2Syntax(__DIR__ . '/templates');

$pdf = $engine->renderFile(__DIR__ . '/templates/multi-page.folio', [
    'reportTitle' => 'Annual Report 2024',
    'companyName' => 'Acme Corporation',
]);

file_put_contents(__DIR__ . '/multi-page-template.pdf', $pdf);

echo 'PDF generated: ' . __DIR__ . '/multi-page-template.pdf' . "\n";
