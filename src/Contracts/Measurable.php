<?php

declare(strict_types=1);

namespace Folio\Pdf\Contracts;

use Folio\Pdf\Layout\Size;

/**
 * Interface for objects that can measure their size.
 */
interface Measurable
{
    /**
     * Measure the intrinsic size of this object.
     */
    public function measure(): Size;
}
