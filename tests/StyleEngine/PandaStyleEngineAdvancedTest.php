<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\StyleEngine;

use Folio\Pdf\Nodes\Text;
use Folio\Pdf\StyleEngine\PandaStyleEngine;
use Folio\Pdf\StyleEngine\Recipe;
use Folio\Pdf\StyleEngine\StyleContext;
use Folio\Pdf\StyleEngine\StyleRule;
use Folio\Pdf\StyleEngine\StyleSheet;
use Folio\Pdf\StyleEngine\TextStylePreset;
use Folio\Pdf\StyleEngine\Theme;
use Folio\Pdf\Styling\Color;
use PHPUnit\Framework\TestCase;

final class PandaStyleEngineAdvancedTest extends TestCase
{
    public function testResolvesThemeTextStyleAndColorTokens(): void
    {
        $theme = new Theme(
            'corporate',
            tokens: [
                'colors' => ['primary' => '#1a237e'],
                'space' => ['lg' => 24],
            ],
            textStyles: [
                'heading' => new TextStylePreset('heading', ['fontSize' => '24', 'color' => 'primary']),
            ],
        );

        $engine = new PandaStyleEngine();
        $node = Text::make('Title');
        $context = StyleContext::root($theme)->withTextStyle('heading');

        $computed = $engine->resolve($node, $context);

        self::assertSame(24.0, $computed->text->fontSize);
        self::assertEquals(Color::hex('#1a237e'), $computed->text->color);
    }

    public function testAppliesRecipeVariants(): void
    {
        $theme = new Theme(
            'ui',
            recipes: [
                'button' => new Recipe(
                    'button',
                    ['padding' => '8 16', 'radius' => 'md'],
                    [
                        'variant' => [
                            'primary' => ['color' => '#fff', 'background' => 'blue'],
                        ],
                        'size' => [
                            'lg' => ['padding' => '12 24', 'fontSize' => '16'],
                        ],
                    ],
                    ['variant' => 'primary'],
                ),
            ],
        );

        $engine = new PandaStyleEngine();
        $node = Text::make('Click');
        $context = StyleContext::root($theme)
            ->withRecipe('button', ['size' => 'lg']);

        $computed = $engine->resolve($node, $context);

        self::assertSame(16.0, $computed->text->fontSize);
        self::assertSame(12.0, $computed->box->paddingTop);
    }

    public function testAppliesStyleSheetRules(): void
    {
        $stylesheet = new StyleSheet(
            new StyleRule('text', ['fontSize' => '12']),
            new StyleRule('.primary', ['color' => '#1a237e']),
            new StyleRule(':odd', ['background' => '#f5f5f5'], ['odd']),
        );

        $theme = new Theme(
            'app',
            stylesheet: $stylesheet,
        );

        $engine = new PandaStyleEngine();
        $node = Text::make('Hello');
        $context = StyleContext::root($theme)
            ->withClassList(['primary'])
            ->withState('odd', true);

        $computed = $engine->resolve($node, $context);

        self::assertSame(12.0, $computed->text->fontSize);
        self::assertEquals(Color::hex('#1a237e'), $computed->text->color);
        self::assertEquals(Color::hex('#f5f5f5'), $computed->box->background);
    }
}
