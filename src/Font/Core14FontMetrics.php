<?php

declare(strict_types=1);

namespace Folio\Pdf\Font;

use Folio\Pdf\Ports\FontMetricsPort;
use Folio\Pdf\Ports\TextMetrics;
use Folio\Pdf\Ports\UnicodeRangeSet;

final class Core14FontMetrics implements FontMetricsPort
{
    /** @var array<string, int>|null */
    private static ?array $baseWidths = null;

    /** @var array<string, int>|null */
    private static ?array $serifAdjustments = null;

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

        $isBold = str_contains($lower, 'bold');

        if (str_contains($lower, 'times')) {
            $widths = $this->timesWidths();

            return $isBold ? $this->scaleWidths($widths, 1.03) : $widths;
        }

        $widths = $this->helveticaWidths();

        return $isBold ? $this->scaleWidths($widths, 1.06) : $widths;
    }

    /**
     * @param array<int, int> $widths
     * @return array<int, int>
     */
    private function scaleWidths(array $widths, float $factor): array
    {
        $scaled = [];

        foreach ($widths as $codepoint => $width) {
            $scaled[$codepoint] = (int) ($width * $factor);
        }

        return $scaled;
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
     * @return array<string, int>
     */
    private function baseWidths(): array
    {
        return self::$baseWidths ??= $this->loadMetric('base');
    }

    /**
     * @return array<string, int>
     */
    private function serifAdjustments(): array
    {
        return self::$serifAdjustments ??= $this->loadMetric('serifAdjustments');
    }

    /**
     * @return array<string, int>
     */
    private function loadMetric(string $key): array
    {
        $path = dirname(__DIR__, 2) . '/data/core14-fonts.json';

        if (!is_file($path)) {
            return [];
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return [];
        }

        $decoded = json_decode($content, true);
        if (!is_array($decoded) || !isset($decoded[$key]) || !is_array($decoded[$key])) {
            return [];
        }

        return $decoded[$key];
    }

    private function averageWidth(): float
    {
        return 0.52;
    }
}
