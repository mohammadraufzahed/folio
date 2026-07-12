<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\Template;

use Folio\Pdf\Styling\Alignment;
use Folio\Pdf\Styling\Color;
use Folio\Pdf\Styling\FontWeight;
use Folio\Pdf\Styling\Length;
use Folio\Pdf\Template\AttributeMapper;
use PHPUnit\Framework\TestCase;

final class AttributeMapperTest extends TestCase
{
    public function testReturnsNullForEmptyAttrs(): void
    {
        $this->assertNull(AttributeMapper::toStyle([]));
    }

    public function testReturnsNullForNonStyleAttrs(): void
    {
        $this->assertNull(AttributeMapper::toStyle(['variant' => 'success', 'colspan' => '2']));
    }

    public function testMapsColor(): void
    {
        $style = AttributeMapper::toStyle(['color' => '#ff0000']);
        $this->assertNotNull($style);
        $this->assertNotNull($style->color());
        $this->assertEquals('#ff0000', $style->color()->toHex());
    }

    public function testMapsNamedColor(): void
    {
        $style = AttributeMapper::toStyle(['color' => 'red']);
        $this->assertNotNull($style);
        $this->assertNotNull($style->color());
    }

    public function testMapsBackground(): void
    {
        $style = AttributeMapper::toStyle(['background' => 'blue']);
        $this->assertNotNull($style);
        $this->assertNotNull($style->background());
    }

    public function testMapsFontSize(): void
    {
        $style = AttributeMapper::toStyle(['fontSize' => '14']);
        $this->assertNotNull($style);
        $this->assertEquals(14.0, $style->fontSize());
    }

    public function testMapsFontWeight(): void
    {
        $style = AttributeMapper::toStyle(['fontWeight' => 'bold']);
        $this->assertNotNull($style);
        $this->assertEquals(FontWeight::Bold, $style->fontWeight());
    }

    public function testMapsAlignment(): void
    {
        $style = AttributeMapper::toStyle(['align' => 'center']);
        $this->assertNotNull($style);
        $this->assertEquals(Alignment::Center, $style->alignment());
    }

    public function testMapsPadding(): void
    {
        $style = AttributeMapper::toStyle(['padding' => '10']);
        $this->assertNotNull($style);
        $this->assertEquals(10.0, $style->padding());
    }

    public function testMapsMargin(): void
    {
        $style = AttributeMapper::toStyle(['margin' => '5']);
        $this->assertNotNull($style);
        $this->assertEquals(5.0, $style->margin());
    }

    public function testMapsWidth(): void
    {
        $style = AttributeMapper::toStyle(['width' => '100']);
        $this->assertNotNull($style);
        $this->assertNotNull($style->width());
        $this->assertEquals(100.0, $style->width()->value());
    }

    public function testMapsWidthWithUnit(): void
    {
        $style = AttributeMapper::toStyle(['width' => '50%']);
        $this->assertNotNull($style);
        $this->assertNotNull($style->width());
        $this->assertEquals(50.0, $style->width()->value());
    }

    public function testMapsMultipleAttrs(): void
    {
        $style = AttributeMapper::toStyle([
            'color' => 'red',
            'fontSize' => '12',
            'align' => 'right',
            'padding' => '8',
        ]);
        $this->assertNotNull($style);
        $this->assertNotNull($style->color());
        $this->assertEquals(12.0, $style->fontSize());
        $this->assertEquals(Alignment::Right, $style->alignment());
        $this->assertEquals(8.0, $style->padding());
    }

    public function testIgnoresUnknownAttrs(): void
    {
        $style = AttributeMapper::toStyle(['color' => 'red', 'variant' => 'success']);
        $this->assertNotNull($style);
        $this->assertNotNull($style->color());
    }
}
