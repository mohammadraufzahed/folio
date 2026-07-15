<?php

declare(strict_types=1);

namespace Folio\Pdf\Nodes;

use Folio\Pdf\Contracts\Node;
use Folio\Pdf\Styling\Style;

final class Page extends AbstractNode
{
    private readonly float $width;
    private readonly float $height;
    private readonly ?Node $content;

    public function __construct(
        float $width = 595.0,
        float $height = 842.0,
        ?Style $style = null,
        ?Node $content = null
    ) {
        parent::__construct($style);
        $this->width = $width;
        $this->height = $height;
        $this->content = $content;
    }

    public static function make(
        float $width = 595.0,
        float $height = 842.0,
        ?Style $style = null
    ): self {
        return new self($width, $height, $style);
    }

    public static function a4(?Style $style = null): self
    {
        return new self(595.0, 842.0, $style);
    }

    public static function letter(?Style $style = null): self
    {
        return new self(612.0, 792.0, $style);
    }

    public static function a3(?Style $style = null): self
    {
        return new self(842.0, 1191.0, $style);
    }

    public function width(): float
    {
        return $this->width;
    }

    public function height(): float
    {
        return $this->height;
    }

    public function content(): ?Node
    {
        return $this->content;
    }

    public function withContent(Node $content): self
    {
        return new self($this->width, $this->height, $this->style, $content);
    }

    public function withSize(float $width, float $height): self
    {
        return new self($width, $height, $this->style, $this->content);
    }

    protected function copy(?Style $style, array $children): static
    {
        return new self($this->width, $this->height, $style, $this->content);
    }
}
