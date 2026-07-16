<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\Layout;

use Folio\Pdf\Font\Core14FontMetrics;
use Folio\Pdf\Font\Font;
use Folio\Pdf\Layout\TextWrapper;
use PHPUnit\Framework\TestCase;

final class TextWrapperTest extends TestCase
{
    public function testWrapsLongTextIntoMultipleLines(): void
    {
        $wrapper = new TextWrapper(Core14FontMetrics::default());
        $font = Font::make('Helvetica', size: 12.0);

        $result = $wrapper->wrap(
            'This is a long sentence that should be wrapped into several lines when the available width is small.',
            $font,
            12.0,
            100.0,
        );

        self::assertGreaterThan(1, count($result->lines));
        self::assertLessThanOrEqual(100.0, $result->width);
        self::assertGreaterThan(0.0, $result->height);
    }

    public function testSingleShortTextDoesNotWrap(): void
    {
        $wrapper = new TextWrapper(Core14FontMetrics::default());
        $font = Font::make('Helvetica', size: 12.0);

        $result = $wrapper->wrap('Hello', $font, 12.0, 500.0);

        self::assertSame(['Hello'], $result->lines);
        self::assertSame(1, count($result->lines));
    }

    public function testEmptyTextReturnsNoLines(): void
    {
        $wrapper = new TextWrapper(Core14FontMetrics::default());
        $font = Font::make('Helvetica', size: 12.0);

        $result = $wrapper->wrap('', $font, 12.0, 100.0);

        self::assertSame([], $result->lines);
        self::assertSame(0.0, $result->width);
        self::assertSame(0.0, $result->height);
    }
}
