<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\Template;

use Folio\Pdf\Template\TemplateEngine;
use PHPUnit\Framework\TestCase;

final class ThemeAndStyleTest extends TestCase
{
    public function testRendersTemplateWithThemeAndStyleAndUse(): void
    {
        $dir = sys_get_temp_dir() . '/folio-theme-style-' . uniqid();
        mkdir($dir);

        file_put_contents($dir . '/partial.folio', 'text "Partial text"');

        file_put_contents($dir . '/modern.json', json_encode([
            'tokens' => [
                'colors' => [
                    'brand' => '#1e3a8a',
                    'surface' => '#f8fafc',
                ],
                'fontSizes' => [
                    '2xl' => 20.0,
                ],
                'space' => [
                    '4' => 12.0,
                ],
            ],
            'styles' => [
                'brand' => [
                    'color' => '{colors.brand}',
                    'fontSize' => '{fontSizes.2xl}',
                ],
            ],
        ]));

        $template = <<<FOLIO
@use "partial.folio"
@theme "modern"

@style {
  .hero {
    background: #f8fafc;
    padding: 20;
    color: #1e3a8a;
  }
}

page {
  column {
    text(class="brand") "Hello"
    heading(class="hero") "Title"
  }
}
FOLIO;

        file_put_contents($dir . '/main.folio', $template);

        $engine = new TemplateEngine();
        $pdf = $engine->renderFile($dir . '/main.folio');

        self::assertStringStartsWith('%PDF-1.7', $pdf);
    }
}
