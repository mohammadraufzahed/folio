<?php

declare(strict_types=1);

namespace Folio\Pdf\Font;

final readonly class Font
{
    public function __construct(
        public string $name,
        public ?string $path = null,
        public ?string $bytes = null,
        public ?string $family = null,
        public float $size = 12.0,
    ) {
    }

    public static function make(
        string $name,
        ?string $path = null,
        ?string $bytes = null,
        ?string $family = null,
        float $size = 12.0,
    ): self {
        return new self($name, $path, $bytes, $family, $size);
    }
}
