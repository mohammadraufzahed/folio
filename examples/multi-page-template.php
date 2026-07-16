<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$engine = new \Folio\Pdf\Template\TemplateEngine();

$pdf = $engine->renderFile(__DIR__ . '/templates/multi-page.folio', [
    'title' => 'Multi-Page Report',
    'company' => 'Acme Corporation',
]);

file_put_contents(__DIR__ . '/multi-page.pdf', $pdf);

echo "Generated multi-page.pdf\n";
