<?php

declare(strict_types=1);

namespace Folio\Pdf\Layout;

use Folio\Pdf\Contracts\Node;
use Folio\Pdf\Styling\Flex;

final class FlexLayout
{
    public function layout(Node $node, LayoutContext $context): LayoutBox
    {
        $style = $node->style();
        $flexDirection = $style?->flex() ?? Flex::Column;

        return match ($flexDirection) {
            Flex::Row, Flex::RowReverse => $this->layoutRow($node, $context, $flexDirection),
            Flex::Column, Flex::ColumnReverse => $this->layoutColumn($node, $context, $flexDirection),
        };
    }

    private function layoutRow(Node $node, LayoutContext $context, Flex $direction): LayoutBox
    {
        $style = $node->style();
        $paddingTop = $style?->paddingTop() ?? 0.0;
        $paddingBottom = $style?->paddingBottom() ?? 0.0;
        $paddingLeft = $style?->paddingLeft() ?? 0.0;
        $paddingRight = $style?->paddingRight() ?? 0.0;

        $availableWidth = $context->availableWidth() - $paddingLeft - $paddingRight;
        $availableHeight = $context->availableHeight() - $paddingTop - $paddingBottom;

        $children = $node->children();
        if ($direction === Flex::RowReverse) {
            $children = array_reverse($children);
        }

        $totalGrow = 0.0;
        foreach ($children as $child) {
            $childStyle = $child->style();
            $grow = $childStyle?->grow() ?? 0.0;
            $totalGrow += $grow;
        }

        $x = $paddingLeft;
        $maxHeight = 0.0;
        $childBoxes = [];
        $remainingWidth = $availableWidth;

        foreach ($children as $child) {
            $childStyle = $child->style();
            $grow = $childStyle?->grow() ?? 0.0;
            $shrink = $childStyle?->shrink() ?? 1.0;

            if ($grow > 0 && $totalGrow > 0) {
                $childWidth = ($grow / $totalGrow) * $remainingWidth;
            } else {
                $childWidth = min($remainingWidth, $availableWidth);
            }

            $childContext = LayoutContext::make($childWidth, $availableHeight);
            $childBox = $this->layoutChild($child, $childContext);
            $childBox = $childBox->withPosition(Point::make($x, $paddingTop));

            $childBoxes[] = $childBox;
            $x += $childBox->width();
            $maxHeight = max($maxHeight, $childBox->height());
            $remainingWidth -= $childBox->width();
        }

        $totalWidth = $x + $paddingRight;
        $totalHeight = $maxHeight + $paddingTop + $paddingBottom;

        return LayoutBox::make(
            Point::origin(),
            Size::make($totalWidth, $totalHeight)
        )->withChildren($childBoxes);
    }

    private function layoutColumn(Node $node, LayoutContext $context, Flex $direction): LayoutBox
    {
        $style = $node->style();
        $paddingTop = $style?->paddingTop() ?? 0.0;
        $paddingBottom = $style?->paddingBottom() ?? 0.0;
        $paddingLeft = $style?->paddingLeft() ?? 0.0;
        $paddingRight = $style?->paddingRight() ?? 0.0;

        $availableWidth = $context->availableWidth() - $paddingLeft - $paddingRight;
        $availableHeight = $context->availableHeight() - $paddingTop - $paddingBottom;

        $children = $node->children();
        if ($direction === Flex::ColumnReverse) {
            $children = array_reverse($children);
        }

        $totalGrow = 0.0;
        foreach ($children as $child) {
            $childStyle = $child->style();
            $grow = $childStyle?->grow() ?? 0.0;
            $totalGrow += $grow;
        }

        $y = $paddingTop;
        $maxWidth = 0.0;
        $childBoxes = [];
        $remainingHeight = $availableHeight;

        foreach ($children as $child) {
            $childStyle = $child->style();
            $grow = $childStyle?->grow() ?? 0.0;

            if ($grow > 0 && $totalGrow > 0) {
                $childHeight = ($grow / $totalGrow) * $remainingHeight;
            } else {
                $childHeight = $remainingHeight;
            }

            $childContext = LayoutContext::make($availableWidth, $childHeight);
            $childBox = $this->layoutChild($child, $childContext);
            $childBox = $childBox->withPosition(Point::make($paddingLeft, $y));

            $childBoxes[] = $childBox;
            $y += $childBox->height();
            $maxWidth = max($maxWidth, $childBox->width());
            $remainingHeight -= $childBox->height();
        }

        $totalWidth = $maxWidth + $paddingLeft + $paddingRight;
        $totalHeight = $y + $paddingBottom;

        return LayoutBox::make(
            Point::origin(),
            Size::make($totalWidth, $totalHeight)
        )->withChildren($childBoxes);
    }

    public function layoutChild(Node $child, LayoutContext $context): LayoutBox
    {
        $layoutEngine = new LayoutEngine();
        return $layoutEngine->layoutNode($child, $context);
    }
}
