<?php

declare(strict_types=1);

namespace Folio\Pdf\Nodes;

use Folio\Pdf\Styling\Style;

final readonly class InlineSpan
{
    public function __construct(
        private string $text,
        private ?Style $style = null,
    ) {
    }

    public static function make(string $text, ?Style $style = null): self
    {
        return new self($text, $style);
    }

    public function text(): string
    {
        return $this->text;
    }

    public function style(): ?Style
    {
        return $this->style;
    }

    public function withText(string $text): self
    {
        return new self($text, $this->style);
    }

    public function withStyle(?Style $style): self
    {
        return new self($this->text, $style);
    }
}
