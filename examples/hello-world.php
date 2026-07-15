<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Document\Pdf;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Text;

Pdf::make()
    ->page(
        Page::a4()->withContent(
            Column::make()
                ->addChildren([
                    Heading::h1('Hello, World!'),
                    Text::make('This is a modern PDF generated with Folio PDF.'),
                    Text::make('Built with PHP 8.3+ and pure PHP.'),
                ])
        )
    )
    ->save(__DIR__ . '/hello-world.pdf');

echo 'PDF generated: ' . __DIR__ . '/hello-world.pdf' . "\n";
