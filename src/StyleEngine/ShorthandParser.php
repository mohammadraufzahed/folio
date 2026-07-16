<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

use Folio\Pdf\Styling\Border;
use Folio\Pdf\Styling\BorderStyle;
use Folio\Pdf\Styling\Color;
use Folio\Pdf\Styling\Length;
use Folio\Pdf\Styling\Shadow;

final class ShorthandParser
{
    private static array $namedColors = [
        'black' => '#000000',
        'white' => '#ffffff',
        'red' => '#ff0000',
        'green' => '#008000',
        'blue' => '#0000ff',
        'yellow' => '#ffff00',
        'cyan' => '#00ffff',
        'magenta' => '#ff00ff',
        'silver' => '#c0c0c0',
        'gray' => '#808080',
        'grey' => '#808080',
        'orange' => '#ffa500',
        'purple' => '#800080',
    ];

    public static function color(string $value, ?TokenSet $tokens = null): ?Color
    {
        $value = trim($value);

        if ($tokens !== null && $tokens->has('colors', $value)) {
            return $tokens->color($value);
        }

        if (isset(self::$namedColors[$value])) {
            return Color::hex(self::$namedColors[$value]);
        }

        if (str_starts_with($value, '#')) {
            try {
                return Color::hex($value);
            } catch (\InvalidArgumentException) {
                return null;
            }
        }

        if (preg_match('/^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/i', $value, $matches)) {
            return Color::rgb((float) $matches[1], (float) $matches[2], (float) $matches[3]);
        }

        if (preg_match('/^rgba\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3}),\s*([\d.]+)\)$/i', $value, $matches)) {
            return Color::rgba(
                (float) $matches[1],
                (float) $matches[2],
                (float) $matches[3],
                (float) $matches[4],
            );
        }

        return null;
    }

    public static function length(string $value, ?TokenSet $tokens = null, string $category = 'space'): ?Length
    {
        $value = trim($value);

        if ($tokens !== null && $tokens->has($category, $value)) {
            return $tokens->length($category, $value);
        }

        return TokenSet::parseLengthString($value);
    }

    /**
     * @return array{top: ?float, right: ?float, bottom: ?float, left: ?float}
     */
    public static function padding(string $value, ?TokenSet $tokens = null): array
    {
        $values = self::split($value);

        $resolved = array_map(
            static fn (string $v): ?float => self::length($v, $tokens, 'space')?->toPixels(),
            $values,
        );

        return match (count($resolved)) {
            1 => ['top' => $resolved[0], 'right' => $resolved[0], 'bottom' => $resolved[0], 'left' => $resolved[0]],
            2 => ['top' => $resolved[0], 'right' => $resolved[1], 'bottom' => $resolved[0], 'left' => $resolved[1]],
            3 => ['top' => $resolved[0], 'right' => $resolved[1], 'bottom' => $resolved[2], 'left' => $resolved[1]],
            4 => ['top' => $resolved[0], 'right' => $resolved[1], 'bottom' => $resolved[2], 'left' => $resolved[3]],
            default => ['top' => null, 'right' => null, 'bottom' => null, 'left' => null],
        };
    }

    public static function border(string $value, ?TokenSet $tokens = null): ?Border
    {
        $parts = self::split($value);

        if (count($parts) < 2) {
            return null;
        }

        $widthValue = $parts[0];
        $styleValue = $parts[1];
        $colorValue = $parts[2] ?? 'black';

        $width = self::length($widthValue, $tokens, 'space')?->toPixels() ?? 1.0;

        $style = match (strtolower($styleValue)) {
            'solid' => BorderStyle::Solid,
            'dashed' => BorderStyle::Dashed,
            'dotted' => BorderStyle::Dotted,
            'double' => BorderStyle::Double,
            'none' => BorderStyle::None,
            default => BorderStyle::Solid,
        };

        $color = self::color($colorValue, $tokens) ?? Color::black();

        return Border::make($width, $color, $style);
    }

    public static function shadow(string $value, ?TokenSet $tokens = null): ?Shadow
    {
        $parts = self::split($value);

        if (count($parts) < 3) {
            return null;
        }

        $offsetX = self::length($parts[0], $tokens, 'space')?->toPixels() ?? 0.0;
        $offsetY = self::length($parts[1], $tokens, 'space')?->toPixels() ?? 0.0;
        $blur = self::length($parts[2], $tokens, 'space')?->toPixels() ?? 0.0;
        $spread = 0.0;
        $colorValue = 'black';

        if (count($parts) === 5) {
            $spread = self::length($parts[3], $tokens, 'space')?->toPixels() ?? 0.0;
            $colorValue = $parts[4];
        } elseif (count($parts) === 4) {
            $colorValue = $parts[3];
        }

        $color = self::color($colorValue, $tokens) ?? Color::black();

        return Shadow::make($offsetX, $offsetY, $blur, $color, $spread);
    }

    /**
     * @return array<int, string>
     */
    private static function split(string $value): array
    {
        return preg_split('/\s+/', trim($value), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }
}
