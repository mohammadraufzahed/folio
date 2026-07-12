<?php

declare(strict_types=1);

namespace Folio\Pdf\Contracts;

use Folio\Pdf\Layout\LayoutBox;
use Folio\Pdf\Layout\LayoutContext;

/**
 * Interface for objects that can be laid out.
 */
interface Layoutable
{
    /**
     * Calculate layout for this object.
     */
    public function layout(LayoutContext $context): LayoutBox;
}
