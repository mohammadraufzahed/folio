<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Template\PhpTemplateCompiler;

$compiler = new PhpTemplateCompiler();

$pdf = $compiler->renderFile(__DIR__ . '/templates/certificate.folio', [
    'recipient' => 'Alexandra Reed',
    'course' => 'Advanced Robotics Engineering',
    'date' => date('F j, Y'),
    'issuer' => 'Acme Robotics Institute',
]);

$out = __DIR__ . '/certificate.pdf';
$pdf->save($out);

echo "Certificate PDF saved: {$out}\n";
