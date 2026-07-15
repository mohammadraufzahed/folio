<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Document\Pdf;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Row;
use Folio\Pdf\Nodes\Text;

Pdf::make()
    ->page(
        Page::a4()->withContent(
            Column::make()
                ->addChildren([
                    Heading::h1('INVOICE'),
                    Text::make('Invoice #: INV-001'),
                    Text::make('Date: ' . date('Y-m-d')),

                    Heading::h2('Bill To:'),
                    Text::make('John Doe'),
                    Text::make('123 Main Street'),
                    Text::make('City, State 12345'),

                    Heading::h2('Items:'),
                    Column::make()
                        ->addChildren([
                            Row::make()
                                ->addChildren([
                                    Text::make('Service A - $100.00'),
                                ]),
                            Row::make()
                                ->addChildren([
                                    Text::make('Service B - $150.00'),
                                ]),
                            Row::make()
                                ->addChildren([
                                    Text::make('Service C - $75.00'),
                                ]),
                        ]),

                    Heading::h2('Total: $325.00'),
                    Text::make('Thank you for your business!'),
                ])
        )
    )
    ->save(__DIR__ . '/invoice.pdf');

echo 'Invoice PDF generated: ' . __DIR__ . '/invoice.pdf' . "\n";
