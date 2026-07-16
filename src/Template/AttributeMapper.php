<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

use Folio\Pdf\StyleEngine\ShorthandParser;
use Folio\Pdf\StyleEngine\TokenSet;
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
    public static function toStyle(array $attrs, ?TokenSet $tokens = null): ?Style
    {
        $style = Style::make();
        $applied = false;

        if (isset($attrs['class'])) {
            $class = is_array($attrs['class']) ? $attrs['class'] : (string) $attrs['class'];
            $style = $style->withClass($class);
            $applied = true;
        }

        if (isset($attrs['color'])) {
            $color = self::parseColor($attrs['color'], $tokens);
            if ($color !== null) {
                $style = $style->withColor($color);
                $applied = true;
            }
        }

        if (isset($attrs['background'])) {
            $bg = self::parseColor($attrs['background'], $tokens);
            if ($bg !== null) {
                $style = $style->withBackground($bg);
                $applied = true;
            }
        }

        if (isset($attrs['fontSize'])) {
            $size = self::parseFontSize($attrs['fontSize'], $tokens);
            if ($size !== null) {
                $style = $style->withFontSize($size);
                $applied = true;
            }
        }

        if (isset($attrs['fontWeight'])) {
            $weight = self::parseFontWeight($attrs['fontWeight'], $tokens);
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
            $val = self::parseSpace($attrs['padding'], $tokens);
            if ($val !== null) {
                $style = $style->withPadding($val);
                $applied = true;
            }
        }

        if (isset($attrs['margin'])) {
            $val = self::parseSpace($attrs['margin'], $tokens);
            if ($val !== null) {
                $style = $style->withMargin($val);
                $applied = true;
            }
        }

        if (isset($attrs['gap'])) {
            $val = self::parseSpace($attrs['gap'], $tokens);
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
            $val = self::parseSpace($attrs['lineHeight'], $tokens);
            if ($val !== null) {
                $style = $style->withLineHeight($val);
                $applied = true;
            }
        }

        if (isset($attrs['opacity'])) {
            $val = self::parseFloat($attrs['opacity']);
            if ($val !== null) {
                $style = $style->withOpacity($val);
                $applied = true;
            }
        }

        if (isset($attrs['width'])) {
            $len = self::parseLength($attrs['width'], $tokens);
            if ($len !== null) {
                $style = $style->withWidth($len);
                $applied = true;
            }
        }

        if (isset($attrs['height'])) {
            $len = self::parseLength($attrs['height'], $tokens);
            if ($len !== null) {
                $style = $style->withHeight($len);
                $applied = true;
            }
        }

        if (isset($attrs['radius'])) {
            $val = self::parseSpace($attrs['radius'], $tokens, 'radii');
            if ($val !== null) {
                $style = $style->withRadius($val);
                $applied = true;
            }
        }

        if (isset($attrs['border'])) {
            $border = ShorthandParser::border((string) $attrs['border'], $tokens);
            if ($border !== null) {
                $style = $style->withBorder($border);
                $applied = true;
            }
        }

        if (isset($attrs['shadow'])) {
            $shadow = ShorthandParser::shadow((string) $attrs['shadow'], $tokens);
            if ($shadow !== null) {
                $style = $style->withShadow($shadow);
                $applied = true;
            }
        }

        return $applied ? $style : null;
    }

    private static function parseColor(mixed $value, ?TokenSet $tokens): ?Color
    {
        if ($value instanceof Color) {
            return $value;
        }

        $str = (string) $value;

        return ShorthandParser::color($str, $tokens);
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

    private static function parseLength(mixed $value, ?TokenSet $tokens): ?Length
    {
        if ($value instanceof Length) {
            return $value;
        }

        $str = (string) $value;

        if (is_numeric($str)) {
            return Length::pt((float) $str);
        }

        return ShorthandParser::length($str, $tokens);
    }

    private static function parseSpace(mixed $value, ?TokenSet $tokens, string $category = 'space'): ?float
    {
        $length = self::parseLength($value, $tokens);

        if ($length !== null) {
            return $length->toPixels();
        }

        if ($tokens !== null && is_string($value)) {
            $fontSize = $tokens->fontSize($value);

            if ($fontSize !== null && $category === 'fontSizes') {
                return $fontSize;
            }
        }

        return null;
    }

    private static function parseFontSize(mixed $value, ?TokenSet $tokens): ?float
    {
        if ($tokens !== null && is_string($value)) {
            $fontSize = $tokens->fontSize($value);

            if ($fontSize !== null) {
                return $fontSize;
            }
        }

        return self::parseSpace($value, $tokens, 'fontSizes');
    }

    private static function parseFontWeight(mixed $value, ?TokenSet $tokens): ?FontWeight
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
}
