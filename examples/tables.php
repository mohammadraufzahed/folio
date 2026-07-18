<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$engine = new \Folio\Pdf\Template\TemplateEngine();

$templates = [
    'simple-table.folio' => 'simple-table',
    'multi-header-table.folio' => 'multi-header-table',
    'multi-level-table.folio' => 'multi-level-table',
    'nested-table.folio' => 'nested-table',
    'financial-table.folio' => 'financial-table',
];

foreach ($templates as $template => $name) {
    $pdf = $engine->renderFile(__DIR__ . '/templates/' . $template);
    file_put_contents(__DIR__ . '/' . $name . '.pdf', $pdf);
    echo "Generated $name.pdf\n";
}
