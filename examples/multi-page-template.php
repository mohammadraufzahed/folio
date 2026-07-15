<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Template\PhpTemplateCompiler;

$compiler = new PhpTemplateCompiler();

$pdf = $compiler->render(file_get_contents(__DIR__ . '/templates/multi-page.folio'), [
    'reportTitle' => 'Annual Report 2024',
    'companyName' => 'Acme Corporation',
]);

$pdf->save(__DIR__ . '/multi-page-template.pdf');

echo 'PDF generated: ' . __DIR__ . '/multi-page-template.pdf' . "\n";
