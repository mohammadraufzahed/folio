<?php

declare(strict_types=1);

namespace Folio\Pdf\Document;

use Folio\Pdf\Contracts\Node;
use Folio\Pdf\Contracts\PdfWriter;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Row;
use Folio\Pdf\Nodes\Table;
use Folio\Pdf\Nodes\TableCell;
use Folio\Pdf\Nodes\TableRow;
use Folio\Pdf\Nodes\Text;

/**
 * PDF document with flow layout, page breaks, and efficient rendering.
 */
final class Document
{
    private const float MARGIN_X = 40.0;
    private const float MARGIN_TOP = 36.0;
    private const float MARGIN_BOTTOM = 40.0;
    private const float BASE_ROW_HEIGHT = 18.0;
    private const float CELL_PAD_X = 5.0;
    private const float CELL_PAD_Y = 4.0;
    private const float TABLE_AFTER_GAP = 16.0;  // space after table before next heading
    private const float HEADING_AFTER_GAP = 8.0; // space after heading before table
    private const float TEXT_GAP = 12.0;
    private const float TITLE_GAP = 14.0;

    private readonly array $pages;

    /** @var list<string> */
    private array $gBuf = [];
    /** @var list<string> */
    private array $tBuf = [];
    private int $tableStyleIndex = 0;

    public function __construct(array $pages = [])
    {
        $this->pages = array_values($pages);
    }

    public static function make(): self
    {
        return new self();
    }

    public function addPage(Page $page): self
    {
        return new self([...$this->pages, $page]);
    }

    /** @return array<int, Page> */
    public function pages(): array
    {
        return $this->pages;
    }

    public function pageCount(): int
    {
        return count($this->pages);
    }

    public function generate(): PdfWriter
    {
        $writer = new \Folio\Pdf\Pdf\PdfFileWriter();

        $blocks = [];
        $pageWidth = 595.0;
        $pageHeight = 842.0;

        foreach ($this->pages as $page) {
            $pageWidth = $page->width();
            $pageHeight = $page->height();
            if ($page->content() !== null) {
                $this->collectBlocks($page->content(), $blocks);
            }
        }

        $contentWidth = $pageWidth - (self::MARGIN_X * 2);
        $renderedPages = $this->flowBlocksToPages($blocks, $pageWidth, $pageHeight, $contentWidth);

        foreach ($renderedPages as $content) {
            $writer->addPage($pageWidth, $pageHeight, $content);
        }

        if ($renderedPages === []) {
            $writer->addPage($pageWidth, $pageHeight, "BT\n/F1 12 Tf\nET\n");
        }

        return $writer;
    }

    /** @param array<int, Node> $blocks */
    private function collectBlocks(Node $node, array &$blocks): void
    {
        if ($node instanceof Column) {
            foreach ($node->children() as $child) {
                $this->collectBlocks($child, $blocks);
            }
            return;
        }

        $blocks[] = $node;
    }

    /**
     * @param array<int, Node> $blocks
     * @return array<int, string>
     */
    private function flowBlocksToPages(array $blocks, float $pageWidth, float $pageHeight, float $contentWidth): array
    {
        $pages = [];
        $this->gBuf = [];
        $this->tBuf = ["BT\n/F1 12 Tf\n"];
        $y = $pageHeight - self::MARGIN_TOP;
        $bottom = self::MARGIN_BOTTOM;
        $x = self::MARGIN_X;
        $this->tableStyleIndex = 0;
        $prevWasTable = false;

        $flush = function () use (&$pages): void {
            $this->tBuf[] = "ET\n";
            $pages[] = implode('', $this->gBuf) . implode('', $this->tBuf);
            $this->gBuf = [];
            $this->tBuf = ["BT\n/F1 12 Tf\n"];
        };

        $newPage = function () use (&$y, $pageHeight, $flush): void {
            $flush();
            $y = $pageHeight - self::MARGIN_TOP;
        };

        foreach ($blocks as $block) {
            if ($block instanceof Text) {
                if ($y - self::TEXT_GAP < $bottom) {
                    $newPage();
                }
                $this->tBuf[] = sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $x, $y, $this->escapePdfString($block->text()));
                $y -= self::TEXT_GAP;
                $prevWasTable = false;
                continue;
            }

            if ($block instanceof Heading) {
                // Extra gap when heading follows a table
                if ($prevWasTable) {
                    $y -= 6.0;
                }

                if ($y - self::TITLE_GAP - 24 < $bottom) {
                    $newPage();
                }

                $this->tBuf[] = sprintf(
                    "1 0 0 1 %.2f %.2f Tm\n/F1 13 Tf\n(%s) Tj\n/F1 12 Tf\n",
                    $x,
                    $y,
                    $this->escapePdfString($block->text())
                );
                $y -= self::HEADING_AFTER_GAP;
                $prevWasTable = false;
                continue;
            }

            if ($block instanceof Table) {
                $style = $this->tableStyleIndex % 3;
                $this->tableStyleIndex++;

                $this->flowTable(
                    $block,
                    $y,
                    $x,
                    $contentWidth,
                    $bottom,
                    $pageHeight,
                    $newPage,
                    $style
                );
                $y -= self::TABLE_AFTER_GAP;
                $prevWasTable = true;
                continue;
            }

            if ($block instanceof Row) {
                foreach ($block->children() as $child) {
                    if ($child instanceof Text) {
                        if ($y - self::TEXT_GAP < $bottom) {
                            $newPage();
                        }
                        $this->tBuf[] = sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $x, $y, $this->escapePdfString($child->text()));
                        $y -= self::TEXT_GAP;
                    }
                }
                $prevWasTable = false;
            }
        }

        if ($this->gBuf !== [] || count($this->tBuf) > 1) {
            $flush();
        }

        return $pages;
    }

    /**
     * @param callable(): void $newPage
     */
    private function flowTable(
        Table $table,
        float &$y,
        float $x,
        float $availableWidth,
        float $bottom,
        float $pageHeight,
        callable $newPage,
        int $style
    ): void {
        $rows = $table->rows();
        if ($rows === []) {
            return;
        }

        $columnCount = $this->resolveColumnCount($table);
        if ($columnCount === 0) {
            return;
        }

        $columnWidths = $this->resolveColumnWidths($table, $columnCount, $availableWidth);
        $rowHeights = $this->measureRowHeights($table, $columnWidths);

        $headerIndexes = [];
        foreach ($rows as $i => $row) {
            if ($row->isHeader()) {
                $headerIndexes[] = $i;
            } else {
                break;
            }
        }

        $headerHeight = 0.0;
        foreach ($headerIndexes as $hi) {
            $headerHeight += $rowHeights[$hi];
        }

        // Style palettes: header fill RGB + zebra fill RGB
        $styles = [
            // blue-gray headers, light blue zebra
            [[0.82, 0.86, 0.92], [0.95, 0.97, 0.99]],
            // green-gray headers, light green zebra
            [[0.84, 0.90, 0.84], [0.96, 0.98, 0.96]],
            // warm headers, light warm zebra
            [[0.92, 0.88, 0.82], [0.99, 0.97, 0.94]],
        ];
        [$headerRgb, $zebraRgb] = $styles[$style] ?? $styles[0];

        $drawRow = function (int $rowIndex, int $bodyIndex) use (
            $table,
            $rows,
            $rowHeights,
            $columnWidths,
            &$y,
            $x,
            $headerRgb,
            $zebraRgb
        ): void {
            $row = $rows[$rowIndex];
            $rowHeight = $rowHeights[$rowIndex];
            $rowTop = $y;
            $rowBottom = $y - $rowHeight;
            $cellX = $x;
            $colCursor = 0;
            $isHeaderRow = $row->isHeader();
            $zebra = !$isHeaderRow && ($bodyIndex % 2 === 1);

            $this->gBuf[] = "0.65 w\n0 0 0 RG\n";

            foreach ($row->cells() as $cell) {
                $colSpan = max(1, $cell->colSpan());
                $cellWidth = $this->sumColumnWidths($columnWidths, $colCursor, $colSpan);
                $isHeader = $cell->isHeader() || $isHeaderRow;

                if ($isHeader) {
                    $this->gBuf[] = sprintf(
                        "%.3f %.3f %.3f rg\n%.2f %.2f %.2f %.2f re f\n0 0 0 rg\n",
                        $headerRgb[0],
                        $headerRgb[1],
                        $headerRgb[2],
                        $cellX,
                        $rowBottom,
                        $cellWidth,
                        $rowHeight
                    );
                } elseif ($zebra) {
                    $this->gBuf[] = sprintf(
                        "%.3f %.3f %.3f rg\n%.2f %.2f %.2f %.2f re f\n0 0 0 rg\n",
                        $zebraRgb[0],
                        $zebraRgb[1],
                        $zebraRgb[2],
                        $cellX,
                        $rowBottom,
                        $cellWidth,
                        $rowHeight
                    );
                }

                if ($table->showBorders()) {
                    $this->gBuf[] = sprintf(
                        "%.2f %.2f %.2f %.2f re S\n",
                        $cellX,
                        $rowBottom,
                        $cellWidth,
                        $rowHeight
                    );
                }

                $this->renderCellContent(
                    $cell,
                    $isHeader,
                    $cellX + self::CELL_PAD_X,
                    $rowTop - self::CELL_PAD_Y,
                    max(14.0, $cellWidth - self::CELL_PAD_X * 2)
                );

                $cellX += $cellWidth;
                $colCursor += $colSpan;
            }

            $y = $rowBottom;
        };

        $drawHeaders = function () use ($headerIndexes, $drawRow): void {
            foreach ($headerIndexes as $hi) {
                $drawRow($hi, 0);
            }
        };

        $firstBody = count($headerIndexes);
        $minNeeded = $headerHeight + ($rowHeights[$firstBody] ?? self::BASE_ROW_HEIGHT);
        if ($y - $minNeeded < $bottom) {
            $newPage();
            $y = $pageHeight - self::MARGIN_TOP;
        }

        $drawHeaders();

        $bodyIndex = 0;
        for ($i = $firstBody; $i < count($rows); $i++) {
            $rowHeight = $rowHeights[$i];
            if ($y - $rowHeight < $bottom) {
                $newPage();
                $y = $pageHeight - self::MARGIN_TOP;
                $drawHeaders();
            }
            $drawRow($i, $bodyIndex);
            $bodyIndex++;
        }
    }

    private function renderCellContent(
        TableCell $cell,
        bool $isHeader,
        float $x,
        float $topY,
        float $maxWidth
    ): void {
        $content = $cell->content();

        if ($content instanceof Text) {
            $fontSize = $isHeader ? 10 : 9;
            $label = $this->truncateText($content->text(), $maxWidth, $fontSize);
            $this->tBuf[] = sprintf(
                "1 0 0 1 %.2f %.2f Tm\n/F1 %d Tf\n(%s) Tj\n/F1 12 Tf\n",
                $x,
                $topY - 9.0,
                $fontSize,
                $this->escapePdfString($label)
            );
            return;
        }

        if ($content instanceof Table) {
            // Nested: simple inline render without page splits
            $nestedY = $topY;
            $this->renderNestedTable($content, $nestedY, $x, $maxWidth);
            return;
        }

        if ($content instanceof Heading) {
            $this->tBuf[] = sprintf(
                "1 0 0 1 %.2f %.2f Tm\n/F1 10 Tf\n(%s) Tj\n/F1 12 Tf\n",
                $x,
                $topY - 9.0,
                $this->escapePdfString($content->text())
            );
        }
    }

    private function renderNestedTable(Table $table, float &$y, float $x, float $availableWidth): void
    {
        $rows = $table->rows();
        if ($rows === []) {
            return;
        }

        $columnCount = $this->resolveColumnCount($table);
        $columnWidths = $this->resolveColumnWidths($table, $columnCount, $availableWidth);
        $rowHeights = $this->measureRowHeights($table, $columnWidths);
        $this->gBuf[] = "0.55 w\n0 0 0 RG\n";

        foreach ($rows as $rowIndex => $row) {
            $rowHeight = $rowHeights[$rowIndex];
            $rowTop = $y;
            $rowBottom = $y - $rowHeight;
            $cellX = $x;
            $colCursor = 0;
            $isHeaderRow = $row->isHeader();

            foreach ($row->cells() as $cell) {
                $colSpan = max(1, $cell->colSpan());
                $cellWidth = $this->sumColumnWidths($columnWidths, $colCursor, $colSpan);
                $isHeader = $cell->isHeader() || $isHeaderRow;

                if ($isHeader) {
                    $this->gBuf[] = sprintf("0.90 0.90 0.92 rg\n%.2f %.2f %.2f %.2f re f\n0 0 0 rg\n", $cellX, $rowBottom, $cellWidth, $rowHeight);
                }
                if ($table->showBorders()) {
                    $this->gBuf[] = sprintf("%.2f %.2f %.2f %.2f re S\n", $cellX, $rowBottom, $cellWidth, $rowHeight);
                }

                $this->renderCellContent(
                    $cell,
                    $isHeader,
                    $cellX + self::CELL_PAD_X,
                    $rowTop - self::CELL_PAD_Y,
                    max(12.0, $cellWidth - self::CELL_PAD_X * 2)
                );

                $cellX += $cellWidth;
                $colCursor += $colSpan;
            }
            $y = $rowBottom;
        }
    }

    /** @param array<int, float> $columnWidths @return array<int, float> */
    private function measureRowHeights(Table $table, array $columnWidths): array
    {
        $heights = [];
        foreach ($table->rows() as $row) {
            $maxHeight = self::BASE_ROW_HEIGHT;
            $colCursor = 0;
            foreach ($row->cells() as $cell) {
                $colSpan = max(1, $cell->colSpan());
                $cellWidth = $this->sumColumnWidths($columnWidths, $colCursor, $colSpan);
                $innerWidth = max(12.0, $cellWidth - self::CELL_PAD_X * 2);
                $contentHeight = $this->measureCellContentHeight($cell, $innerWidth);
                $maxHeight = max($maxHeight, $contentHeight + self::CELL_PAD_Y * 2);
                $colCursor += $colSpan;
            }
            $heights[] = $maxHeight;
        }
        return $heights;
    }

    private function measureCellContentHeight(TableCell $cell, float $innerWidth): float
    {
        $content = $cell->content();
        if ($content instanceof Text || $content instanceof Heading) {
            return 11.0;
        }
        if ($content instanceof Table) {
            $cc = $this->resolveColumnCount($content);
            if ($cc === 0) {
                return self::BASE_ROW_HEIGHT;
            }
            $cw = $this->resolveColumnWidths($content, $cc, $innerWidth);
            return array_sum($this->measureRowHeights($content, $cw));
        }
        return 11.0;
    }

    private function resolveColumnCount(Table $table): int
    {
        $max = 0;
        foreach ($table->rows() as $row) {
            $count = 0;
            foreach ($row->cells() as $cell) {
                $count += max(1, $cell->colSpan());
            }
            $max = max($max, $count);
        }
        return $max;
    }

    /** @return array<int, float> */
    private function resolveColumnWidths(Table $table, int $columnCount, float $availableWidth): array
    {
        $configured = $table->columnWidths();
        if ($configured !== [] && count($configured) === $columnCount) {
            $sum = array_sum($configured);
            if ($sum > 0) {
                $scale = $availableWidth / $sum;
                return array_map(static fn(float $w): float => $w * $scale, array_map('floatval', $configured));
            }
        }
        return array_fill(0, $columnCount, $availableWidth / max(1, $columnCount));
    }

    /** @param array<int, float> $columnWidths */
    private function sumColumnWidths(array $columnWidths, int $start, int $span): float
    {
        $total = 0.0;
        for ($i = $start; $i < $start + $span; $i++) {
            $total += $columnWidths[$i] ?? 0.0;
        }
        return max(16.0, $total);
    }

    private function truncateText(string $text, float $maxWidth, int $fontSize): string
    {
        $maxChars = max(1, (int) floor($maxWidth / ($fontSize * 0.48)));
        if (strlen($text) <= $maxChars) {
            return $text;
        }
        return substr($text, 0, max(1, $maxChars - 1)) . '...';
    }

    private function escapePdfString(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
