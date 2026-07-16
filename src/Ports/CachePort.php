<?php

declare(strict_types=1);

namespace Folio\Pdf\Ports;

interface CachePort
{
    public function get(string $key): mixed;

    public function set(string $key, mixed $value): void;

    public function has(string $key): bool;

    public function clear(): void;
}
