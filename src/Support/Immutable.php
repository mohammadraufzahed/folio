<?php

declare(strict_types=1);

namespace Folio\Pdf\Support;

trait Immutable
{
    public function __set(string $name, mixed $value): void
    {
        throw new \RuntimeException(sprintf(
            'Cannot set property "%s" on immutable class "%s"',
            $name,
            static::class
        ));
    }

    public function __unset(string $name): void
    {
        throw new \RuntimeException(sprintf(
            'Cannot unset property "%s" on immutable class "%s"',
            $name,
            static::class
        ));
    }

    public function __clone()
    {
        throw new \RuntimeException(sprintf(
            'Cannot clone immutable class "%s"',
            static::class
        ));
    }
}
