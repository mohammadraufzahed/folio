<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\Font;

use Folio\Pdf\Font\Core14FontMetrics;
use Folio\Pdf\Font\Font;
use PHPUnit\Framework\TestCase;

final class Core14FontMetricsTest extends TestCase
{
    public function testMeasuresTextWidth(): void
    {
        $metrics = Core14FontMetrics::default();
        $font = Font::make('Helvetica', size: 12.0);

        $result = $metrics->measure('Hello', $font, 12.0);

        self::assertGreaterThan(0.0, $result->width);
        self::assertSame($result->width, $result->advance);
        self::assertGreaterThan(0.0, $result->height);
        self::assertGreaterThan(0.0, $result->baseline);
    }

    public function testCourierIsMonospaced(): void
    {
        $metrics = Core14FontMetrics::default();
        $font = Font::make('Courier', size: 12.0);

        $a = $metrics->measure('A', $font, 12.0)->width;
        $i = $metrics->measure('i', $font, 12.0)->width;

        self::assertSame($a, $i);
    }

    public function testHelveticaWidthsVary(): void
    {
        $metrics = Core14FontMetrics::default();
        $font = Font::make('Helvetica', size: 12.0);

        $i = $metrics->measure('i', $font, 12.0)->width;
        $m = $metrics->measure('m', $font, 12.0)->width;

        self::assertLessThan($m, $i);
    }

    public function testLineHeight(): void
    {
        $metrics = Core14FontMetrics::default();
        $font = Font::make('Helvetica', size: 12.0);

        self::assertEqualsWithDelta(14.4, $metrics->lineHeight($font, 12.0), 0.0001);
    }

    public function testSupportedCharacters(): void
    {
        $metrics = Core14FontMetrics::default();
        $font = Font::make('Helvetica', size: 12.0);

        $range = $metrics->supportedCharacters($font);

        self::assertTrue($range->contains(0x41));
        self::assertFalse($range->contains(0x1f600));
    }
}
