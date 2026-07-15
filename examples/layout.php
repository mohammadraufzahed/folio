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
                    Heading::h1('Layout Demo'),

                    Heading::h2('Column Layout (Vertical)'),
                    Column::make()
                        ->addChildren([
                            Text::make('First item'),
                            Text::make('Second item'),
                            Text::make('Third item'),
                        ]),

                    Heading::h2('Row Layout (Horizontal)'),
                    Row::make()
                        ->addChildren([
                            Text::make('Item 1'),
                            Text::make('Item 2'),
                            Text::make('Item 3'),
                        ]),

                    Heading::h2('Nested Layouts'),
                    Row::make()
                        ->addChildren([
                            Column::make()
                                ->addChildren([
                                    Text::make('Column 1, Item 1'),
                                    Text::make('Column 1, Item 2'),
                                ]),
                            Column::make()
                                ->addChildren([
                                    Text::make('Column 2, Item 1'),
                                    Text::make('Column 2, Item 2'),
                                ]),
                        ]),
                ])
        )
    )
    ->save(__DIR__ . '/layout.pdf');

echo 'Layout demo PDF generated: ' . __DIR__ . '/layout.pdf' . "\n";
