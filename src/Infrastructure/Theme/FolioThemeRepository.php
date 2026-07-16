<?php

declare(strict_types=1);

namespace Folio\Pdf\Infrastructure\Theme;

use Folio\Pdf\Ports\ThemeRepositoryPort;
use Folio\Pdf\StyleEngine\FolioStyleParser;
use Folio\Pdf\StyleEngine\LayerStylePreset;
use Folio\Pdf\StyleEngine\Recipe;
use Folio\Pdf\StyleEngine\SlotRecipe;
use Folio\Pdf\StyleEngine\StyleRule;
use Folio\Pdf\StyleEngine\StyleSheet;
use Folio\Pdf\StyleEngine\TextStylePreset;
use Folio\Pdf\StyleEngine\Theme;

final class FolioThemeRepository implements ThemeRepositoryPort
{
    /** @var list<string> */
    private array $searchPaths;

    /**
     * @param string|list<string>|null $baseDir
     */
    public function __construct(string|array|null $baseDir = null)
    {
        $this->searchPaths = [];

        if (is_string($baseDir)) {
            $this->addSearchPath($baseDir);
        } elseif (is_array($baseDir)) {
            foreach ($baseDir as $dir) {
                $this->addSearchPath($dir);
            }
        }

        if ($this->searchPaths === []) {
            $this->addSearchPath(getcwd() ?: '.');
        }
    }

    /**
     * @phpstan-impure
     */
    public function addSearchPath(string $dir): self
    {
        $dir = rtrim($dir, '/\\');

        if ($dir !== '' && is_dir($dir) && !in_array($dir, $this->searchPaths, true)) {
            $this->searchPaths[] = $dir;
        }

        return $this;
    }

    public function load(string $name): Theme
    {
        $data = $this->loadJson($name);

        return $this->themeFromArray($name, $data);
    }

    /**
     * @return array<string, mixed>
     */
    private function loadJson(string $name): array
    {
        $candidates = $this->candidatePaths($name);

        foreach ($candidates as $path) {
            if (!is_file($path) || !is_readable($path)) {
                continue;
            }

            $content = file_get_contents($path);

            if ($content === false) {
                continue;
            }

            $decoded = json_decode($content, true);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        throw new \RuntimeException("Theme not found: {$name} (searched " . implode(', ', $candidates) . ')');
    }

    /**
     * @return list<string>
     */
    private function candidatePaths(string $name): array
    {
        $candidates = [];

        foreach ($this->searchPaths as $dir) {
            if (str_contains($name, '/') || str_contains($name, '\\')) {
                $candidates[] = $dir . '/' . $name;
                $candidates[] = $dir . '/' . $name . '.json';
                $candidates[] = $dir . '/' . $name . '.theme.json';

                continue;
            }

            $candidates[] = $dir . '/themes/' . $name . '.json';
            $candidates[] = $dir . '/' . $name . '.theme.json';
            $candidates[] = $dir . '/' . $name . '.json';
            $candidates[] = $dir . '/themes/' . $name;
            $candidates[] = $dir . '/' . $name;
        }

        return $candidates;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function themeFromArray(string $name, array $data): Theme
    {
        $tokens = $this->normalizeTokens($data['tokens'] ?? []);
        $styles = $this->normalizeStyles($data['styles'] ?? []);

        return new Theme(
            name: (string) ($data['name'] ?? $name),
            tokens: $tokens,
            styles: $styles,
            recipes: $this->buildRecipes($data['recipes'] ?? []),
            slotRecipes: $this->buildSlotRecipes($data['slotRecipes'] ?? []),
            textStyles: $this->buildTextStyles($data['textStyles'] ?? []),
            layerStyles: $this->buildLayerStyles($data['layerStyles'] ?? []),
            stylesheet: $this->buildStyleSheet($data['stylesheet'] ?? null),
        );
    }

    /**
     * @param mixed $tokens
     *
     * @return array<string, array<string, mixed>>
     */
    private function normalizeTokens(mixed $tokens): array
    {
        if (!is_array($tokens)) {
            return [];
        }

        $normalized = [];

        foreach ($tokens as $category => $values) {
            if (!is_string($category) || !is_array($values)) {
                continue;
            }

            $normalized[$category] = [];

            foreach ($values as $key => $value) {
                if (is_string($key)) {
                    $normalized[$category][$key] = $value;
                }
            }
        }

        return $normalized;
    }

    /**
     * @param mixed $styles
     *
     * @return array<string, array<string, mixed>>
     */
    private function normalizeStyles(mixed $styles): array
    {
        if (!is_array($styles)) {
            return [];
        }

        $normalized = [];

        foreach ($styles as $name => $properties) {
            if (is_string($name) && is_array($properties)) {
                $normalized[$name] = $properties;
            }
        }

        return $normalized;
    }

    /**
     * @param mixed $recipes
     *
     * @return array<string, Recipe>
     */
    private function buildRecipes(mixed $recipes): array
    {
        if (!is_array($recipes)) {
            return [];
        }

        $result = [];

        foreach ($recipes as $name => $recipeData) {
            if (!is_string($name) || !is_array($recipeData)) {
                continue;
            }

            $base = $recipeData['base'] ?? [];
            $variants = $this->normalizeVariants($recipeData['variants'] ?? []);
            $defaultVariants = $this->normalizeDefaultVariants($recipeData['defaultVariants'] ?? []);

            if (!is_array($base)) {
                $base = [];
            }

            $result[$name] = new Recipe($name, $base, $variants, $defaultVariants);
        }

        return $result;
    }

    /**
     * @param mixed $slotRecipes
     *
     * @return array<string, SlotRecipe>
     */
    private function buildSlotRecipes(mixed $slotRecipes): array
    {
        if (!is_array($slotRecipes)) {
            return [];
        }

        $result = [];

        foreach ($slotRecipes as $name => $recipeData) {
            if (!is_string($name) || !is_array($recipeData)) {
                continue;
            }

            $base = $recipeData['base'] ?? [];
            $slots = $this->normalizeStyles($recipeData['slots'] ?? []);
            $variants = $this->normalizeVariants($recipeData['variants'] ?? []);

            if (!is_array($base)) {
                $base = [];
            }

            $result[$name] = new SlotRecipe($name, $base, $slots, $variants);
        }

        return $result;
    }

    /**
     * @param mixed $textStyles
     *
     * @return array<string, TextStylePreset>
     */
    private function buildTextStyles(mixed $textStyles): array
    {
        if (!is_array($textStyles)) {
            return [];
        }

        $result = [];

        foreach ($textStyles as $name => $properties) {
            if (is_string($name) && is_array($properties)) {
                $result[$name] = new TextStylePreset($name, $properties);
            }
        }

        return $result;
    }

    /**
     * @param mixed $layerStyles
     *
     * @return array<string, LayerStylePreset>
     */
    private function buildLayerStyles(mixed $layerStyles): array
    {
        if (!is_array($layerStyles)) {
            return [];
        }

        $result = [];

        foreach ($layerStyles as $name => $properties) {
            if (is_string($name) && is_array($properties)) {
                $result[$name] = new LayerStylePreset($name, $properties);
            }
        }

        return $result;
    }

    /**
     * @param mixed $stylesheet
     */
    private function buildStyleSheet(mixed $stylesheet): ?StyleSheet
    {
        if ($stylesheet === null) {
            return null;
        }

        if (is_string($stylesheet)) {
            return (new FolioStyleParser())->parse($stylesheet);
        }

        if (is_array($stylesheet)) {
            return $this->buildStyleSheetFromArray($stylesheet);
        }

        return null;
    }

    /**
     * @param array<int, mixed>|array<string, mixed> $rules
     */
    private function buildStyleSheetFromArray(array $rules): StyleSheet
    {
        $styleRules = [];

        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                continue;
            }

            $selector = $rule['selector'] ?? '';

            if (!is_string($selector) || $selector === '') {
                continue;
            }

            $properties = $rule['properties'] ?? [];

            if (!is_array($properties)) {
                $properties = [];
            }

            $conditions = [];
            $rawConditions = $rule['conditions'] ?? [];

            if (is_array($rawConditions)) {
                foreach ($rawConditions as $condition) {
                    if (is_string($condition)) {
                        $conditions[] = $condition;
                    }
                }
            }

            $styleRules[] = new StyleRule($selector, $properties, $conditions);
        }

        return new StyleSheet(...$styleRules);
    }

    /**
     * @param mixed $variants
     *
     * @return array<string, array<string, array<string, mixed>>>
     */
    private function normalizeVariants(mixed $variants): array
    {
        if (!is_array($variants)) {
            return [];
        }

        $normalized = [];

        foreach ($variants as $group => $options) {
            if (!is_string($group) || !is_array($options)) {
                continue;
            }

            foreach ($options as $option => $properties) {
                if (is_string($option) && is_array($properties)) {
                    $normalized[$group][$option] = $properties;
                }
            }
        }

        return $normalized;
    }

    /**
     * @param mixed $defaults
     *
     * @return array<string, string>
     */
    private function normalizeDefaultVariants(mixed $defaults): array
    {
        if (!is_array($defaults)) {
            return [];
        }

        $normalized = [];

        foreach ($defaults as $group => $option) {
            if (is_string($group) && is_string($option)) {
                $normalized[$group] = $option;
            }
        }

        return $normalized;
    }
}
