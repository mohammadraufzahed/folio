<?php

declare(strict_types=1);

namespace Folio\Pdf\Document;

use Folio\Pdf\Nodes\Page;

final class Document
{
    /** @var array<int, Page> */
    private readonly array $pages;

    /**
     * @param array<int, Page> $pages
     */
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

    /**
     * @return array<int, Page>
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
