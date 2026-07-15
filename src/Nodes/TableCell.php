<?php

declare(strict_types=1);

namespace Folio\Pdf\Nodes;

use Folio\Pdf\Contracts\Node;
use Folio\Pdf\Styling\Style;

final readonly class TableCell implements Node
{
    private readonly ?Style $style;
    private readonly int $rowSpan;
    private readonly int $colSpan;
    private readonly bool $isHeader;
    private readonly ?string $variant;

    public function __construct(
        private readonly Node $content,
        ?Style $style = null,
        int $rowSpan = 1,
        int $colSpan = 1,
        bool $isHeader = false,
        ?string $variant = null
    ) {
        $this->style = $style;
        $this->rowSpan = $rowSpan;
        $this->colSpan = $colSpan;
        $this->isHeader = $isHeader;
        $this->variant = $variant;
    }

    public static function make(Node $content, ?string $variant = null): self
    {
        return new self($content, null, 1, 1, false, $variant);
    }

    public static function header(Node $content, ?Style $style = null, int $rowSpan = 1, int $colSpan = 1): self
    {
        return new self($content, $style, $rowSpan, $colSpan, true);
    }

    public static function withSpan(Node $content, int $rowSpan, int $colSpan, ?Style $style = null): self
    {
        return new self($content, $style, $rowSpan, $colSpan, false);
    }

    public function content(): Node
    {
        return $this->content;
    }

    public function style(): ?Style
    {
        return $this->style;
    }

    public function children(): array
    {
        return [$this->content];
    }

    public function hasChildren(): bool
    {
        return true;
    }

    public function type(): string
    {
        return 'table_cell';
    }

    public function rowSpan(): int
    {
        return $this->rowSpan;
    }

    public function colSpan(): int
    {
        return $this->colSpan;
    }

    public function isHeader(): bool
    {
        return $this->isHeader;
    }

    public function variant(): ?string
    {
        return $this->variant;
    }

    public function withStyle(?Style $style): self
    {
        return new self($this->content, $style, $this->rowSpan, $this->colSpan, $this->isHeader, $this->variant);
    }

    public function withContent(Node $content): self
    {
        return new self($content, $this->style, $this->rowSpan, $this->colSpan, $this->isHeader, $this->variant);
    }
}
