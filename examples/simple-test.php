<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Document\Pdf;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Text;
use Folio\Pdf\Styling\Color;
use Folio\Pdf\Styling\Style;

Pdf::make()
    ->page(
        Page::a4()->withContent(
            Column::make(
                Style::make()->withBackground(Color::hex('#ffffff'))->withPadding(48.0)->withGap(16.0),
                [
                    Heading::make('Simple Test', 1, Style::make()->withColor(Color::hex('#1e3a8a'))->withFontSize(32.0)),
                    Text::make('This is a minimal styled PDF generated with Folio.', Style::make()->withColor(Color::hex('#334155'))->withFontSize(11.0)),
                ]
            )
        )
    )
    ->save(__DIR__ . '/simple-test.pdf');

echo 'Simple test PDF generated: ' . __DIR__ . '/simple-test.pdf' . "\n";
