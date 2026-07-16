<?php

declare(strict_types=1);

namespace Folio\Pdf\Ports;

use Folio\Pdf\StyleEngine\Theme;

interface ThemeRepositoryPort
{
    public function load(string $name): Theme;
}
