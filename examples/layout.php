<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Document\Pdf;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Row;
use Folio\Pdf\Nodes\Text;
use Folio\Pdf\Styling\Color;
use Folio\Pdf\Styling\FontWeight;
use Folio\Pdf\Styling\Style;

$primary = Color::hex('#1e3a8a');
$muted = Color::hex('#64748b');
$dark = Color::hex('#334155');

Pdf::make()
    ->page(
        Page::a4()->withContent(
            Column::make(
                Style::make()->withBackground(Color::hex('#ffffff'))->withPadding(48.0)->withGap(24.0),
                [
                    Heading::make('Layout Demo', 1, Style::make()->withColor($primary)->withFontSize(32.0)->withFontWeight(FontWeight::Bold)),

                    Heading::make('Row Layout (Horizontal)', 2, Style::make()->withColor($primary)->withFontSize(18.0)->withFontWeight(FontWeight::Bold)),
                    Row::make(Style::make()->withGap(16.0), [
                        Column::make(Style::make()->withGrow(1.0)->withBackground(Color::hex('#f8fafc'))->withPadding(16.0), [
                            Text::make('Box 1', Style::make()->withColor($dark)->withFontSize(11.0)),
                        ]),
                        Column::make(Style::make()->withGrow(1.0)->withBackground(Color::hex('#e0f2fe'))->withPadding(16.0), [
                            Text::make('Box 2', Style::make()->withColor($dark)->withFontSize(11.0)),
                        ]),
                        Column::make(Style::make()->withGrow(1.0)->withBackground(Color::hex('#dcfce7'))->withPadding(16.0), [
                            Text::make('Box 3', Style::make()->withColor($dark)->withFontSize(11.0)),
                        ]),
                    ]),

                    Heading::make('Column Layout (Vertical)', 2, Style::make()->withColor($primary)->withFontSize(18.0)->withFontWeight(FontWeight::Bold)),
                    Column::make(Style::make()->withGap(12.0), [
                        Text::make('First item', Style::make()->withBackground(Color::hex('#f8fafc'))->withPadding(12.0)->withColor($dark)),
                        Text::make('Second item', Style::make()->withBackground(Color::hex('#e0f2fe'))->withPadding(12.0)->withColor($dark)),
                        Text::make('Third item', Style::make()->withBackground(Color::hex('#dcfce7'))->withPadding(12.0)->withColor($dark)),
                    ]),

                    Heading::make('Grow & Gap', 2, Style::make()->withColor($primary)->withFontSize(18.0)->withFontWeight(FontWeight::Bold)),
                    Row::make(Style::make()->withGap(16.0), [
                        Column::make(Style::make()->withGrow(1.0)->withBackground(Color::hex('#f8fafc'))->withPadding(16.0), [
                            Text::make('grows to fill space', Style::make()->withColor($muted)->withFontSize(10.0)),
                        ]),
                        Column::make(Style::make()->withGrow(2.0)->withBackground(Color::hex('#1e3a8a'))->withPadding(16.0), [
                            Text::make('grows 2x', Style::make()->withColor(Color::hex('#ffffff'))->withFontSize(10.0)),
                        ]),
                    ]),
                ]
            )
        )
    )
    ->save(__DIR__ . '/layout.pdf');

echo 'PDF generated: ' . __DIR__ . '/layout.pdf' . "\n";
