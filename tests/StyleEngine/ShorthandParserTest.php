<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\StyleEngine;

use Folio\Pdf\StyleEngine\ShorthandParser;
use Folio\Pdf\StyleEngine\TokenSet;
use PHPUnit\Framework\TestCase;

final class ShorthandParserTest extends TestCase
{
    public function testParsesColors(): void
    {
        $tokens = TokenSet::fromArray([
            'colors' => ['primary' => '#1a237e'],
        ]);

        self::assertNotNull(ShorthandParser::color('#fff', $tokens));
        self::assertNotNull(ShorthandParser::color('rgb(255, 0, 0)', $tokens));
        self::assertNotNull(ShorthandParser::color('primary', $tokens));
        self::assertNull(ShorthandParser::color('unknown', $tokens));
    }

    public function testParsesPaddingShorthand(): void
    {
        $tokens = TokenSet::fromArray([]);

        $uniform = ShorthandParser::padding('16', $tokens);
        $two = ShorthandParser::padding('12 24', $tokens);
        $four = ShorthandParser::padding('4 8 12 16', $tokens);

        self::assertSame(16.0, $uniform['top']);
        self::assertSame(12.0, $two['top']);
        self::assertSame(24.0, $two['right']);
        self::assertSame(16.0, $four['left']);
    }

    public function testParsesBorderShorthand(): void
    {
        $tokens = TokenSet::fromArray([]);

        $border = ShorthandParser::border('1 dashed #333', $tokens);

        self::assertNotNull($border);
        self::assertSame(1.0, $border->width());
        self::assertSame('dashed', $border->style()->value);
    }

    public function testParsesShadowShorthand(): void
    {
        $tokens = TokenSet::fromArray([]);

        $shadow = ShorthandParser::shadow('0 2 8 rgba(0,0,0,0.08)', $tokens);

        self::assertNotNull($shadow);
        self::assertSame(2.0, $shadow->offsetY());
        self::assertSame(8.0, $shadow->blurRadius());
    }
}
