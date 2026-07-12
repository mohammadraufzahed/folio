<?php

declare(strict_types=1);

namespace Folio\Pdf\Nodes;

use Folio\Pdf\Contracts\Node;
use Folio\Pdf\Styling\Style;

/**
 * Table row node.
 */
final readonly class TableRow implements Node
{
    private readonly array $cells;
    private readonly ?Style $style;
    private readonly bool $isHeader;
    private readonly bool $isFooter;

    public function __construct(array $cells, ?Style $style = null, bool $isHeader = false, bool $isFooter = false)
    {
        $this->cells = array_values(array_filter($cells, fn($cell) => $cell instanceof TableCell));
        $this->style = $style;
        $this->isHeader = $isHeader;
        $this->isFooter = $isFooter;
    }

    public static function make(array $cells): self
    {
        return new self($cells);
    }

    public static function header(array $cells, ?Style $style = null): self
    {
        return new self($cells, $style, true);
    }

    public static function footer(array $cells, ?Style $style = null): self
    {
        return new self($cells, $style, false, true);
    }

    public function cells(): array
    {
        return $this->cells;
    }

    public function style(): ?Style
    {
        return $this->style;
    }

    public function children(): array
    {
        return $this->cells;
    }

    public function hasChildren(): bool
    {
        return !empty($this->cells);
    }

    public function type(): string
    {
        return 'table_row';
    }

    public function isHeader(): bool
    {
        return $this->isHeader;
    }

    public function isFooter(): bool
    {
        return $this->isFooter;
    }

    public function withStyle(?Style $style): self
    {
        return new self($this->cells, $style, $this->isHeader, $this->isFooter);
    }

    public function addCell(TableCell $cell): self
    {
        return new self([...$this->cells, $cell], $this->style, $this->isHeader, $this->isFooter);
    }
}
