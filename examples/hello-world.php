<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Document\Pdf;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Text;
use Folio\Pdf\Styling\Color;
use Folio\Pdf\Styling\FontWeight;
use Folio\Pdf\Styling\Style;

Pdf::make()
    ->page(
        Page::a4()->withContent(
            Column::make(
                Style::make()
                    ->withBackground(Color::hex('#ffffff'))
                    ->withPadding(64.0)
                    ->withGap(24.0),
                [
                    Heading::make('Hello, World!', 1, Style::make()
                        ->withColor(Color::hex('#1e3a8a'))
                        ->withFontSize(36.0)
                        ->withFontWeight(FontWeight::Bold)),
                    Text::make('Welcome to Folio PDF.', Style::make()
                        ->withColor(Color::hex('#64748b'))
                        ->withFontSize(14.0)),
                    Column::make(
                        Style::make()
                            ->withBackground(Color::hex('#f8fafc'))
                            ->withPadding(24.0)
                            ->withGap(12.0),
                        [
                            Text::make('Built with pure PHP.', Style::make()
                                ->withColor(Color::hex('#334155'))
                                ->withFontSize(11.0)),
                            Text::make('No external dependencies required.', Style::make()
                                ->withColor(Color::hex('#334155'))
                                ->withFontSize(11.0)),
                        ]
                    ),
                ]
            )
        )
    )
    ->save(__DIR__ . '/hello-world.pdf');

echo 'PDF generated: ' . __DIR__ . '/hello-world.pdf' . "\n";
