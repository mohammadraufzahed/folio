<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

final readonly class StyleContext
{
    /**
     * @param array<string, mixed> $state
     * @param array<int, string> $classList
     * @param array<string, mixed> $rawProperties
     * @param array<string, string> $variants
     */
    public function __construct(
        public ?ComputedStyle $parent = null,
        public ?Theme $theme = null,
        public array $state = [],
        public array $classList = [],
        public array $rawProperties = [],
        public ?string $recipe = null,
        public ?string $slot = null,
        public ?string $textStyle = null,
        public ?string $layerStyle = null,
        public array $variants = [],
    ) {
    }

    public static function root(?Theme $theme = null): self
    {
        return new self(null, $theme);
    }

    public function withParent(?ComputedStyle $parent): self
    {
        return new self($parent, $this->theme, $this->state, $this->classList, $this->rawProperties, $this->recipe, $this->slot, $this->textStyle, $this->layerStyle, $this->variants);
    }

    public function withTheme(Theme $theme): self
    {
        return new self($this->parent, $theme, $this->state, $this->classList, $this->rawProperties, $this->recipe, $this->slot, $this->textStyle, $this->layerStyle, $this->variants);
    }

    public function withState(string $key, mixed $value): self
    {
        return new self($this->parent, $this->theme, [...$this->state, $key => $value], $this->classList, $this->rawProperties, $this->recipe, $this->slot, $this->textStyle, $this->layerStyle, $this->variants);
    }

    /**
     * @param array<int, string> $classList
     */
    public function withClassList(array $classList): self
    {
        return new self($this->parent, $this->theme, $this->state, $classList, $this->rawProperties, $this->recipe, $this->slot, $this->textStyle, $this->layerStyle, $this->variants);
    }

    /**
     * @param array<string, mixed> $rawProperties
     */
    public function withRawProperties(array $rawProperties): self
    {
        return new self($this->parent, $this->theme, $this->state, $this->classList, $rawProperties, $this->recipe, $this->slot, $this->textStyle, $this->layerStyle, $this->variants);
    }

    public function withRecipe(string $name, array $variants = []): self
    {
        return new self($this->parent, $this->theme, $this->state, $this->classList, $this->rawProperties, $name, $this->slot, $this->textStyle, $this->layerStyle, $variants);
    }

    public function withSlot(string $slot): self
    {
        return new self($this->parent, $this->theme, $this->state, $this->classList, $this->rawProperties, $this->recipe, $slot, $this->textStyle, $this->layerStyle, $this->variants);
    }

    public function withTextStyle(string $name): self
    {
        return new self($this->parent, $this->theme, $this->state, $this->classList, $this->rawProperties, $this->recipe, $this->slot, $name, $this->layerStyle, $this->variants);
    }

    public function withLayerStyle(string $name): self
    {
        return new self($this->parent, $this->theme, $this->state, $this->classList, $this->rawProperties, $this->recipe, $this->slot, $this->textStyle, $name, $this->variants);
    }

    public function tokenSet(): TokenSet
    {
        return $this->theme?->tokenSet() ?? new TokenSet();
    }
}
