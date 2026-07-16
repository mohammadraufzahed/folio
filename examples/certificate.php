<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Template\TemplateEngine;

$engine = (new TemplateEngine())->enableFolio2Syntax(__DIR__ . '/templates');

$pdf = $engine->renderFile(__DIR__ . '/templates/certificate.folio', [
    'recipient' => 'Alexandra Reed',
    'course' => 'Advanced Robotics Engineering',
    'date' => date('F j, Y'),
    'issuer' => 'Acme Robotics Institute',
]);

file_put_contents(__DIR__ . '/certificate.pdf', $pdf);

echo 'Certificate PDF saved: ' . __DIR__ . '/certificate.pdf' . "\n";
