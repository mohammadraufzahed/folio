<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$engine = (new \Folio\Pdf\Template\TemplateEngine())->enableFolio2Syntax(__DIR__ . '/templates');

$pdf = $engine->renderFile(__DIR__ . '/templates/certificate.folio', [
    'recipient' => 'Alexandra Reed',
    'course' => 'Advanced Product Design',
    'date' => date('F j, Y'),
    'issuer' => 'Acme Design Institute',
]);

file_put_contents(__DIR__ . '/certificate.pdf', $pdf);

echo "Generated certificate.pdf\n";
