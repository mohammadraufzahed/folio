<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$engine = new \Folio\Pdf\Template\TemplateEngine();

$pdf = $engine->renderFile(__DIR__ . '/templates/pro-certificate.folio', [
    'recipient' => 'Alexandra Reed',
    'course' => 'Advanced Product Design',
    'date' => date('Y-m-d'),
    'signature' => 'Dr. John Smith',
]);

file_put_contents(__DIR__ . '/pro-certificate.pdf', $pdf);

echo "Generated pro-certificate.pdf\n";
