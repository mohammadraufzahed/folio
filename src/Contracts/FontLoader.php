<?php

declare(strict_types=1);

namespace Folio\Pdf\Contracts;

use Folio\Pdf\Font\Font;

/**
 * Interface for font loaders.
 */
interface FontLoader
{
    /**
     * Load a font by name.
     */
    public function load(string $name): Font;

    /**
     * Check if a font is available.
     */
    public function has(string $name): bool;
}
