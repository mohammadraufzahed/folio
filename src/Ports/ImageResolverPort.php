<?php

declare(strict_types=1);

namespace Folio\Pdf\Ports;

use Folio\Pdf\Image\ImageData;
use Folio\Pdf\Layout\Size;

interface ImageResolverPort
{
    public function size(string $source): Size;

    public function decode(string $source): ImageData;
}
