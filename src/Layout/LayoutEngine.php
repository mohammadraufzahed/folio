<?php

declare(strict_types=1);

namespace Folio\Pdf\Layout;

use Folio\Pdf\Contracts\Layoutable;
use Folio\Pdf\Contracts\Node;
use Folio\Pdf\Document\Document;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Row;
use Folio\Pdf\Nodes\Text;
use Folio\Pdf\Nodes\Heading;

/**
 * Main layout engine that orchestrates layout calculations.
 */
final class LayoutEngine
{
    public function layout(Document $document): LayoutResult
    {
        $layoutBoxes = [];

        foreach ($document->pages() as $page) {
            $context = LayoutContext::make($page->width(), $page->height());
            $layoutBoxes[] = $this->layoutNode($page, $context);
        }

        return new LayoutResult($layoutBoxes);
    }

    public function layoutNode(Node $node, LayoutContext $context): LayoutBox
    {
        if ($node instanceof Page) {
            return $this->layoutPage($node, $context);
        }

        if ($node instanceof Column) {
            return $this->layoutColumn($node, $context);
        }

        if ($node instanceof Row) {
            return $this->layoutRow($node, $context);
        }

        if ($node instanceof Text) {
            return $this->layoutText($node, $context);
        }

        if ($node instanceof Heading) {
            return $this->layoutHeading($node, $context);
        }

        return $this->layoutDefault($node, $context);
    }

    private function layoutPage(Page $page, LayoutContext $context): LayoutBox
    {
        $size = Size::make($page->width(), $page->height());
        $position = Point::origin();

        $children = [];
        if ($page->content() !== null) {
            $innerContext = LayoutContext::make(
                $context->availableWidth(),
                $context->availableHeight()
            );
            $children[] = $this->layoutNode($page->content(), $innerContext);
        }

        return LayoutBox::make($position, $size)->withChildren($children);
    }

    private function layoutColumn(Column $column, LayoutContext $context): LayoutBox
    {
        $style = $column->style();
        $paddingTop = $style?->paddingTop() ?? 0.0;
        $paddingBottom = $style?->paddingBottom() ?? 0.0;
        $paddingLeft = $style?->paddingLeft() ?? 0.0;
        $paddingRight = $style?->paddingRight() ?? 0.0;

        $availableWidth = $context->availableWidth() - $paddingLeft - $paddingRight;
        $availableHeight = $context->availableHeight() - $paddingTop - $paddingBottom;

        $y = $paddingTop;
        $maxWidth = 0.0;
        $childBoxes = [];

        foreach ($column->children() as $child) {
            $childContext = LayoutContext::make($availableWidth, $availableHeight - $y);
            $childBox = $this->layoutNode($child, $childContext);
            $childBox = $childBox->withPosition(Point::make($paddingLeft, $y));

            $childBoxes[] = $childBox;
            $y += $childBox->height();
            $maxWidth = max($maxWidth, $childBox->width());
        }

        $totalWidth = $maxWidth + $paddingLeft + $paddingRight;
        $totalHeight = $y + $paddingBottom;

        return LayoutBox::make(
            Point::origin(),
            Size::make($totalWidth, $totalHeight)
        )->withChildren($childBoxes);
    }

    private function layoutRow(Row $row, LayoutContext $context): LayoutBox
    {
        $style = $row->style();
        $paddingTop = $style?->paddingTop() ?? 0.0;
        $paddingBottom = $style?->paddingBottom() ?? 0.0;
        $paddingLeft = $style?->paddingLeft() ?? 0.0;
        $paddingRight = $style?->paddingRight() ?? 0.0;

        $availableWidth = $context->availableWidth() - $paddingLeft - $paddingRight;
        $availableHeight = $context->availableHeight() - $paddingTop - $paddingBottom;

        $x = $paddingLeft;
        $maxHeight = 0.0;
        $childBoxes = [];

        foreach ($row->children() as $child) {
            $childContext = LayoutContext::make($availableWidth - $x, $availableHeight);
            $childBox = $this->layoutNode($child, $childContext);
            $childBox = $childBox->withPosition(Point::make($x, $paddingTop));

            $childBoxes[] = $childBox;
            $x += $childBox->width();
            $maxHeight = max($maxHeight, $childBox->height());
        }

        $totalWidth = $x + $paddingRight;
        $totalHeight = $maxHeight + $paddingTop + $paddingBottom;

        return LayoutBox::make(
            Point::origin(),
            Size::make($totalWidth, $totalHeight)
        )->withChildren($childBoxes);
    }

    private function layoutText(Text $text, LayoutContext $context): LayoutBox
    {
        $style = $text->style();
        $fontSize = $style?->fontSize() ?? 12.0;
        $lineHeight = $style?->lineHeight() ?? 1.5;

        $charWidth = $fontSize * 0.6;
        $textWidth = strlen($text->text()) * $charWidth;
        $textHeight = $fontSize * $lineHeight;

        $textWidth = min($textWidth, $context->availableWidth());

        return LayoutBox::make(
            Point::origin(),
            Size::make($textWidth, $textHeight)
        );
    }

    private function layoutHeading(Heading $heading, LayoutContext $context): LayoutBox
    {
        $style = $heading->style();
        $fontSize = $style?->fontSize() ?? (32.0 - ($heading->level() * 4));
        $lineHeight = $style?->lineHeight() ?? 1.2;

        $charWidth = $fontSize * 0.6;
        $textWidth = strlen($heading->text()) * $charWidth;
        $textHeight = $fontSize * $lineHeight;

        $textWidth = min($textWidth, $context->availableWidth());

        return LayoutBox::make(
            Point::origin(),
            Size::make($textWidth, $textHeight)
        );
    }

    private function layoutDefault(Node $node, LayoutContext $context): LayoutBox
    {
        // Default layout for unknown node types
        return LayoutBox::make(
            Point::origin(),
            Size::make($context->availableWidth(), 0.0)
        );
    }
}
