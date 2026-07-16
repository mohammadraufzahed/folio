<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

use Folio\Pdf\Styling\Color;
use Folio\Pdf\Styling\FontWeight;
use Folio\Pdf\Styling\Length;

final class Utility
{
    /**
     * @param array<int, string> $classList
     *
     * @return array<string, mixed>
     */
    public static function resolve(array $classList, ?TokenSet $tokens = null): array
    {
        $resolved = [];

        foreach ($classList as $class) {
            $properties = self::parseClass(trim($class), $tokens);

            foreach ($properties as $key => $value) {
                $resolved[$key] = $value;
            }
        }

        return $resolved;
    }

    /**
     * @return array<string, mixed>
     */
    private static function parseClass(string $class, ?TokenSet $tokens): array
    {
        if ($class === '') {
            return [];
        }

        if (preg_match('/^p-(.+)$/', $class, $matches)) {
            return ['padding' => self::sizeValue($matches[1], $tokens)];
        }

        if (preg_match('/^px-(.+)$/', $class, $matches)) {
            $value = self::sizeValue($matches[1], $tokens);

            return ['paddingLeft' => $value, 'paddingRight' => $value];
        }

        if (preg_match('/^py-(.+)$/', $class, $matches)) {
            $value = self::sizeValue($matches[1], $tokens);

            return ['paddingTop' => $value, 'paddingBottom' => $value];
        }

        if (preg_match('/^m-(.+)$/', $class, $matches)) {
            return ['margin' => self::sizeValue($matches[1], $tokens)];
        }

        if (preg_match('/^mx-(.+)$/', $class, $matches)) {
            $value = self::sizeValue($matches[1], $tokens);

            return ['marginLeft' => $value, 'marginRight' => $value];
        }

        if (preg_match('/^my-(.+)$/', $class, $matches)) {
            $value = self::sizeValue($matches[1], $tokens);

            return ['marginTop' => $value, 'marginBottom' => $value];
        }

        if (preg_match('/^w-(.+)$/', $class, $matches)) {
            return ['width' => self::lengthValue($matches[1], $tokens)];
        }

        if (preg_match('/^h-(.+)$/', $class, $matches)) {
            return ['height' => self::lengthValue($matches[1], $tokens)];
        }

        if (preg_match('/^rounded-(.+)$/', $class, $matches)) {
            return ['radius' => self::sizeValue($matches[1], $tokens, 'radii')];
        }

        if (preg_match('/^bg-(.+)$/', $class, $matches)) {
            return ['background' => self::colorValue($matches[1], $tokens)];
        }

        if (preg_match('/^text-(.+)$/', $class, $matches)) {
            $value = $matches[1];

            if ($tokens !== null && $tokens->has('colors', $value)) {
                return ['color' => $tokens->color($value)];
            }

            if ($tokens !== null && $tokens->has('fontSizes', $value)) {
                return ['fontSize' => $tokens->fontSize($value)];
            }

            $color = ShorthandParser::color($value, $tokens);
            if ($color !== null) {
                return ['color' => $color];
            }

            $length = self::sizeValue($value, $tokens, 'fontSizes');
            if ($length !== null) {
                return ['fontSize' => $length];
            }
        }

        if (preg_match('/^font-(.+)$/', $class, $matches)) {
            $value = $matches[1];

            if ($tokens !== null && $tokens->has('fonts', $value)) {
                $family = $tokens->get('fonts', $value);

                if (is_string($family)) {
                    return ['font' => $family];
                }
            }

            $weight = self::fontWeight($value);
            if ($weight !== null) {
                return ['fontWeight' => $weight];
            }

            return ['font' => $value];
        }

        if (preg_match('/^font-weight-(.+)$/', $class, $matches)) {
            $weight = self::fontWeight($matches[1]);

            return $weight !== null ? ['fontWeight' => $weight] : [];
        }

        if (preg_match('/^shadow-(.+)$/', $class, $matches)) {
            $value = $matches[1];

            if ($tokens !== null && $tokens->has('shadows', $value)) {
                $shadowValue = $tokens->get('shadows', $value);

                if (is_string($shadowValue)) {
                    return ['shadow' => ShorthandParser::shadow($shadowValue, $tokens)];
                }
            }

            return [];
        }

        return match ($class) {
            'grow' => ['grow' => 1.0],
            'grow-0' => ['grow' => 0.0],
            'shrink' => ['shrink' => 1.0],
            'shrink-0' => ['shrink' => 0.0],
            'uppercase' => ['textTransform' => 'uppercase'],
            'lowercase' => ['textTransform' => 'lowercase'],
            'capitalize' => ['textTransform' => 'capitalize'],
            'underline' => ['textDecoration' => 'underline'],
            'line-through' => ['textDecoration' => 'line-through'],
            default => [],
        };
    }

    private static function sizeValue(string $value, ?TokenSet $tokens = null, string $category = 'space'): ?float
    {
        $length = self::lengthValue($value, $tokens, $category);

        return $length?->toPixels();
    }

    private static function lengthValue(string $value, ?TokenSet $tokens = null, string $category = 'space'): ?Length
    {
        if ($tokens !== null && $tokens->has($category, $value)) {
            return $tokens->length($category, $value);
        }

        return ShorthandParser::length($value, $tokens, $category);
    }

    private static function colorValue(string $value, ?TokenSet $tokens = null): ?Color
    {
        if ($tokens !== null && $tokens->has('colors', $value)) {
            return $tokens->color($value);
        }

        return ShorthandParser::color($value, $tokens);
    }

    private static function fontWeight(string $value): ?FontWeight
    {
        return match ($value) {
            'thin' => FontWeight::Thin,
            'extralight' => FontWeight::ExtraLight,
            'light' => FontWeight::Light,
            'normal' => FontWeight::Regular,
            'medium' => FontWeight::Medium,
            'semibold' => FontWeight::SemiBold,
            'bold' => FontWeight::Bold,
            'extrabold' => FontWeight::ExtraBold,
            'black' => FontWeight::Black,
            default => null,
        };
    }
}
