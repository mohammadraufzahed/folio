<?php

declare(strict_types=1);

namespace Folio\Pdf\Layout;

use Folio\Pdf\Contracts\Node;
use Folio\Pdf\Styling\Style;

/**
 * Grid layout engine for 2D grid layouts.
 */
final class GridLayout
{
    private readonly int $columns;
    private readonly float $gap;

    public function __construct(int $columns = 2, float $gap = 10.0)
    {
        $this->columns = max(1, $columns);
        $this->gap = max(0.0, $gap);
    }

    public function layout(Node $node, LayoutContext $context): LayoutBox
    {
        $style = $node->style();
        $paddingTop = $style?->paddingTop() ?? 0.0;
        $paddingBottom = $style?->paddingBottom() ?? 0.0;
        $paddingLeft = $style?->paddingLeft() ?? 0.0;
        $paddingRight = $style?->paddingRight() ?? 0.0;

        $availableWidth = $context->availableWidth() - $paddingLeft - $paddingRight;
        $availableHeight = $context->availableHeight() - $paddingTop - $paddingBottom;

        $children = $node->children();
        $columnWidth = ($availableWidth - ($this->columns - 1) * $this->gap) / $this->columns;

        $childBoxes = [];
        $x = $paddingLeft;
        $y = $paddingTop;
        $maxRowHeight = 0.0;
        $currentColumn = 0;

        foreach ($children as $child) {
            $childContext = LayoutContext::make($columnWidth, $availableHeight - ($y - $paddingTop));
            $childBox = $this->layoutChild($child, $childContext);
            $childBox = $childBox->withPosition(Point::make($x, $y));

            $childBoxes[] = $childBox;
            $maxRowHeight = max($maxRowHeight, $childBox->height());

            $currentColumn++;
            if ($currentColumn >= $this->columns) {
                $x = $paddingLeft;
                $y += $maxRowHeight + $this->gap;
                $maxRowHeight = 0.0;
                $currentColumn = 0;
            } else {
                $x += $columnWidth + $this->gap;
            }
        }

        $totalWidth = $availableWidth + $paddingLeft + $paddingRight;
        $totalHeight = $y + $maxRowHeight + $paddingBottom;

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
