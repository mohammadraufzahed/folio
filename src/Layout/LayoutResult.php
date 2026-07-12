<?php

declare(strict_types=1);

namespace Folio\Pdf\Layout;

use Folio\Pdf\Support\Immutable;

/**
 * Result of layout calculations containing all layout boxes.
 */
final class LayoutResult
{
    use Immutable;

    private readonly array $layoutBoxes;

    public function __construct(array $layoutBoxes)
    {
        $this->layoutBoxes = array_values($layoutBoxes);
    }

    /**
     * @return array<int, LayoutBox>
     */
    public function layoutBoxes(): array
    {
        return $this->layoutBoxes;
    }

    public function pageCount(): int
    {
        return count($this->layoutBoxes);
    }
}
