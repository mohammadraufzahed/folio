<?php

declare(strict_types=1);

namespace Folio\Pdf\Pagination;

use Folio\Pdf\Contracts\Node;
use Folio\Pdf\Font\Core14FontMetrics;
use Folio\Pdf\Font\Font;
use Folio\Pdf\Layout\LayoutBox;
use Folio\Pdf\Layout\LayoutContext;
use Folio\Pdf\Layout\LayoutEngine;
use Folio\Pdf\Layout\Point;
use Folio\Pdf\Layout\Size;
use Folio\Pdf\Layout\TextWrapper;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Row;
use Folio\Pdf\Nodes\Table;
use Folio\Pdf\Nodes\TableRow;
use Folio\Pdf\Nodes\Text;
use Folio\Pdf\Nodes\TextRun;
use Folio\Pdf\Ports\FontMetricsPort;

final class Paginator
{
    private readonly LayoutEngine $layoutEngine;
    private readonly FontMetricsPort $fontMetrics;

    public function __construct(?LayoutEngine $layoutEngine = null, ?FontMetricsPort $fontMetrics = null)
    {
        $this->layoutEngine = $layoutEngine ?? new LayoutEngine();
        $this->fontMetrics = $fontMetrics ?? Core14FontMetrics::default();
    }

    public function paginate(LayoutBox $content, Size $pageSize, float $headerHeight = 0.0, float $footerHeight = 0.0): PagedLayout
    {
        $availableHeight = max(0.0, $pageSize->height() - $headerHeight - $footerHeight);
        $pages = [];
        $remaining = $content;
        $guard = 0;

        while ($remaining !== null && $remaining->height() > 0.0) {
            if (++$guard > 100) {
                throw new \RuntimeException('Paginator guard exceeded. Remaining height: ' . $remaining->height());
            }
            [$page, $remaining] = $this->splitBox($remaining, $availableHeight);

            if ($page->height() > 0.0 || $page->children() !== []) {
                $pages[] = $this->placeOnPage($page, $pageSize, $headerHeight);
            } else {
                break;
            }
        }

        if ($pages === []) {
            $pages[] = $this->placeOnPage($content, $pageSize, $headerHeight);
        }

        return new PagedLayout($pages);
    }

    /**
     * @return array{0: LayoutBox, 1: LayoutBox|null}
     */
    private function splitBox(LayoutBox $box, float $availableHeight): array
    {
        if ($box->height() <= $availableHeight || $availableHeight <= 0.0) {
            return [$box, null];
        }

        $source = $box->source();

        if ($source instanceof Column || $source instanceof Table) {
            return $this->splitContainer($box, $availableHeight);
        }

        if ($source instanceof Text || $source instanceof TextRun || $source instanceof Heading) {
            return $this->splitTextBox($box, $availableHeight);
        }

        if ($source instanceof Row || $source instanceof TableRow) {
            return [$box, null];
        }

        if ($box->children() !== []) {
            return $this->splitContainer($box, $availableHeight);
        }

        return [$box, null];
    }

    /**
     * @return array{0: LayoutBox, 1: LayoutBox|null}
     */
    private function splitContainer(LayoutBox $box, float $availableHeight): array
    {
        $style = $box->computedStyle();
        $paddingTop = $style?->box->paddingTop ?? $style?->box->padding ?? 0.0;
        $paddingBottom = $style?->box->paddingBottom ?? $style?->box->padding ?? 0.0;
        $paddingLeft = $style?->box->paddingLeft ?? $style?->box->padding ?? 0.0;
        $paddingRight = $style?->box->paddingRight ?? $style?->box->padding ?? 0.0;
        $gap = $style?->layout->gap ?? 0.0;

        $contentHeight = max(0.0, $availableHeight - $paddingTop - $paddingBottom);

        $firstChildren = [];
        $restChildren = [];
        $currentY = $paddingTop;
        $state = 'first';

        $children = $box->children();
        $count = count($children);

        foreach ($children as $index => $child) {
            if ($state === 'rest') {
                $restChildren[] = $child;
                continue;
            }

            $childHeight = $child->height();
            $extra = ($index !== $count - 1) ? $gap : 0.0;

            if ($currentY + $childHeight + $extra <= $paddingTop + $contentHeight + 0.0001) {
                $firstChildren[] = $child->withPosition(Point::make($child->x(), $currentY));
                $currentY += $childHeight + $extra;
                continue;
            }

            $remainingHeight = max(0.0, $contentHeight - ($currentY - $paddingTop));
            [$firstPart, $restPart] = $this->splitBox($child, $remainingHeight);

            $wasSplit = $restPart !== null || ($firstPart !== $child && $firstPart->height() < $child->height() - 0.0001);

            if ($wasSplit && ($firstPart->height() > 0.0 || $firstPart->children() !== [])) {
                $firstChildren[] = $firstPart->withPosition(Point::make($firstPart->x(), $currentY));
                $currentY += $firstPart->height() + $extra;
            }

            if ($restPart !== null) {
                $restChildren[] = $restPart;
                $state = 'rest';
                continue;
            }

            if (!$wasSplit) {
                if ($firstChildren === []) {
                    $firstChildren[] = $child->withPosition(Point::make($child->x(), $currentY));
                    $currentY += $child->height() + $extra;
                } else {
                    $restChildren[] = $child;
                    $state = 'rest';
                }
            }
        }

        $firstHeight = $currentY + $paddingBottom;
        $firstWidth = $this->childrenWidth($firstChildren) + $paddingRight;

        $first = LayoutBox::make(
            Point::origin(),
            Size::make(max($box->width(), $firstWidth), min($availableHeight, $firstHeight)),
            $firstChildren,
            $style,
            $box->source(),
        );

        if ($restChildren === []) {
            return [$first, null];
        }

        $rest = LayoutBox::make(
            Point::origin(),
            Size::make($box->width(), $this->estimateRestHeight($restChildren) + $paddingTop + $paddingBottom),
            $this->repositionChildren($restChildren, $paddingTop, $gap),
            $style,
            $box->source(),
        );

        return [$first, $rest];
    }

    /**
     * @param array<int, LayoutBox> $children
     */
    private function repositionChildren(array $children, float $startY, float $gap): array
    {
        $y = $startY;
        $result = [];
        $count = count($children);

        foreach ($children as $index => $child) {
            $result[] = $child->withPosition(Point::make($child->x(), $y));
            $y += $child->height();
            if ($index !== $count - 1) {
                $y += $gap;
            }
        }

        return $result;
    }

    /**
     * @param array<int, LayoutBox> $children
     */
    private function childrenWidth(array $children): float
    {
        $max = 0.0;

        foreach ($children as $child) {
            $max = max($max, $child->x() + $child->width());
        }

        return $max;
    }

    /**
     * @param array<int, LayoutBox> $children
     */
    private function estimateRestHeight(array $children): float
    {
        $height = 0.0;
        $count = count($children);

        foreach ($children as $index => $child) {
            $height += $child->height();
            if ($index !== $count - 1) {
                $height += 0.0; // gap is already preserved by child positions in the original container
            }
        }

        return $height;
    }

    /**
     * @return array{0: LayoutBox, 1: LayoutBox|null}
     */
    private function splitTextBox(LayoutBox $box, float $availableHeight): array
    {
        $source = $box->source();
        $style = $box->computedStyle();

        $text = match (true) {
            $source instanceof Text => $source->text(),
            $source instanceof TextRun => $source->text(),
            $source instanceof Heading => $source->text(),
            default => '',
        };

        if ($text === '') {
            return [$box, null];
        }

        $fontSize = $style?->text->fontSize ?? 12.0;
        $lineHeightMultiplier = $style?->text->lineHeight ?? 1.2;
        $fontName = $style?->text->font ?? 'Helvetica';
        $font = Font::make($fontName, size: $fontSize);

        $wrapper = new TextWrapper($this->fontMetrics);
        [$firstWrap, $restWrap] = $wrapper->split($text, $font, $fontSize, $box->width(), $availableHeight, $lineHeightMultiplier);

        $firstNode = $this->textNodeFromSource($source, implode(' ', $firstWrap->lines), $style);
        $firstBox = $this->layoutEngine->layoutNode($firstNode, LayoutContext::make($box->width(), PHP_FLOAT_MAX));

        if ($restWrap->lines === []) {
            return [$firstBox->withSource($firstNode), null];
        }

        $restNode = $this->textNodeFromSource($source, implode(' ', $restWrap->lines), $style);
        $restBox = $this->layoutEngine->layoutNode($restNode, LayoutContext::make($box->width(), PHP_FLOAT_MAX));

        return [$firstBox->withSource($firstNode), $restBox->withSource($restNode)];
    }

    private function textNodeFromSource(Node $source, string $text, ?\Folio\Pdf\StyleEngine\ComputedStyle $style): Node
    {
        $nodeStyle = $source->style();

        return match (true) {
            $source instanceof Text => Text::make($text, $nodeStyle),
            $source instanceof Heading => Heading::make($text, $source->level(), $nodeStyle),
            $source instanceof TextRun => TextRun::fromText($text, $nodeStyle),
            default => Text::make($text, $nodeStyle),
        };
    }

    private function placeOnPage(LayoutBox $box, Size $pageSize, float $headerHeight): LayoutBox
    {
        return $box->withPosition(Point::make(0.0, $headerHeight))
            ->withSize(Size::make($pageSize->width(), min($pageSize->height() - $headerHeight, $box->height())));
    }
}
