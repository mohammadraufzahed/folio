<?php

declare(strict_types=1);

namespace Folio\Pdf\Pagination;

use Folio\Pdf\Contracts\HasChildren;
use Folio\Pdf\Contracts\Node;
use Folio\Pdf\Document\Document;
use Folio\Pdf\Layout\LayoutContext;
use Folio\Pdf\Layout\LayoutEngine;
use Folio\Pdf\Nodes\Page;

final class PaginationEngine
{
    private readonly float $pageWidth;
    private readonly float $pageHeight;
    private readonly float $margin;
    private readonly float $headerHeight;
    private readonly float $footerHeight;

    public function __construct(
        float $pageWidth = 595.0,
        float $pageHeight = 842.0,
        float $margin = 50.0,
        float $headerHeight = 50.0,
        float $footerHeight = 30.0
    ) {
        $this->pageWidth = $pageWidth;
        $this->pageHeight = $pageHeight;
        $this->margin = $margin;
        $this->headerHeight = $headerHeight;
        $this->footerHeight = $footerHeight;
    }

    public function paginate(Node $content): Document
    {
        $document = Document::make();
        $layoutEngine = new LayoutEngine();

        $availableWidth = $this->pageWidth - (2 * $this->margin);
        $availableHeight = $this->pageHeight - $this->headerHeight - $this->footerHeight - (2 * $this->margin);

        $pages = $this->paginateNode($content, $availableWidth, $availableHeight, $layoutEngine);

        foreach ($pages as $pageContent) {
            $page = Page::make($this->pageWidth, $this->pageHeight)
                ->withContent($pageContent);
            $document = $document->addPage($page);
        }

        return $document;
    }

    /**
     * @return array<int, Node>
     */
    private function paginateNode(
        Node $node,
        float $availableWidth,
        float $availableHeight,
        LayoutEngine $layoutEngine
    ): array {
        $context = LayoutContext::make($availableWidth, $availableHeight);
        $layoutBox = $layoutEngine->layoutNode($node, $context);

        if ($layoutBox->height() <= $availableHeight) {
            return [$node];
        }

        return $this->splitContent($node, $availableWidth, $availableHeight, $layoutEngine);
    }

    /**
     * @return array<int, Node>
     */
    private function splitContent(
        Node $node,
        float $availableWidth,
        float $availableHeight,
        LayoutEngine $layoutEngine
    ): array {
        $pages = [];
        $children = $node->children();

        if (empty($children)) {
            return [$node];
        }

        $currentPageChildren = [];
        $currentHeight = 0.0;

        foreach ($children as $child) {
            $context = LayoutContext::make($availableWidth, $availableHeight - $currentHeight);
            $childBox = $layoutEngine->layoutNode($child, $context);

            if ($currentHeight + $childBox->height() <= $availableHeight) {
                $currentPageChildren[] = $child;
                $currentHeight += $childBox->height();
            } else {
                if (!empty($currentPageChildren)) {
                    $pages[] = $this->createPageFromChildren($node, $currentPageChildren);
                }
                $currentPageChildren = [$child];
                $currentHeight = $childBox->height();
            }
        }

        $pages[] = $this->createPageFromChildren($node, $currentPageChildren);

        return $pages;
    }

    /**
     * @param array<int, Node> $children
     */
    private function createPageFromChildren(Node $parent, array $children): Node
    {
        if ($parent instanceof HasChildren) {
            return $parent->withChildren($children);
        }

        return $children[0];
    }
}
