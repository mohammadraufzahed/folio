<?php

declare(strict_types=1);

namespace Folio\Pdf\Support;

/**
 * Trait to make classes immutable.
 * Prevents modification of properties after construction.
 */
trait Immutable
{
    /**
     * Prevent setting undeclared properties.
     */
    public function __set(string $name, mixed $value): void
    {
        throw new \RuntimeException(sprintf(
            'Cannot set property "%s" on immutable class "%s"',
            $name,
            static::class
        ));
    }

    /**
     * Prevent unsetting properties.
     */
    public function __unset(string $name): void
    {
        throw new \RuntimeException(sprintf(
            'Cannot unset property "%s" on immutable class "%s"',
            $name,
            static::class
        ));
    }

    /**
     * Clone should return the same instance since objects are immutable.
     */
    public function __clone()
    {
        throw new \RuntimeException(sprintf(
            'Cannot clone immutable class "%s"',
            static::class
        ));
    }
}
