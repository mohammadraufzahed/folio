<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Document\Pdf;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Row;
use Folio\Pdf\Nodes\Table;
use Folio\Pdf\Nodes\TableCell;
use Folio\Pdf\Nodes\TableRow;
use Folio\Pdf\Nodes\Text;
use Folio\Pdf\Styling\Alignment;
use Folio\Pdf\Styling\Color;
use Folio\Pdf\Styling\FontWeight;
use Folio\Pdf\Styling\Length;
use Folio\Pdf\Styling\Style;

$brand = Color::hex('#0f172a');
$muted = Color::hex('#64748b');
$text = Color::hex('#334155');
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
                            Row::make(Style::make()->withGap(24.0), [
                                Column::make(Style::make()->withGrow(1.0)->withGap(4.0), [
                                    Heading::make('Acme Corporation', 1, Style::make()->withColor($white)->withFontSize(28.0)),
                                    Text::make('123 Business Street, New York, NY 10001', Style::make()->withColor(Color::hex('#94a3b8'))->withFontSize(10.0)),
                                ]),
                                Column::make(Style::make()->withGap(4.0), [
                                    Text::make('INVOICE', Style::make()->withColor(Color::hex('#94a3b8'))->withFontSize(9.0)->withFontWeight(FontWeight::Bold)),
                                    Heading::make('INV-001', 1, Style::make()->withColor($white)->withFontSize(24.0)->withAlignment(Alignment::Right)),
                                ]),
                            ]),
                        ]
                    ),
                    Column::make(
                        Style::make()->withPadding(48.0)->withGap(28.0)->withWidth(Length::percent(100.0)),
                        [
                            Row::make(Style::make()->withGap(48.0), [
                                Column::make(Style::make()->withGrow(1.0)->withGap(6.0), [
                                    Heading::make('Bill To', 2, Style::make()->withColor($brand)->withFontSize(14.0)),
                                    Text::make('John Doe', Style::make()->withColor($text)->withFontSize(11.0)),
                                    Text::make('john@example.com', Style::make()->withColor($muted)->withFontSize(10.0)),
                                ]),
                                Column::make(Style::make()->withGrow(1.0)->withGap(6.0), [
                                    Heading::make('Invoice Details', 2, Style::make()->withColor($brand)->withFontSize(14.0)),
                                    Row::make(Style::make()->withGap(0.0), [
                                        Text::make('Date: ', Style::make()->withColor($muted)->withFontSize(11.0)),
                                        Text::make(date('Y-m-d'), Style::make()->withColor($text)->withFontSize(11.0)),
                                    ]),
                                    Text::make('Terms: Net 30', Style::make()->withColor($text)->withFontSize(11.0)),
                                ]),
                            ]),
                            Heading::make('Items', 2, Style::make()->withColor($brand)->withFontSize(16.0)),
                            Table::simple([
                                TableRow::header([
                                    TableCell::header(Text::make('Description', Style::make()->withColor($white))),
                                    TableCell::header(Text::make('Quantity', Style::make()->withColor($white)->withAlignment(Alignment::Right))),
                                    TableCell::header(Text::make('Price', Style::make()->withColor($white)->withAlignment(Alignment::Right))),
                                    TableCell::header(Text::make('Total', Style::make()->withColor($white)->withAlignment(Alignment::Right))),
                                ], Style::make()->withBackground($brand)->withFontSize(10.0)),
                                TableRow::make([
                                    TableCell::make(Text::make('Product A', Style::make()->withColor($text)->withFontSize(10.0))),
                                    TableCell::make(Text::make('2', Style::make()->withColor($text)->withFontSize(10.0)->withAlignment(Alignment::Right))),
                                    TableCell::make(Text::make('$99.00', Style::make()->withColor($text)->withFontSize(10.0)->withAlignment(Alignment::Right))),
                                    TableCell::make(Text::make('$198.00', Style::make()->withColor($text)->withFontSize(10.0)->withAlignment(Alignment::Right))),
                                ], Style::make()->withBackground($white)),
                                TableRow::make([
                                    TableCell::make(Text::make('Product B', Style::make()->withColor($text)->withFontSize(10.0))),
                                    TableCell::make(Text::make('1', Style::make()->withColor($text)->withFontSize(10.0)->withAlignment(Alignment::Right))),
                                    TableCell::make(Text::make('$49.00', Style::make()->withColor($text)->withFontSize(10.0)->withAlignment(Alignment::Right))),
                                    TableCell::make(Text::make('$49.00', Style::make()->withColor($text)->withFontSize(10.0)->withAlignment(Alignment::Right))),
                                ], Style::make()->withBackground(Color::hex('#f1f5f9'))),
                            ])->withStyle(Style::make()->withBackground($surface)->withPadding(12.0)->withWidth(Length::percent(100.0))),
                            Row::make(Style::make()->withGap(24.0), [
                                Column::make(Style::make()->withGrow(1.0), []),
                                Column::make(Style::make()->withBackground($surface)->withPadding(20.0)->withGap(8.0)->withWidth(Length::pt(260.0)), [
                                    Row::make(Style::make()->withGap(12.0), [
                                        Text::make('Subtotal', Style::make()->withColor($muted)->withFontSize(11.0)->withGrow(1.0)),
                                        Text::make('$247.00', Style::make()->withColor($text)->withFontSize(11.0)->withFontWeight(FontWeight::Bold)->withAlignment(Alignment::Right)->withGrow(1.0)),
                                    ]),
                                    Row::make(Style::make()->withGap(12.0), [
                                        Text::make('Tax', Style::make()->withColor($muted)->withFontSize(11.0)->withGrow(1.0)),
                                        Text::make('$0.00', Style::make()->withColor($text)->withFontSize(11.0)->withFontWeight(FontWeight::Bold)->withAlignment(Alignment::Right)->withGrow(1.0)),
                                    ]),
                                    Row::make(Style::make()->withGap(12.0), [
                                        Text::make('Total', Style::make()->withColor($brand)->withFontSize(13.0)->withFontWeight(FontWeight::Bold)->withGrow(1.0)),
                                        Text::make('$247.00', Style::make()->withColor($brand)->withFontSize(13.0)->withFontWeight(FontWeight::Bold)->withAlignment(Alignment::Right)->withGrow(1.0)),
                                    ]),
                                ]),
                            ]),
                            Text::make('Thank you for your business.', Style::make()->withColor(Color::hex('#94a3b8'))->withFontSize(9.0)->withAlignment(Alignment::Center)),
                        ]
                    ),
                ]
            )
        )
    )
    ->save(__DIR__ . '/invoice.pdf');

echo "Generated invoice.pdf\n";
