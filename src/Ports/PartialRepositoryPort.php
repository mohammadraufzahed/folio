<?php

declare(strict_types=1);

namespace Folio\Pdf\Ports;

interface PartialRepositoryPort
{
    public function load(string $name): string;
}
