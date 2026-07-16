<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Template\TemplateEngine;

$engine = (new TemplateEngine())->enableFolio2Syntax(__DIR__ . '/templates');

$pdf = $engine->renderFile(__DIR__ . '/templates/hello-world.folio', [
    'title' => 'Hello, World!',
    'subtitle' => 'Welcome to Folio PDF',
]);

file_put_contents(__DIR__ . '/hello-world-template.pdf', $pdf);

echo 'PDF generated: ' . __DIR__ . '/hello-world-template.pdf' . "\n";
