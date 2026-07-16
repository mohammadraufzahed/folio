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
use Folio\Pdf\Styling\Length;
use Folio\Pdf\Styling\Style;

$brand = Color::hex('#0f172a');
$muted = Color::hex('#64748b');
$accent = Color::hex('#2563eb');
$surface = Color::hex('#f8fafc');
$white = Color::hex('#ffffff');

Pdf::make()
    ->page(
        Page::a4()->withContent(
            Column::make(
                Style::make()->withBackground($white)->withWidth(Length::percent(100.0)),
                [
                    Column::make(
                        Style::make()->withBackground($brand)->withPadding(40.0)->withWidth(Length::percent(100.0)),
                        [
                            Heading::make('Hello, World!', 1, Style::make()->withColor($white)->withFontSize(32.0)),
                            Text::make('Welcome to Folio PDF.', Style::make()->withColor(Color::hex('#94a3b8'))->withFontSize(11.0)),
                        ]
                    ),
                    Column::make(
                        Style::make()->withPadding(48.0)->withGap(20.0)->withWidth(Length::percent(100.0)),
                        [
                            Text::make('Generate clean, professional PDFs with a declarative template language.', Style::make()->withColor(Color::hex('#334155'))->withFontSize(11.0)->withLineHeight(1.6)),
                            Column::make(
                                Style::make()->withBackground($surface)->withPadding(24.0)->withGap(12.0)->withWidth(Length::percent(100.0)),
                                [
                                    Row::make(Style::make()->withGap(12.0), [
                                        Column::make(Style::make()->withBackground($white)->withPadding(14.0)->withGap(4.0)->withGrow(1.0), [
                                            Text::make('Pure PHP', Style::make()->withColor($accent)->withFontSize(12.0)->withFontWeight(FontWeight::Bold)),
                                            Text::make('No external dependencies.', Style::make()->withColor($muted)->withFontSize(10.0)),
                                        ]),
                                        Column::make(Style::make()->withBackground($white)->withPadding(14.0)->withGap(4.0)->withGrow(1.0), [
                                            Text::make('Clean DSL', Style::make()->withColor($accent)->withFontSize(12.0)->withFontWeight(FontWeight::Bold)),
                                            Text::make('Declarative templates.', Style::make()->withColor($muted)->withFontSize(10.0)),
                                        ]),
                                    ]),
                                ]
                            ),
                        ]
                    ),
                ]
            )
        )
    )
    ->save(__DIR__ . '/hello-world.pdf');

echo "Generated hello-world.pdf\n";
