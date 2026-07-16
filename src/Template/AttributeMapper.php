<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

use Folio\Pdf\Styling\Alignment;
use Folio\Pdf\Styling\Color;
use Folio\Pdf\Styling\FontWeight;
use Folio\Pdf\Styling\Length;
use Folio\Pdf\Styling\Style;

final class AttributeMapper
{
    /**
     * @param array<string, mixed> $attrs
     */
    public static function toStyle(array $attrs): ?Style
    {
        $style = Style::make();
        $applied = false;

        if (isset($attrs['color'])) {
            $color = self::parseColor($attrs['color']);
            if ($color !== null) {
                $style = $style->withColor($color);
                $applied = true;
            }
        }

        if (isset($attrs['background'])) {
            $bg = self::parseColor($attrs['background']);
            if ($bg !== null) {
                $style = $style->withBackground($bg);
                $applied = true;
            }
        }

        if (isset($attrs['fontSize'])) {
            $size = self::parseFloat($attrs['fontSize']);
            if ($size !== null) {
                $style = $style->withFontSize($size);
                $applied = true;
            }
        }

        if (isset($attrs['fontWeight'])) {
            $weight = self::parseFontWeight($attrs['fontWeight']);
            if ($weight !== null) {
                $style = $style->withFontWeight($weight);
                $applied = true;
            }
        }

        if (isset($attrs['font'])) {
            $style = $style->withFont((string) $attrs['font']);
            $applied = true;
        }

        if (isset($attrs['padding'])) {
            $val = self::parseFloat($attrs['padding']);
            if ($val !== null) {
                $style = $style->withPadding($val);
                $applied = true;
            }
        }

        if (isset($attrs['margin'])) {
            $val = self::parseFloat($attrs['margin']);
            if ($val !== null) {
                $style = $style->withMargin($val);
                $applied = true;
            }
        }

        if (isset($attrs['gap'])) {
            $val = self::parseFloat($attrs['gap']);
            if ($val !== null) {
                $style = $style->withGap($val);
                $applied = true;
            }
        }

        if (isset($attrs['grow'])) {
            $val = self::parseFloat($attrs['grow']);
            if ($val !== null) {
                $style = $style->withGrow($val);
                $applied = true;
            }
        }

        if (isset($attrs['shrink'])) {
            $val = self::parseFloat($attrs['shrink']);
            if ($val !== null) {
                $style = $style->withShrink($val);
                $applied = true;
            }
        }

        if (isset($attrs['align'])) {
            $align = self::parseAlignment($attrs['align']);
            if ($align !== null) {
                $style = $style->withAlignment($align);
                $applied = true;
            }
        }

        if (isset($attrs['lineHeight'])) {
            $val = self::parseFloat($attrs['lineHeight']);
            if ($val !== null) {
                $style = $style->withLineHeight($val);
                $applied = true;
            }
        }

        if (isset($attrs['letterSpacing'])) {
        }

        if (isset($attrs['opacity'])) {
            $val = self::parseFloat($attrs['opacity']);
            if ($val !== null) {
                $style = $style->withOpacity($val);
                $applied = true;
            }
        }

        if (isset($attrs['width'])) {
            $len = self::parseLength($attrs['width']);
            if ($len !== null) {
                $style = $style->withWidth($len);
                $applied = true;
            }
        }

        if (isset($attrs['height'])) {
            $len = self::parseLength($attrs['height']);
            if ($len !== null) {
                $style = $style->withHeight($len);
                $applied = true;
            }
        }

        if (isset($attrs['radius'])) {
            $val = self::parseFloat($attrs['radius']);
            if ($val !== null) {
                $style = $style->withRadius($val);
                $applied = true;
            }
        }

        return $applied ? $style : null;
    }

    private static function parseColor(mixed $value): ?Color
    {
        if ($value instanceof Color) {
            return $value;
        }

        $str = (string) $value;

        if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $str)) {
            return Color::hex($str);
        }

        return self::namedColor($str);
    }

    private static function parseFloat(mixed $value): ?float
    {
        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }

        $str = (string) $value;
        if (is_numeric($str)) {
            return (float) $str;
        }

        return null;
    }

    private static function parseFontWeight(mixed $value): ?FontWeight
    {
        $str = strtolower((string) $value);

        return match ($str) {
            'normal', '400' => FontWeight::Regular,
            'medium', '500' => FontWeight::Medium,
            'semibold', '600' => FontWeight::SemiBold,
            'bold', '700' => FontWeight::Bold,
            'extrabold', '800' => FontWeight::ExtraBold,
            'light', '300' => FontWeight::Light,
            'thin', '100' => FontWeight::Thin,
            'extralight', '200' => FontWeight::ExtraLight,
            'black', '900' => FontWeight::Black,
            default => null,
        };
    }

    private static function parseAlignment(mixed $value): ?Alignment
    {
        $str = strtolower((string) $value);

        return match ($str) {
            'left' => Alignment::Left,
            'center' => Alignment::Center,
            'right' => Alignment::Right,
            'justify' => Alignment::Justify,
            default => null,
        };
    }

    private static function parseLength(mixed $value): ?Length
    {
        $str = (string) $value;

        if (is_numeric($str)) {
            return Length::pt((float) $str);
        }

        if (preg_match('/^([\d.]+)\s*(pt|px|cm|mm|%)$/', $str, $m)) {
            $val = (float) $m[1];
            return match ($m[2]) {
                'pt' => Length::pt($val),
                'px' => Length::px($val),
                'cm' => Length::cm($val),
                'mm' => Length::mm($val),
                '%' => Length::percent($val),

            };
        }

        return null;
    }

    private static function namedColor(string $name): ?Color
    {
        $colors = [
            'black' => [0, 0, 0],
            'white' => [255, 255, 255],
            'red' => [255, 0, 0],
            'green' => [0, 128, 0],
            'blue' => [0, 0, 255],
            'yellow' => [255, 255, 0],
            'cyan' => [0, 255, 255],
            'magenta' => [255, 0, 255],
            'gray' => [128, 128, 128],
            'grey' => [128, 128, 128],
            'orange' => [255, 165, 0],
            'purple' => [128, 0, 128],
            'pink' => [255, 192, 203],
            'brown' => [165, 42, 42],
            'navy' => [0, 0, 128],
            'teal' => [0, 128, 128],
            'lime' => [0, 255, 0],
            'silver' => [192, 192, 192],
            'maroon' => [128, 0, 0],
            'olive' => [128, 128, 0],
        ];

        $name = strtolower($name);
        if (isset($colors[$name])) {
            [$r, $g, $b] = $colors[$name];
            return Color::rgb($r, $g, $b);
        }

        return null;
    }
}
