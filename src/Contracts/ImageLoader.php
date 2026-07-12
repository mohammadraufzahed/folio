<?php

declare(strict_types=1);

namespace Folio\Pdf\Contracts;

use Folio\Pdf\Image\Image;

/**
 * Interface for image loaders.
 */
interface ImageLoader
{
    /**
     * Load an image from a path.
     */
    public function load(string $path): Image;

    /**
     * Load an image from binary data.
     */
    public function loadFromBytes(string $bytes): Image;

    /**
     * Check if the image format is supported.
     */
    public function supports(string $format): bool;
}
