<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Document\Pdf;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Text;
use Folio\Pdf\Styling\Color;
use Folio\Pdf\Styling\Length;
use Folio\Pdf\Styling\Style;

$brand = Color::hex('#0f172a');
$text = Color::hex('#334155');
$surface = Color::hex('#f8fafc');
$white = Color::hex('#ffffff');

Pdf::make()
    ->page(
        Page::a4()->withContent(
            Column::make(
                Style::make()->withBackground($white)->withPadding(48.0)->withGap(16.0)->withWidth(Length::percent(100.0)),
                [
                    Heading::make('Simple Test', 1, Style::make()->withColor($brand)->withFontSize(32.0)),
                    Text::make('This is a minimal styled PDF generated with Folio.', Style::make()->withColor($text)->withFontSize(11.0)->withLineHeight(1.5)),
                    Column::make(
                        Style::make()->withBackground($surface)->withPadding(24.0)->withWidth(Length::percent(100.0)),
                        [
                            Text::make('Card content with a subtle background.', Style::make()->withColor($text)->withFontSize(10.0)),
                        ]
                    ),
                ]
            )
        )
    )
    ->save(__DIR__ . '/simple-test.pdf');

echo "Generated simple-test.pdf\n";
