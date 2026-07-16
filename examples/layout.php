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
use Folio\Pdf\Styling\Length;
use Folio\Pdf\Styling\Style;

$brand = Color::hex('#0f172a');
$muted = Color::hex('#64748b');
$surface = Color::hex('#f8fafc');
$white = Color::hex('#ffffff');
$blue = Color::hex('#e0f2fe');
$green = Color::hex('#dcfce7');
$text = Color::hex('#334155');

Pdf::make()
    ->page(
        Page::a4()->withContent(
            Column::make(
                Style::make()->withBackground($white)->withWidth(Length::percent(100.0)),
                [
                    Column::make(
                        Style::make()->withBackground($brand)->withPadding(40.0)->withWidth(Length::percent(100.0)),
                        [Heading::make('Layout Demo', 1, Style::make()->withColor($white)->withFontSize(32.0))]
                    ),
                    Column::make(
                        Style::make()->withPadding(48.0)->withGap(28.0)->withWidth(Length::percent(100.0)),
                        [
                            Heading::make('Row Layout', 2, Style::make()->withColor($brand)->withFontSize(18.0)),
                            Row::make(Style::make()->withGap(16.0), [
                                Column::make(Style::make()->withGrow(1.0)->withBackground($surface)->withPadding(16.0), [Text::make('Box 1', Style::make()->withColor($text)->withFontSize(11.0))]),
                                Column::make(Style::make()->withGrow(1.0)->withBackground($blue)->withPadding(16.0), [Text::make('Box 2', Style::make()->withColor($text)->withFontSize(11.0))]),
                                Column::make(Style::make()->withGrow(1.0)->withBackground($green)->withPadding(16.0), [Text::make('Box 3', Style::make()->withColor($text)->withFontSize(11.0))]),
                            ]),
                            Heading::make('Column Layout', 2, Style::make()->withColor($brand)->withFontSize(18.0)),
                            Column::make(Style::make()->withGap(12.0)->withWidth(Length::percent(100.0)), [
                                Text::make('First item', Style::make()->withBackground($surface)->withPadding(12.0)->withColor($text)->withWidth(Length::percent(100.0))),
                                Text::make('Second item', Style::make()->withBackground($blue)->withPadding(12.0)->withColor($text)->withWidth(Length::percent(100.0))),
                                Text::make('Third item', Style::make()->withBackground($green)->withPadding(12.0)->withColor($text)->withWidth(Length::percent(100.0))),
                            ]),
                            Heading::make('Grow & Gap', 2, Style::make()->withColor($brand)->withFontSize(18.0)),
                            Row::make(Style::make()->withGap(16.0), [
                                Column::make(Style::make()->withGrow(1.0)->withBackground($surface)->withPadding(16.0), [Text::make('grows 1x', Style::make()->withColor($muted)->withFontSize(10.0))]),
                                Column::make(Style::make()->withGrow(2.0)->withBackground($brand)->withPadding(16.0), [Text::make('grows 2x', Style::make()->withColor($white)->withFontSize(10.0))]),
                            ]),
                        ]
                    ),
                ]
            )
        )
    )
    ->save(__DIR__ . '/layout.pdf');

echo "Generated layout.pdf\n";
