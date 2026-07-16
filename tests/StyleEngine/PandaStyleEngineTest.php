<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\StyleEngine;

use Folio\Pdf\Nodes\Text;
use Folio\Pdf\StyleEngine\PandaStyleEngine;
use Folio\Pdf\StyleEngine\StyleContext;
use Folio\Pdf\Styling\Color;
use Folio\Pdf\Styling\FontWeight;
use Folio\Pdf\Styling\Style;
use PHPUnit\Framework\TestCase;

final class PandaStyleEngineTest extends TestCase
{
    public function testResolvesStyleFromNode(): void
    {
        $style = Style::make()
            ->withColor(Color::hex('#1a237e'))
            ->withFontSize(18.0)
            ->withFontWeight(FontWeight::Bold)
            ->withPadding(12.0);

        $node = Text::make('Hello', $style);
        $engine = new PandaStyleEngine();

        $computed = $engine->resolve($node, StyleContext::root());

        self::assertSame($style->color(), $computed->text->color);
        self::assertSame($style->fontSize(), $computed->text->fontSize);
        self::assertSame($style->fontWeight(), $computed->text->fontWeight);
        self::assertSame($style->padding(), $computed->box->padding);
    }

    public function testFallsBackToDefaultsForNodesWithoutStyle(): void
    {
        $node = Text::make('Hello');
        $engine = new PandaStyleEngine();

        $computed = $engine->resolve($node, StyleContext::root());

        self::assertNull($computed->text->color);
        self::assertNull($computed->text->fontSize);
        self::assertNull($computed->box->padding);
    }
}
