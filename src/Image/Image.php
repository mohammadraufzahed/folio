<?php

declare(strict_types=1);

namespace Folio\Pdf\Image;

final readonly class Image
{
    public function __construct(
        public string $format,
        public float $width,
        public float $height,
        public ?string $path = null,
        public ?string $bytes = null,
    ) {
    }

    public static function make(
        string $format,
        float $width,
        float $height,
        ?string $path = null,
        ?string $bytes = null,
    ): self {
        return new self($format, $width, $height, $path, $bytes);
    }
}
