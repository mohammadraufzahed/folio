<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\StyleEngine;

use Folio\Pdf\StyleEngine\TokenSet;
use PHPUnit\Framework\TestCase;

final class TokenSetTest extends TestCase
{
    public function testResolvesColorTokens(): void
    {
        $tokens = TokenSet::fromArray([
            'colors' => ['primary' => '#1a237e', 'accent' => '#ff6f00'],
        ]);

        $primary = $tokens->color('primary');
        $missing = $tokens->color('unknown');

        self::assertNotNull($primary);
        self::assertNull($missing);
    }

    public function testResolvesLengthTokens(): void
    {
        $tokens = TokenSet::fromArray([
            'space' => ['md' => 16, 'lg' => '24px'],
            'radii' => ['full' => 9999],
        ]);

        $md = $tokens->length('space', 'md');
        $lg = $tokens->length('space', 'lg');
        $full = $tokens->length('radii', 'full');

        self::assertNotNull($md);
        self::assertNotNull($lg);
        self::assertNotNull($full);
        self::assertSame(16.0, $md->value());
        self::assertSame(24.0, $lg->value());
        self::assertSame(9999.0, $full->value());
    }

    public function testParsesCssLengthStrings(): void
    {
        $pt = TokenSet::parseLengthString('12pt');
        $px = TokenSet::parseLengthString('16px');
        $cm = TokenSet::parseLengthString('2.54cm');
        $percent = TokenSet::parseLengthString('50%');

        self::assertNotNull($pt);
        self::assertNotNull($px);
        self::assertNotNull($cm);
        self::assertNotNull($percent);

        self::assertSame(12.0, $pt->value());
        self::assertSame('pt', $pt->unit()->value);
        self::assertSame('px', $px->unit()->value);
        self::assertSame('cm', $cm->unit()->value);
        self::assertSame('%', $percent->unit()->value);
    }
}
