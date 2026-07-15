<?php

declare(strict_types=1);

namespace Folio\Pdf\Contracts;

use Folio\Pdf\Font\Font;

interface FontLoader
{
    public function load(string $name): Font;

    public function has(string $name): bool;
}
