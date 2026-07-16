<?php

declare(strict_types=1);

namespace Folio\Pdf\Image;

final readonly class ImageData
{
    public function __construct(
        public string $bytes,
        public string $format,
        public int $width,
        public int $height,
    ) {
    }
}
