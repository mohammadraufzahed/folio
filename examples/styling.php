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
use Folio\Pdf\Styling\Style;

Pdf::make()
    ->page(
        Page::a4()->withContent(
            Column::make()
                ->addChildren([
                    Heading::h1('Styling Demo'),

                    Text::make('This text has custom styling')
                        ->withStyle(
                            Style::make()
                                ->withColor(Color::hex('#ff0000'))
                                ->withFontSize(16.0)
                                ->withFontWeight(FontWeight::Bold)
                        ),

                    Text::make('Centered text with blue color')
                        ->withStyle(
                            Style::make()
                                ->withColor(Color::hex('#0000ff'))
                                ->withAlignment(Alignment::Center)
                                ->withFontSize(14.0)
                        ),

                    Text::make('Text with padding and margin')
                        ->withStyle(
                            Style::make()
                                ->withPadding(20.0)
                                ->withMargin(10.0)
                                ->withColor(Color::hex('#333333'))
                        ),
                ])
        )
    )
    ->save(__DIR__ . '/styling.pdf');

echo 'Styling demo PDF generated: ' . __DIR__ . '/styling.pdf' . "\n";
