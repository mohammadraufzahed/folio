<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Document\Pdf;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Text;
use Folio\Pdf\Styling\Alignment;
use Folio\Pdf\Styling\Color;
use Folio\Pdf\Styling\FontWeight;
use Folio\Pdf\Styling\Length;
use Folio\Pdf\Styling\Style;

$brand = Color::hex('#0f172a');
$accent = Color::hex('#2563eb');
$success = Color::hex('#10b981');
$warning = Color::hex('#f59e0b');
$danger = Color::hex('#ef4444');
$surface = Color::hex('#f8fafc');
$text = Color::hex('#334155');
$white = Color::hex('#ffffff');

Pdf::make()
    ->page(
        Page::a4()->withContent(
            Column::make(
                Style::make()->withBackground($white)->withWidth(Length::percent(100.0)),
                [
                    Column::make(
                        Style::make()->withBackground($brand)->withPadding(40.0)->withWidth(Length::percent(100.0)),
                        [Heading::make('Styling Demo', 1, Style::make()->withColor($white)->withFontSize(32.0))]
                    ),
                    Column::make(
                        Style::make()->withPadding(48.0)->withGap(20.0)->withWidth(Length::percent(100.0)),
                        [
                            Column::make(
                                Style::make()->withBackground($surface)->withPadding(24.0)->withGap(12.0)->withWidth(Length::percent(100.0)),
                                [
                                    Text::make('Primary brand color', Style::make()->withColor($brand)->withFontSize(14.0)->withFontWeight(FontWeight::Bold)),
                                    Text::make('Accent sky blue', Style::make()->withColor($accent)->withFontSize(14.0)),
                                    Text::make('Success green', Style::make()->withColor($success)->withFontSize(14.0)),
                                    Text::make('Warning amber', Style::make()->withColor($warning)->withFontSize(14.0)),
                                    Text::make('Danger red', Style::make()->withColor($danger)->withFontSize(14.0)),
                                ]
                            ),
                            Column::make(
                                Style::make()->withBackground($surface)->withPadding(24.0)->withGap(12.0)->withWidth(Length::percent(100.0)),
                                [
                                    Text::make('Left aligned (default)', Style::make()->withColor($text)->withFontSize(12.0)),
                                    Text::make('Centered text', Style::make()->withColor($text)->withFontSize(12.0)->withAlignment(Alignment::Center)),
                                    Text::make('Right aligned', Style::make()->withColor($text)->withFontSize(12.0)->withAlignment(Alignment::Right)),
                                ]
                            ),
                            Column::make(
                                Style::make()->withBackground($surface)->withPadding(24.0)->withGap(8.0)->withWidth(Length::percent(100.0)),
                                [
                                    Text::make('Light weight', Style::make()->withColor($text)->withFontSize(12.0)->withFontWeight(FontWeight::Light)),
                                    Text::make('Regular weight', Style::make()->withColor($text)->withFontSize(12.0)->withFontWeight(FontWeight::Regular)),
                                    Text::make('Bold weight', Style::make()->withColor($text)->withFontSize(12.0)->withFontWeight(FontWeight::Bold)),
                                    Text::make('Black weight', Style::make()->withColor($text)->withFontSize(12.0)->withFontWeight(FontWeight::Black)),
                                ]
                            ),
                        ]
                    ),
                ]
            )
        )
    )
    ->save(__DIR__ . '/styling.pdf');

echo "Generated styling.pdf\n";
