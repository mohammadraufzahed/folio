<?php

declare(strict_types=1);

namespace Folio\Pdf\Contracts;

use Folio\Pdf\Image\Image;

interface ImageLoader
{
    public function load(string $path): Image;

    public function loadFromBytes(string $bytes): Image;

    public function supports(string $format): bool;
}
