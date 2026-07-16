<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

final class Theme
{
    /**
     * @param array<string, mixed> $tokens
     * @param array<string, mixed> $styles
     * @param array<string, Recipe> $recipes
     * @param array<string, SlotRecipe> $slotRecipes
     * @param array<string, TextStylePreset> $textStyles
     * @param array<string, LayerStylePreset> $layerStyles
     */
    public function __construct(
        public readonly string $name = 'default',
        public readonly array $tokens = [],
        public readonly array $styles = [],
        public readonly array $recipes = [],
        public readonly array $slotRecipes = [],
        public readonly array $textStyles = [],
        public readonly array $layerStyles = [],
        public readonly ?StyleSheet $stylesheet = null,
    ) {
    }

    public function tokenSet(): TokenSet
    {
        return TokenSet::fromArray($this->tokens);
    }

    /**
     * @return array<string, mixed>
     */
    public function style(string $name): array
    {
        $value = $this->styles[$name] ?? null;

        return is_array($value) ? $value : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function textStyle(string $name): array
    {
        $preset = $this->textStyles[$name] ?? null;

        return $preset !== null ? $preset->properties : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function layerStyle(string $name): array
    {
        $preset = $this->layerStyles[$name] ?? null;

        return $preset !== null ? $preset->properties : [];
    }

    public function recipe(string $name): ?Recipe
    {
        return $this->recipes[$name] ?? null;
    }

    public function slotRecipe(string $name): ?SlotRecipe
    {
        return $this->slotRecipes[$name] ?? null;
    }
}
