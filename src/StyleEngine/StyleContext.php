<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

final readonly class StyleContext
{
    /**
     * @param array<string, mixed> $state
     */
    public function __construct(
        public ?ComputedStyle $parent = null,
        public ?Theme $theme = null,
        public array $state = [],
    ) {
    }

    public static function root(?Theme $theme = null): self
    {
        return new self(null, $theme);
    }

    public function withParent(ComputedStyle $parent): self
    {
        return new self($parent, $this->theme, $this->state);
    }

    public function withState(string $key, mixed $value): self
    {
        return new self($this->parent, $this->theme, [...$this->state, $key => $value]);
    }
}
