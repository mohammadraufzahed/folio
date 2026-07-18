<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

use Folio\Pdf\Contracts\Node;
use Folio\Pdf\Styling\Style;

final readonly class PandaStyleEngine implements StyleEngine
{
    public function resolve(Node $node, StyleContext $context): ComputedStyle
    {
        $style = $node->style() ?? Style::make();
        $tokens = $context->tokenSet();

        $classList = $style->classList();

        if ($classList !== []) {
            $context = $context->withClassList($classList);
        }

        $builder = new ComputedStyleBuilder();

        $this->applyInheritance($builder, $context);
        $this->applyPresets($builder, $context, $tokens);
        $this->applyNamedStyles($builder, $context, $tokens);
        $this->applyRecipes($builder, $context, $tokens);

        if (!empty($context->classList)) {
            $builder->apply(Utility::resolve($context->classList, $tokens), $tokens);
        }

        $this->applyStylesheet($builder, $node, $context, $tokens);

        $inlineProperties = array_filter(
            $style->toArray(),
            static fn (mixed $value): bool => $value !== null && $value !== [],
        );

        $builder->apply($inlineProperties, $tokens);

        if (!empty($context->rawProperties)) {
            $builder->apply($context->rawProperties, $tokens);
        }

        return $builder->build();
    }

    private function applyInheritance(ComputedStyleBuilder $builder, StyleContext $context): void
    {
        $inherited = $context->parent;

        if ($inherited === null) {
            return;
        }

        $builder->withColor($inherited->text->color, false);
        $builder->withFont($inherited->text->font, false);
        $builder->withFontSize($inherited->text->fontSize, false);
        $builder->withFontWeight($inherited->text->fontWeight, false);
        $builder->withLineHeight($inherited->text->lineHeight, false);
        $builder->withAlignment($inherited->text->alignment, false);
    }

    private function applyPresets(ComputedStyleBuilder $builder, StyleContext $context, TokenSet $tokens): void
    {
        $theme = $context->theme;

        if ($theme === null) {
            return;
        }

        if ($context->textStyle !== null) {
            $builder->apply($theme->textStyle($context->textStyle), $tokens);
        }

        if ($context->layerStyle !== null) {
            $builder->apply($theme->layerStyle($context->layerStyle), $tokens);
        }
    }

    private function applyNamedStyles(ComputedStyleBuilder $builder, StyleContext $context, TokenSet $tokens): void
    {
        $theme = $context->theme;

        if ($theme === null) {
            return;
        }

        foreach ($context->classList as $class) {
            $properties = $theme->style($class);

            if (!empty($properties)) {
                $builder->apply($properties, $tokens);
            }
        }
    }

    private function applyRecipes(ComputedStyleBuilder $builder, StyleContext $context, TokenSet $tokens): void
    {
        $theme = $context->theme;

        if ($theme === null || $context->recipe === null) {
            return;
        }

        if ($context->slot !== null) {
            $slotRecipe = $theme->slotRecipe($context->recipe);

            if ($slotRecipe !== null) {
                $builder->apply($slotRecipe->resolve($context->slot, $context->variants), $tokens);

                return;
            }
        }

        $recipe = $theme->recipe($context->recipe);

        if ($recipe !== null) {
            $builder->apply($recipe->resolve($context->variants), $tokens);
        }
    }

    private function applyStylesheet(ComputedStyleBuilder $builder, Node $node, StyleContext $context, TokenSet $tokens): void
    {
        $theme = $context->theme;

        if ($theme === null || $theme->stylesheet === null) {
            return;
        }

        $properties = $theme->stylesheet->matchingProperties(
            $node->type(),
            $context->classList,
            $context,
        );

        if (!empty($properties)) {
            $builder->apply($properties, $tokens);
        }
    }
}
