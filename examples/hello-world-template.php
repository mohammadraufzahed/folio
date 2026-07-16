<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$engine = (new \Folio\Pdf\Template\TemplateEngine())->enableFolio2Syntax(__DIR__ . '/templates');

$pdf = $engine->renderFile(__DIR__ . '/templates/hello-world.folio', [
    'title' => 'Hello, World!',
    'subtitle' => 'Welcome to Folio PDF',
]);

file_put_contents(__DIR__ . '/hello-world-template.pdf', $pdf);

echo "Generated hello-world-template.pdf\n";
