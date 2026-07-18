<?php

declare(strict_types=1);

namespace Folio\Pdf\Document;

use Folio\Pdf\Nodes\Page;
use Folio\Pdf\StyleEngine\StyleSheet;
use Folio\Pdf\StyleEngine\Theme;

final class Document
{
    /** @var array<int, Page> */
    private readonly array $pages;

    public function __construct(
        array $pages = [],
        private readonly ?Theme $theme = null,
        private readonly ?StyleSheet $styleSheet = null,
    ) {
        $this->pages = array_values($pages);
    }

    public static function make(): self
    {
        return new self();
    }

    public function addPage(Page $page): self
    {
        return new self([...$this->pages, $page], $this->theme, $this->styleSheet);
    }

    public function withTheme(?Theme $theme): self
    {
        return new self($this->pages, $theme, $this->styleSheet);
    }

    public function withStyleSheet(?StyleSheet $styleSheet): self
    {
        return new self($this->pages, $this->theme, $styleSheet);
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

    public function theme(): ?Theme
    {
        return $this->theme;
    }

    public function styleSheet(): ?StyleSheet
    {
        return $this->styleSheet;
    }
}
