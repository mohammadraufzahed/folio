<?php

declare(strict_types=1);

namespace Folio\Pdf\Nodes;

use Folio\Pdf\Styling\Style;

final class Text extends AbstractNode
{
    private readonly string $text;

    public function __construct(string $text, ?Style $style = null)
    {
        parent::__construct($style);
        $this->text = $text;
    }

    public static function make(string $text, ?Style $style = null): self
    {
        return new self($text, $style);
    }

    public function text(): string
    {
        return $this->text;
    }

    public function withText(string $text): self
    {
        return new self($text, $this->style);
    }

    protected function copy(?Style $style, array $children): static
    {
        return new self($this->text, $style);
    }
}
