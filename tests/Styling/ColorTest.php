<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\Styling;

use Folio\Pdf\Styling\Color;
use PHPUnit\Framework\TestCase;

final class ColorTest extends TestCase
{
    public function testRgb(): void
    {
        $color = Color::rgb(255, 0, 0);
        $this->assertEquals(1.0, $color->red());
        $this->assertEquals(0.0, $color->green());
        $this->assertEquals(0.0, $color->blue());
        $this->assertEquals(1.0, $color->alpha());
    }

    public function testHex(): void
    {
        $color = Color::hex('#ff0000');
        $this->assertEquals(1.0, $color->red());
        $this->assertEquals(0.0, $color->green());
        $this->assertEquals(0.0, $color->blue());
    }

    public function testBlack(): void
    {
        $color = Color::black();
        $this->assertEquals(0.0, $color->red());
        $this->assertEquals(0.0, $color->green());
        $this->assertEquals(0.0, $color->blue());
    }

    public function testWhite(): void
    {
        $color = Color::white();
        $this->assertEquals(1.0, $color->red());
        $this->assertEquals(1.0, $color->green());
        $this->assertEquals(1.0, $color->blue());
    }

    public function testGray(): void
    {
        $color = Color::gray(0.5);
        $this->assertEquals(0.5, $color->red());
        $this->assertEquals(0.5, $color->green());
        $this->assertEquals(0.5, $color->blue());
    }

    public function testToHex(): void
    {
        $color = Color::rgb(255, 128, 0);
        $this->assertEquals('#ff8000', $color->toHex());
    }

    public function testClamping(): void
    {
        $color = Color::rgb(300, -50, 0);
        $this->assertEquals(1.0, $color->red());
        $this->assertEquals(0.0, $color->green());
        $this->assertEquals(0.0, $color->blue());
    }
}
