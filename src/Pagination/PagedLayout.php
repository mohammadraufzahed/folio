<?php

declare(strict_types=1);

namespace Folio\Pdf\Pagination;

use Folio\Pdf\Layout\LayoutBox;

final readonly class PagedLayout
{
    /**
     * @param array<int, LayoutBox> $pages
     */
    public function __construct(private array $pages)
    {
    }

    /**
     * @return array<int, LayoutBox>
     */
    public function pages(): array
    {
        return $this->pages;
    }

    public function pageCount(): int
    {
        return count($this->pages);
    }
}
