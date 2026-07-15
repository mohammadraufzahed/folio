<?php

declare(strict_types=1);

namespace Folio\Pdf\Nodes;

use Folio\Pdf\Contracts\HasChildren;
use Folio\Pdf\Contracts\Node;
use Folio\Pdf\Styling\Style;

final readonly class Table implements HasChildren
{
    private readonly array $rows;
    private readonly ?Style $style;
    private readonly array $columnWidths;
    private readonly bool $showBorders;
    private readonly bool $showHeaders;

    public function __construct(
        array $rows,
        ?Style $style = null,
        ?array $columnWidths = null,
        bool $showBorders = true,
        bool $showHeaders = true
    ) {
        $this->rows = array_values(array_filter($rows, fn ($row) => $row instanceof TableRow));
        $this->style = $style;
        $this->columnWidths = $columnWidths ?? [];
        $this->showBorders = $showBorders;
        $this->showHeaders = $showHeaders;
    }

    public static function make(array $rows): self
    {
        return new self($rows);
    }

    public static function simple(array $rows): self
    {
        return new self($rows, null, null, true, true);
    }

    public static function noBorders(array $rows): self
    {
        return new self($rows, null, null, false, false);
    }

    public static function withColumnWidths(array $rows, array $columnWidths): self
    {
        return new self($rows, null, $columnWidths, true, true);
    }

    public function rows(): array
    {
        return $this->rows;
    }

    public function style(): ?Style
    {
        return $this->style;
    }

    public function children(): array
    {
        return $this->rows;
    }

    public function hasChildren(): bool
    {
        return !empty($this->rows);
    }

    public function type(): string
    {
        return 'table';
    }

    public function columnWidths(): array
    {
        return $this->columnWidths;
    }

    public function showBorders(): bool
    {
        return $this->showBorders;
    }

    public function showHeaders(): bool
    {
        return $this->showHeaders;
    }

    public function withStyle(?Style $style): self
    {
        return new self($this->rows, $style, $this->columnWidths, $this->showBorders, $this->showHeaders);
    }

    /**
     * @param array<int, Node> $children
     */
    public function withChildren(array $children): self
    {
        return new self($children, $this->style, $this->columnWidths, $this->showBorders, $this->showHeaders);
    }

    public function addRow(TableRow $row): self
    {
        return new self([...$this->rows, $row], $this->style, $this->columnWidths, $this->showBorders, $this->showHeaders);
    }

    public function rowCount(): int
    {
        return count($this->rows);
    }

    public function columnCount(): int
    {
        if (empty($this->rows)) {
            return 0;
        }
        return max(array_map(fn ($row) => count($row->cells()), $this->rows));
    }
}
