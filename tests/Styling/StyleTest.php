<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\Styling;

use Folio\Pdf\Styling\Color;
use Folio\Pdf\Styling\FontWeight;
use Folio\Pdf\Styling\Style;
use PHPUnit\Framework\TestCase;

final class StyleTest extends TestCase
{
    public function testMake(): void
    {
        $style = Style::make();
        $this->assertNull($style->padding());
        $this->assertNull($style->margin());
        $this->assertNull($style->color());
    }

    public function testWithPadding(): void
    {
        $style = Style::make()->withPadding(10.0);
        $this->assertEquals(10.0, $style->padding());
        $this->assertEquals(10.0, $style->paddingTop());
        $this->assertEquals(10.0, $style->paddingBottom());
        $this->assertEquals(10.0, $style->paddingLeft());
        $this->assertEquals(10.0, $style->paddingRight());
    }

    public function testWithColor(): void
    {
        $color = Color::hex('#ff0000');
        $style = Style::make()->withColor($color);
        $this->assertSame($color, $style->color());
    }

    public function testWithFontSize(): void
    {
        $style = Style::make()->withFontSize(14.0);
        $this->assertEquals(14.0, $style->fontSize());
    }

    public function testWithFontWeight(): void
    {
        $style = Style::make()->withFontWeight(FontWeight::Bold);
        $this->assertEquals(FontWeight::Bold, $style->fontWeight());
    }

    public function testImmutability(): void
    {
        $style1 = Style::make();
        $style2 = $style1->withPadding(10.0);
        
        $this->assertNotSame($style1, $style2);
        $this->assertNull($style1->padding());
        $this->assertEquals(10.0, $style2->padding());
    }

    public function testChaining(): void
    {
        $style = Style::make()
            ->withPadding(10.0)
            ->withMargin(20.0)
            ->withColor(Color::black())
            ->withFontSize(14.0);
        
        $this->assertEquals(10.0, $style->padding());
        $this->assertEquals(20.0, $style->margin());
        $this->assertEquals(14.0, $style->fontSize());
    }
}
