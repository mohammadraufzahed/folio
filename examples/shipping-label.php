<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Template\TemplateEngine;

$engine = (new TemplateEngine())->enableFolio2Syntax(__DIR__ . '/templates');

$pdf = $engine->renderFile(__DIR__ . '/templates/shipping-label.folio', [
    'from' => [
        'name' => 'Acme Robotics Inc.',
        'address1' => '123 Business Street',
        'city' => 'Austin',
        'state' => 'TX',
        'zip' => '78701',
    ],
    'to' => [
        'name' => 'NorthGrid Energy',
        'address1' => '4500 Solar Drive',
        'address2' => 'Suite 900',
        'city' => 'Denver',
        'state' => 'CO',
        'zip' => '80202',
        'country' => 'USA',
    ],
    'tracking' => '1Z999AA10123456784',
    'weight' => '4.2 kg',
]);

file_put_contents(__DIR__ . '/shipping-label.pdf', $pdf);

echo 'Shipping label PDF saved: ' . __DIR__ . '/shipping-label.pdf' . "\n";
