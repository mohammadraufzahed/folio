<?php

declare(strict_types=1);

namespace Folio\Pdf\Font;

use Folio\Pdf\Ports\FontMetricsPort;
use Folio\Pdf\Ports\TextMetrics;
use Folio\Pdf\Ports\UnicodeRangeSet;

final class Core14FontMetrics implements FontMetricsPort
{
    public static function default(): self
    {
        return new self();
    }

    public function measure(string $text, Font $font, float $size): TextMetrics
    {
        $widths = $this->resolveWidths($font);
        $width = 0.0;

        $length = mb_strlen($text);
        for ($i = 0; $i < $length; $i++) {
            $codepoint = mb_ord(mb_substr($text, $i, 1));
            $width += ($widths[$codepoint] ?? (int) ($this->averageWidth() * 1000)) * ($size / 1000.0);
        }

        $lineHeight = $this->lineHeight($font, $size);

        return new TextMetrics(
            width: $width,
            height: $lineHeight,
            baseline: $size,
            advance: $width,
        );
    }

    public function lineHeight(Font $font, float $size): float
    {
        return $size * 1.2;
    }

    public function supportedCharacters(Font $font): UnicodeRangeSet
    {
        return new UnicodeRangeSet([
            ['start' => 0x20, 'end' => 0x7e],
        ]);
    }

    /**
     * @return array<int, int>
     */
    private function resolveWidths(Font $font): array
    {
        return $this->loadWidths($font->name);
    }

    /**
     * @return array<int, int>
     */
    private function loadWidths(string $name): array
    {
        $lower = strtolower($name);

        if (str_contains($lower, 'courier')) {
            return $this->courierWidths();
        }

        if (str_contains($lower, 'times')) {
            return $this->timesWidths();
        }

        return $this->helveticaWidths();
    }

    /**
     * @return array<int, int>
     */
    private function helveticaWidths(): array
    {
        $widths = [];

        foreach ($this->baseWidths() as $char => $width) {
            $codepoint = mb_ord((string) $char);
            if ($codepoint !== false) {
                $widths[$codepoint] = $width;
            }
        }

        return $widths;
    }

    /**
     * @return array<int, int>
     */
    private function timesWidths(): array
    {
        $widths = $this->helveticaWidths();

        foreach ($this->serifAdjustments() as $char => $adjustment) {
            $codepoint = mb_ord((string) $char);
            if ($codepoint !== false) {
                $widths[$codepoint] = max(100, ($widths[$codepoint] ?? $this->averageWidth() * 1000) + $adjustment);
            }
        }

        return $widths;
    }

    /**
     * @return array<int, int>
     */
    private function courierWidths(): array
    {
        $widths = [];

        for ($i = 32; $i <= 126; $i++) {
            $widths[$i] = 600;
        }

        return $widths;
    }

    /**
     * @return array<int|string, int>
     */
    private function baseWidths(): array
    {
        return [
            ' ' => 278, '!' => 278, '"' => 355, '#' => 556, '$' => 556,
            '%' => 889, '&' => 667, '\'' => 191, '(' => 333, ')' => 333,
            '*' => 389, '+' => 584, ',' => 278, '-' => 333, '.' => 278,
            '/' => 278, '0' => 556, '1' => 556, '2' => 556, '3' => 556,
            '4' => 556, '5' => 556, '6' => 556, '7' => 556, '8' => 556,
            '9' => 556, ':' => 278, ';' => 278, '<' => 584, '=' => 584,
            '>' => 584, '?' => 556, '@' => 1015, 'A' => 667, 'B' => 667,
            'C' => 722, 'D' => 722, 'E' => 667, 'F' => 611, 'G' => 778,
            'H' => 722, 'I' => 278, 'J' => 500, 'K' => 667, 'L' => 611,
            'M' => 833, 'N' => 722, 'O' => 778, 'P' => 667, 'Q' => 778,
            'R' => 722, 'S' => 667, 'T' => 611, 'U' => 722, 'V' => 667,
            'W' => 944, 'X' => 667, 'Y' => 667, 'Z' => 611, '[' => 278,
            '\\' => 278, ']' => 278, '^' => 469, '_' => 556, '`' => 333,
            'a' => 556, 'b' => 556, 'c' => 500, 'd' => 556, 'e' => 556,
            'f' => 278, 'g' => 556, 'h' => 556, 'i' => 222, 'j' => 222,
            'k' => 500, 'l' => 222, 'm' => 833, 'n' => 556, 'o' => 556,
            'p' => 556, 'q' => 556, 'r' => 333, 's' => 500, 't' => 278,
            'u' => 556, 'v' => 500, 'w' => 722, 'x' => 500, 'y' => 500,
            'z' => 444, '{' => 334, '|' => 260, '}' => 334, '~' => 584,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function serifAdjustments(): array
    {
        return [
            'A' => 40, 'B' => 20, 'C' => 50, 'D' => 30, 'E' => -30,
            'F' => -50, 'G' => 60, 'H' => 20, 'I' => -60, 'J' => 20,
            'K' => 30, 'L' => -20, 'M' => 30, 'N' => 20, 'O' => 30,
            'P' => 0, 'Q' => 50, 'R' => 20, 'S' => 20, 'T' => -30,
            'U' => 20, 'V' => 20, 'W' => 60, 'X' => 20, 'Y' => 10,
            'Z' => 30,
            'a' => 40, 'b' => 40, 'c' => 30, 'd' => 40, 'e' => 50,
            'f' => -100, 'g' => 30, 'h' => 30, 'i' => -30, 'j' => -30,
            'k' => 0, 'l' => -100, 'm' => 30, 'n' => 30, 'o' => 40,
            'p' => 40, 'q' => 40, 'r' => 0, 's' => 20, 't' => -80,
            'u' => 30, 'v' => 0, 'w' => 20, 'x' => 0, 'y' => 0,
            'z' => 20,
        ];
    }

    private function averageWidth(): float
    {
        return 0.52;
    }
}
