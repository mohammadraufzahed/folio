<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

use Folio\Pdf\Contracts\Node;
use InvalidArgumentException;

final class PartialRegistry
{
    /** @var array<string, Partial> */
    private array $partials = [];

    public function register(Partial $partial): self
    {
        $this->partials[$partial->name] = $partial;

        return $this;
    }

    public function resolve(Component $component): Node
    {
        $partial = $this->partials[$component->name()] ?? null;

        if ($partial === null) {
            throw new InvalidArgumentException("Partial '{$component->name()}' not found.");
        }

        return $partial->resolve($component->props(), $component->slots());
    }

    public function has(string $name): bool
    {
        return isset($this->partials[$name]);
    }
}
