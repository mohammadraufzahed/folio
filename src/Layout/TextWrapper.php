<?php

declare(strict_types=1);

namespace Folio\Pdf\Layout;

use Folio\Pdf\Font\Font;
use Folio\Pdf\Ports\FontMetricsPort;

final readonly class TextWrapper
{
    public function __construct(private FontMetricsPort $fontMetrics)
    {
    }

    public function wrap(
        string $text,
        Font $font,
        float $size,
        float $maxWidth,
        float $lineHeightMultiplier = 1.0,
    ): TextWrapResult {
        $maxWidth = max(1.0, $maxWidth);
        $text = trim($text);

        if ($text === '') {
            return new TextWrapResult([], 0.0, 0.0);
        }

        $spaceWidth = $this->fontMetrics->measure(' ', $font, $size)->width;
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $lines = [];
        $currentLine = '';
        $currentWidth = 0.0;
        $maxLineWidth = 0.0;

        foreach ($words as $word) {
            $wordWidth = $this->fontMetrics->measure($word, $font, $size)->width;

            if ($wordWidth > $maxWidth) {
                if ($currentLine !== '') {
                    $lines[] = $currentLine;
                    $maxLineWidth = max($maxLineWidth, $currentWidth);
                    $currentLine = '';
                    $currentWidth = 0.0;
                }

                $currentLine = $this->breakLongWord($word, $font, $size, $maxWidth, $lines, $maxLineWidth);
                $currentWidth = $this->fontMetrics->measure($currentLine, $font, $size)->width;
                continue;
            }

            if ($currentLine === '') {
                $currentLine = $word;
                $currentWidth = $wordWidth;
                continue;
            }

            $candidateWidth = $currentWidth + $spaceWidth + $wordWidth;

            if ($candidateWidth <= $maxWidth) {
                $currentLine .= ' ' . $word;
                $currentWidth = $candidateWidth;
                continue;
            }

            $lines[] = $currentLine;
            $maxLineWidth = max($maxLineWidth, $currentWidth);
            $currentLine = $word;
            $currentWidth = $wordWidth;
        }

        if ($currentLine !== '') {
            $lines[] = $currentLine;
            $maxLineWidth = max($maxLineWidth, $currentWidth);
        }

        $lineCount = max(1, count($lines));
        $lineHeight = $this->fontMetrics->lineHeight($font, $size) * max(0.1, $lineHeightMultiplier);

        return new TextWrapResult(
            $lines,
            $maxLineWidth,
            $lineCount * $lineHeight,
        );
    }

    /**
     * @return array{0: TextWrapResult, 1: TextWrapResult}
     */
    public function split(
        string $text,
        Font $font,
        float $size,
        float $maxWidth,
        float $maxHeight,
        float $lineHeightMultiplier = 1.0,
    ): array {
        $wrapped = $this->wrap($text, $font, $size, $maxWidth, $lineHeightMultiplier);

        if ($wrapped->height <= $maxHeight || $wrapped->lines === []) {
            return [$wrapped, new TextWrapResult([], 0.0, 0.0)];
        }

        $lineCount = count($wrapped->lines);
        $lineHeight = $wrapped->height / $lineCount;
        $fitCount = max(1, (int) floor($maxHeight / $lineHeight));

        if ($fitCount >= $lineCount) {
            return [$wrapped, new TextWrapResult([], 0.0, 0.0)];
        }

        $firstLines = array_slice($wrapped->lines, 0, $fitCount);
        $restLines = array_slice($wrapped->lines, $fitCount);

        return [
            $this->resultForLines($firstLines, $font, $size, $lineHeight),
            $this->resultForLines($restLines, $font, $size, $lineHeight),
        ];
    }

    /**
     * @param array<int, string> $lines
     */
    private function resultForLines(array $lines, Font $font, float $size, float $lineHeight): TextWrapResult
    {
        $maxLineWidth = 0.0;

        foreach ($lines as $line) {
            $maxLineWidth = max($maxLineWidth, $this->fontMetrics->measure($line, $font, $size)->width);
        }

        return new TextWrapResult(
            $lines,
            $maxLineWidth,
            count($lines) * $lineHeight,
        );
    }

    /**
     * @param array<int, string> $lines
     */
    private function breakLongWord(
        string $word,
        Font $font,
        float $size,
        float $maxWidth,
        array &$lines,
        float &$maxLineWidth,
    ): string {
        $chars = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $currentLine = '';
        $currentWidth = 0.0;

        foreach ($chars as $char) {
            $charWidth = $this->fontMetrics->measure($char, $font, $size)->width;

            if ($currentWidth + $charWidth > $maxWidth && $currentLine !== '') {
                $lines[] = $currentLine;
                $maxLineWidth = max($maxLineWidth, $currentWidth);
                $currentLine = '';
                $currentWidth = 0.0;
            }

            $currentLine .= $char;
            $currentWidth += $charWidth;
        }

        return $currentLine;
    }
}
