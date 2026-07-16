<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\StyleEngine;

use Folio\Pdf\StyleEngine\TokenSet;
use Folio\Pdf\StyleEngine\Utility;
use PHPUnit\Framework\TestCase;

final class UtilityTest extends TestCase
{
    public function testResolvesPaddingUtilities(): void
    {
        $tokens = TokenSet::fromArray([
            'space' => ['md' => 16],
        ]);

        $props = Utility::resolve(['p-md', 'px-8', 'py-12'], $tokens);

        self::assertSame(16.0, $props['padding']);
        self::assertSame(8.0, $props['paddingLeft']);
        self::assertSame(12.0, $props['paddingTop']);
    }

    public function testResolvesColorAndFontUtilities(): void
    {
        $tokens = TokenSet::fromArray([
            'colors' => ['primary' => '#1a237e'],
            'fontSizes' => ['lg' => 20],
        ]);

        $props = Utility::resolve(['text-primary', 'text-lg', 'font-bold'], $tokens);

        self::assertNotNull($props['color']);
        self::assertSame(20.0, $props['fontSize']);
        self::assertNotNull($props['fontWeight']);
    }

    public function testResolvesLayoutUtilities(): void
    {
        $props = Utility::resolve(['grow', 'shrink-0', 'rounded-4'], null);

        self::assertSame(1.0, $props['grow']);
        self::assertSame(0.0, $props['shrink']);
        self::assertSame(4.0, $props['radius']);
    }
}
