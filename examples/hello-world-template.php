<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Template\PhpTemplateCompiler;

$compiler = new PhpTemplateCompiler();

$pdf = $compiler->render(file_get_contents(__DIR__ . '/templates/hello-world.folio'), [
    'title' => 'Hello, World!',
    'subtitle' => 'Welcome to Folio PDF',
]);

$pdf->save(__DIR__ . '/hello-world-template.pdf');

echo 'PDF generated: ' . __DIR__ . '/hello-world-template.pdf' . "\n";
