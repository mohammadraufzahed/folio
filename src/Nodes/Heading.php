<?php

declare(strict_types=1);

namespace Folio\Pdf\Nodes;

use Folio\Pdf\Styling\FontWeight;
use Folio\Pdf\Styling\Style;

/**
 * Represents a heading node.
 */
final class Heading extends AbstractNode
{
    private readonly string $text;
    private readonly int $level;

    public function __construct(string $text, int $level = 1, ?Style $style = null)
    {
        if ($level < 1 || $level > 6) {
            throw new \InvalidArgumentException('Heading level must be between 1 and 6');
        }

        parent::__construct($style);
        $this->text = $text;
        $this->level = $level;
    }

    public static function make(string $text, int $level = 1, ?Style $style = null): self
    {
        return new self($text, $level, $style);
    }

    public static function h1(string $text, ?Style $style = null): self
    {
        return new self($text, 1, $style ?? Style::make()
            ->withFontSize(32.0)
            ->withFontWeight(FontWeight::Bold));
    }

    public static function h2(string $text, ?Style $style = null): self
    {
        return new self($text, 2, $style ?? Style::make()
            ->withFontSize(24.0)
            ->withFontWeight(FontWeight::Bold));
    }

    public static function h3(string $text, ?Style $style = null): self
    {
        return new self($text, 3, $style ?? Style::make()
            ->withFontSize(18.0)
            ->withFontWeight(FontWeight::SemiBold));
    }

    public static function h4(string $text, ?Style $style = null): self
    {
        return new self($text, 4, $style ?? Style::make()
            ->withFontSize(16.0)
            ->withFontWeight(FontWeight::SemiBold));
    }

    public static function h5(string $text, ?Style $style = null): self
    {
        return new self($text, 5, $style ?? Style::make()
            ->withFontSize(14.0)
            ->withFontWeight(FontWeight::Medium));
    }

    public static function h6(string $text, ?Style $style = null): self
    {
        return new self($text, 6, $style ?? Style::make()
            ->withFontSize(12.0)
            ->withFontWeight(FontWeight::Medium));
    }

    public function text(): string
    {
        return $this->text;
    }

    public function level(): int
    {
        return $this->level;
    }

    public function withText(string $text): self
    {
        return new self($text, $this->level, $this->style);
    }
}
