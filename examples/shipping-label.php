<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$engine = (new \Folio\Pdf\Template\TemplateEngine())->enableFolio2Syntax(__DIR__ . '/templates');

$pdf = $engine->renderFile(__DIR__ . '/templates/shipping-label.folio', [
    'to' => [
        'name' => 'Alice Johnson',
        'address1' => '789 Maple Street',
        'city' => 'Austin',
        'state' => 'TX',
        'zip' => '78701',
        'country' => 'United States',
    ],
    'from' => [
        'name' => 'Acme Warehouse',
        'address1' => '100 Industrial Blvd',
        'city' => 'Dallas',
        'state' => 'TX',
        'zip' => '75201',
    ],
    'tracking' => '1Z999AA10123456784',
    'weight' => '2.4 lbs',
]);

file_put_contents(__DIR__ . '/shipping-label.pdf', $pdf);

echo "Generated shipping-label.pdf\n";
