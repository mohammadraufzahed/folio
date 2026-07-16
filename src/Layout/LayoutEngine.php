<?php

declare(strict_types=1);

namespace Folio\Pdf\Layout;

use Folio\Pdf\Contracts\Node;
use Folio\Pdf\Document\Document;
use Folio\Pdf\Font\Core14FontMetrics;
use Folio\Pdf\Font\Font;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Row;
use Folio\Pdf\Nodes\Table;
use Folio\Pdf\Nodes\TableCell;
use Folio\Pdf\Nodes\TableRow;
use Folio\Pdf\Nodes\Text;
use Folio\Pdf\Nodes\TextRun;
use Folio\Pdf\Ports\FontMetricsPort;
use Folio\Pdf\StyleEngine\ComputedStyle;
use Folio\Pdf\StyleEngine\PandaStyleEngine;
use Folio\Pdf\StyleEngine\StyleContext;
use Folio\Pdf\StyleEngine\StyleEngine;
use Folio\Pdf\StyleEngine\StyleSheet;
use Folio\Pdf\StyleEngine\Theme;
use Folio\Pdf\Styling\FontWeight;
use Folio\Pdf\Styling\Length;
use Folio\Pdf\Styling\LengthUnit;
use Folio\Pdf\Template\Component;
use Folio\Pdf\Template\PartialRegistry;

final class LayoutEngine
{
    private readonly ?FontMetricsPort $fontMetrics;
    private readonly ?PartialRegistry $partials;
    private readonly ?StyleEngine $styleEngine;
    private readonly ?Theme $theme;
    private readonly ?StyleSheet $styleSheet;

    private ?Theme $activeTheme = null;

    public function __construct(
        ?FontMetricsPort $fontMetrics = null,
        ?PartialRegistry $partials = null,
        ?StyleEngine $styleEngine = null,
        ?Theme $theme = null,
        ?StyleSheet $styleSheet = null,
    ) {
        $this->fontMetrics = $fontMetrics;
        $this->partials = $partials;
        $this->styleEngine = $styleEngine;
        $this->theme = $theme;
        $this->styleSheet = $styleSheet;
    }

    private function fontMetrics(): FontMetricsPort
    {
        return $this->fontMetrics ?? Core14FontMetrics::default();
    }

    private function styleEngine(): StyleEngine
    {
        return $this->styleEngine ?? new PandaStyleEngine();
    }

    private function textWrapper(): TextWrapper
    {
        return new TextWrapper($this->fontMetrics());
    }

    public function layout(Document $document): LayoutResult
    {
        $this->activeTheme = $this->resolveTheme($document);

        $layoutBoxes = [];

        foreach ($document->pages() as $page) {
            $context = LayoutContext::make($page->width(), $page->height());
            $layoutBoxes[] = $this->layoutNode($page, $context);
        }

        return new LayoutResult($layoutBoxes);
    }

    private function resolveTheme(Document $document): ?Theme
    {
        $theme = $document->theme() ?? $this->theme;
        $styleSheet = $document->styleSheet() ?? $this->styleSheet;

        if ($styleSheet === null) {
            return $theme;
        }

        if ($theme === null) {
            return new Theme(stylesheet: $styleSheet);
        }

        return $theme->withStyleSheet($styleSheet);
    }

    public function layoutNode(Node $node, LayoutContext $context): LayoutBox
    {
        return $this->layoutNodeWithStyle($node, $context, null);
    }

    private function resolveStyle(Node $node, ?ComputedStyle $parent): ComputedStyle
    {
        $context = StyleContext::root($this->activeTheme ?? $this->theme)->withParent($parent);

        return $this->styleEngine()->resolve($node, $context);
    }

    private function layoutNodeWithStyle(Node $node, LayoutContext $context, ?ComputedStyle $parent): LayoutBox
    {
        $style = $this->resolveStyle($node, $parent);

        if ($node instanceof Page) {
            return $this->layoutPage($node, $context, $style);
        }

        if ($node instanceof Column) {
            return $this->layoutColumn($node, $context, $style);
        }

        if ($node instanceof Row) {
            return $this->layoutRow($node, $context, $style);
        }

        if ($node instanceof Text) {
            return $this->layoutText($node, $context, $style);
        }

        if ($node instanceof Heading) {
            return $this->layoutHeading($node, $context, $style);
        }

        if ($node instanceof TextRun) {
            return $this->layoutTextRun($node, $context, $style);
        }

        if ($node instanceof Table) {
            return $this->layoutTable($node, $context, $style);
        }

        if ($node instanceof TableRow) {
            return $this->layoutTableRow($node, $context, $style);
        }

        if ($node instanceof TableCell) {
            return $this->layoutTableCell($node, $context, $style);
        }

        if ($node instanceof Component) {
            return $this->layoutComponent($node, $context, $style);
        }

        return LayoutBox::make(
            Point::origin(),
            Size::make($context->availableWidth(), 0.0)
        )->withComputedStyle($style)
            ->withSource($node);
    }

    private function layoutPage(Page $page, LayoutContext $context, ComputedStyle $style): LayoutBox
    {
        $size = Size::make($page->width(), $page->height());
        $children = [];

        if ($page->content() !== null) {
            $innerContext = LayoutContext::make(
                $context->availableWidth(),
                $context->availableHeight()
            );
            $children[] = $this->layoutNodeWithStyle($page->content(), $innerContext, $style);
        }

        return LayoutBox::make(Point::origin(), $size)
            ->withChildren($children)
            ->withComputedStyle($style)
            ->withSource($page);
    }

    private function layoutColumn(Column $column, LayoutContext $context, ComputedStyle $style): LayoutBox
    {
        $paddingTop = $style->box->paddingTop ?? $style->box->padding ?? 0.0;
        $paddingBottom = $style->box->paddingBottom ?? $style->box->padding ?? 0.0;
        $paddingLeft = $style->box->paddingLeft ?? $style->box->padding ?? 0.0;
        $paddingRight = $style->box->paddingRight ?? $style->box->padding ?? 0.0;
        $gap = $style->layout->gap ?? 0.0;

        $explicitWidth = $this->resolveLength($style->layout->width ?? $style->box->width ?? null, $context->availableWidth());
        $baseWidth = $explicitWidth ?? $context->availableWidth();

        $availableWidth = max(0.0, $baseWidth - $paddingLeft - $paddingRight);
        $availableHeight = max(0.0, $context->availableHeight() - $paddingTop - $paddingBottom);

        $y = $paddingTop;
        $maxWidth = 0.0;
        $childBoxes = [];
        $count = count($column->children());

        foreach ($column->children() as $index => $child) {
            $childContext = LayoutContext::make($availableWidth, max(0.0, $availableHeight - $y));
            $childBox = $this->layoutNodeWithStyle($child, $childContext, $style);
            $childBox = $childBox->withPosition(Point::make($paddingLeft, $y));

            $childBoxes[] = $childBox;
            $y += $childBox->height();
            if ($index !== $count - 1) {
                $y += $gap;
            }
            $maxWidth = max($maxWidth, $childBox->width());
        }

        $totalWidth = max($explicitWidth ?? 0.0, $maxWidth + $paddingLeft + $paddingRight);
        $totalHeight = $y + $paddingBottom;

        return LayoutBox::make(
            Point::origin(),
            Size::make($totalWidth, $totalHeight),
            $childBoxes,
            $style,
            $column,
        );
    }

    private function layoutRow(Row $row, LayoutContext $context, ComputedStyle $style): LayoutBox
    {
        $paddingTop = $style->box->paddingTop ?? $style->box->padding ?? 0.0;
        $paddingBottom = $style->box->paddingBottom ?? $style->box->padding ?? 0.0;
        $paddingLeft = $style->box->paddingLeft ?? $style->box->padding ?? 0.0;
        $paddingRight = $style->box->paddingRight ?? $style->box->padding ?? 0.0;
        $gap = $style->layout->gap ?? 0.0;

        $explicitWidth = $this->resolveLength($style->layout->width ?? $style->box->width ?? null, $context->availableWidth());
        $baseWidth = $explicitWidth ?? $context->availableWidth();

        $availableWidth = max(0.0, $baseWidth - $paddingLeft - $paddingRight);
        $availableHeight = max(0.0, $context->availableHeight() - $paddingTop - $paddingBottom);

        $children = $row->children();
        $count = count($children);

        $naturalBoxes = [];
        $naturalWidth = 0.0;
        $totalGrow = 0.0;
        $gaps = max(0, $count - 1) * $gap;

        foreach ($children as $child) {
            $childContext = LayoutContext::make($availableWidth, $availableHeight);
            $childStyle = $this->resolveStyle($child, $style);
            $childBox = $this->layoutNodeWithStyle($child, $childContext, $style)
                ->withComputedStyle($childStyle);

            $naturalBoxes[] = $childBox;
            $naturalWidth += $childBox->width();
            $totalGrow += $childStyle->layout->grow ?? 0.0;
        }

        $remainingWidth = max(0.0, $availableWidth - $naturalWidth - $gaps);

        $x = $paddingLeft;
        $maxHeight = 0.0;
        $childBoxes = [];

        foreach ($naturalBoxes as $index => $childBox) {
            $childStyle = $childBox->computedStyle();
            $grow = $childStyle?->layout->grow ?? 0.0;

            if ($totalGrow > 0.0 && $grow > 0.0) {
                $extra = ($grow / $totalGrow) * $remainingWidth;
                $newWidth = $childBox->width() + $extra;
                $childBox = $childBox->withSize(Size::make($newWidth, $childBox->height()));
            }

            $childBox = $childBox->withPosition(Point::make($x, $paddingTop));
            $childBoxes[] = $childBox;
            $x += $childBox->width();
            if ($index !== $count - 1) {
                $x += $gap;
            }
            $maxHeight = max($maxHeight, $childBox->height());
        }

        $totalWidth = max($explicitWidth ?? 0.0, $x + $paddingRight);
        $totalHeight = $maxHeight + $paddingTop + $paddingBottom;

        return LayoutBox::make(
            Point::origin(),
            Size::make($totalWidth, $totalHeight),
            $childBoxes,
            $style,
            $row,
        );
    }

    private function layoutText(Text $text, LayoutContext $context, ComputedStyle $style): LayoutBox
    {
        $wrapped = $this->wrapComputedText($text->text(), $style, $context->availableWidth());

        return LayoutBox::make(
            Point::origin(),
            Size::make($wrapped->width, $wrapped->height),
            [],
            $style,
            $text,
        );
    }

    private function layoutTextRun(TextRun $textRun, LayoutContext $context, ComputedStyle $style): LayoutBox
    {
        $wrapped = $this->wrapComputedText($textRun->text(), $style, $context->availableWidth());

        return LayoutBox::make(
            Point::origin(),
            Size::make($wrapped->width, $wrapped->height),
            [],
            $style,
            $textRun,
        );
    }

    private function layoutHeading(Heading $heading, LayoutContext $context, ComputedStyle $style): LayoutBox
    {
        $fontSize = $style->text->fontSize ?? (32.0 - ($heading->level() * 4));
        $lineHeightMultiplier = $style->text->lineHeight ?? 1.2;
        $fontName = $this->resolveFontName($style->text->font ?? 'Helvetica', $style->text->fontWeight);

        $font = Font::make($fontName, size: $fontSize);

        $wrapped = $this->textWrapper()->wrap(
            $heading->text(),
            $font,
            $fontSize,
            $context->availableWidth(),
            $lineHeightMultiplier,
        );

        return LayoutBox::make(
            Point::origin(),
            Size::make($wrapped->width, $wrapped->height),
            [],
            $style,
            $heading,
        );
    }

    private function layoutTable(Table $table, LayoutContext $context, ComputedStyle $style): LayoutBox
    {
        $paddingTop = $style->box->paddingTop ?? $style->box->padding ?? 0.0;
        $paddingBottom = $style->box->paddingBottom ?? $style->box->padding ?? 0.0;
        $paddingLeft = $style->box->paddingLeft ?? $style->box->padding ?? 0.0;
        $paddingRight = $style->box->paddingRight ?? $style->box->padding ?? 0.0;

        $availableWidth = max(0.0, $context->availableWidth() - $paddingLeft - $paddingRight);
        $availableHeight = max(0.0, $context->availableHeight() - $paddingTop - $paddingBottom);

        $rows = $table->rows();
        $columnCount = $table->columnCount();
        $columnWidths = $this->resolveTableColumnWidths($table, $availableWidth, $columnCount);

        $rowBoxes = [];
        $y = $paddingTop;
        $maxWidth = 0.0;

        foreach ($rows as $row) {
            $rowContext = LayoutContext::make($availableWidth, max(0.0, $availableHeight - $y));
            $rowBox = $this->layoutTableRowWithCells($row, $rowContext, $style, $columnWidths, 0.0);
            $rowBox = $rowBox->withPosition(Point::make($paddingLeft, $y));

            $rowBoxes[] = $rowBox;
            $y += $rowBox->height();
            $maxWidth = max($maxWidth, $rowBox->width());
        }

        $totalWidth = $maxWidth + $paddingLeft + $paddingRight;
        $totalHeight = $y + $paddingBottom;

        return LayoutBox::make(
            Point::origin(),
            Size::make($totalWidth, $totalHeight),
            $rowBoxes,
            $style,
            $table,
        );
    }

    /**
     * @param array<int, float> $columnWidths
     */
    private function layoutTableRowWithCells(TableRow $row, LayoutContext $context, ComputedStyle $style, array $columnWidths, float $offsetX): LayoutBox
    {
        $x = 0.0;
        $maxHeight = 0.0;
        $cellBoxes = [];
        $cells = $row->cells();
        $rowStyle = $this->resolveStyle($row, $style);
        $columnIndex = 0;

        foreach ($cells as $cell) {
            $colSpan = max(1, $cell->colSpan());
            $cellWidth = 0.0;

            for ($i = 0; $i < $colSpan; $i++) {
                $cellWidth += $columnWidths[$columnIndex + $i] ?? 0.0;
            }

            $cellContext = LayoutContext::make(max(0.0, $cellWidth), $context->availableHeight());
            $cellBox = $this->layoutTableCell($cell, $cellContext, $rowStyle);
            $cellBox = $cellBox
                ->withSize(Size::make(max($cellBox->width(), $cellWidth), $cellBox->height()))
                ->withPosition(Point::make($offsetX + $x, 0.0));

            $cellBoxes[] = $cellBox;
            $x += $cellBox->width();
            $maxHeight = max($maxHeight, $cellBox->height());
            $columnIndex += $colSpan;
        }

        return LayoutBox::make(
            Point::origin(),
            Size::make($x, $maxHeight),
            $cellBoxes,
            $rowStyle,
            $row,
        );
    }

    private function layoutTableRow(TableRow $row, LayoutContext $context, ComputedStyle $style): LayoutBox
    {
        $columnCount = count($row->cells());
        $columnWidths = $this->resolveTableColumnWidthsFromCells($row->cells(), $context->availableWidth(), $columnCount);

        return $this->layoutTableRowWithCells($row, $context, $style, $columnWidths, 0.0);
    }

    /**
     * @param array<int, TableCell> $cells
     * @return array<int, float>
     */
    private function resolveTableColumnWidthsFromCells(array $cells, float $availableWidth, int $columnCount): array
    {
        $totalSpan = 0;

        foreach ($cells as $cell) {
            $totalSpan += max(1, $cell->colSpan());
        }

        $count = max(1, $columnCount, $totalSpan);
        $base = $availableWidth / $count;

        return array_fill(0, $count, $base);
    }

    private function layoutTableCell(TableCell $cell, LayoutContext $context, ComputedStyle $style): LayoutBox
    {
        $cellStyle = $this->resolveStyle($cell, $style);
        $contentBox = $this->layoutNodeWithStyle($cell->content(), $context, $cellStyle);
        $contentBox = $contentBox->withSize(
            Size::make(max($contentBox->width(), $context->availableWidth()), $contentBox->height())
        );

        return LayoutBox::make($contentBox->position(), $contentBox->size(), [$contentBox], $cellStyle, $cell);
    }

    /**
     * @return array<int, float>
     */
    private function resolveTableColumnWidths(Table $table, float $availableWidth, int $columnCount): array
    {
        $explicit = $table->columnWidths();

        if ($explicit !== []) {
            $widths = array_values($explicit);
            $count = max($columnCount, count($widths));
            $widths = array_pad($widths, $count, 0.0);
            $total = array_sum($widths);

            if ($total > 0) {
                return array_map(fn (float $w) => ($w / $total) * $availableWidth, $widths);
            }
        }

        $count = max(1, $columnCount);
        $base = $availableWidth / $count;

        return array_fill(0, $count, $base);
    }

    private function resolveLength(?Length $length, float $base): ?float
    {
        if ($length === null) {
            return null;
        }

        if ($length->unit() === LengthUnit::Percent) {
            return $base * ($length->value() / 100.0);
        }

        if ($length->unit() === LengthUnit::Fr || $length->unit() === LengthUnit::Auto) {
            return null;
        }

        return $length->toPixels();
    }

    private function resolveFontName(string $name, ?FontWeight $weight): string
    {
        $lower = strtolower($name);

        $family = match (true) {
            str_contains($lower, 'courier') => 'Courier',
            str_contains($lower, 'times') => 'Times',
            default => 'Helvetica',
        };

        $isBold = str_contains($lower, 'bold');
        $isItalic = str_contains($lower, 'italic') || str_contains($lower, 'oblique');
        $wantBold = ($weight !== null && $weight->value >= FontWeight::Bold->value) || $isBold;

        return match ([$family, $wantBold, $isItalic]) {
            ['Helvetica', false, false] => 'Helvetica',
            ['Helvetica', true, false] => 'Helvetica-Bold',
            ['Helvetica', false, true] => 'Helvetica-Oblique',
            ['Helvetica', true, true] => 'Helvetica-BoldOblique',
            ['Times', false, false] => 'Times-Roman',
            ['Times', true, false] => 'Times-Bold',
            ['Times', false, true] => 'Times-Italic',
            ['Times', true, true] => 'Times-BoldItalic',
            ['Courier', false, false] => 'Courier',
            ['Courier', true, false] => 'Courier-Bold',
            ['Courier', false, true] => 'Courier-Oblique',
            ['Courier', true, true] => 'Courier-BoldOblique',
        };
    }

    private function layoutComponent(Component $component, LayoutContext $context, ComputedStyle $style): LayoutBox
    {
        if ($this->partials === null) {
            return LayoutBox::make(
                Point::origin(),
                Size::make($context->availableWidth(), 0.0),
            )->withComputedStyle($style)
                ->withSource($component);
        }

        $resolved = $this->partials->resolve($component);

        return $this->layoutNodeWithStyle($resolved, $context, $style);
    }

    private function wrapComputedText(string $text, ComputedStyle $style, float $availableWidth): TextWrapResult
    {
        $fontSize = $style->text->fontSize ?? 12.0;
        $lineHeightMultiplier = $style->text->lineHeight ?? 1.2;
        $fontName = $this->resolveFontName($style->text->font ?? 'Helvetica', $style->text->fontWeight);

        $font = Font::make($fontName, size: $fontSize);

        return $this->textWrapper()->wrap($text, $font, $fontSize, $availableWidth, $lineHeightMultiplier);
    }
}
