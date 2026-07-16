<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\Golden;

use Folio\Pdf\Template\TemplateEngine;
use PHPUnit\Framework\TestCase;

final class GoldenRegressionTest extends TestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function templateProvider(): array
    {
        $dir = __DIR__ . '/../../examples/templates';
        $files = glob($dir . '/*.folio');

        if ($files === false) {
            return [];
        }

        $cases = [];

        foreach ($files as $file) {
            $cases[basename($file)] = [$file];
        }

        return $cases;
    }

    /**
     * @dataProvider templateProvider
     */
    public function testTemplateRendersValidPdf(string $path): void
    {
        $engine = new TemplateEngine();
        $data = $this->sampleData();

        $pdf = $engine->renderFile($path, $data);

        self::assertStringStartsWith('%PDF-1.7', $pdf);
        self::assertGreaterThan(100, strlen($pdf));
    }

    /**
     * @return array<string, mixed>
     */
    private function sampleData(): array
    {
        return [
            'customerName' => 'Jane Doe',
            'customerEmail' => 'jane@example.com',
            'invoiceNumber' => 'INV-999',
            'invoiceDate' => '2024-06-15',
            'items' => [
                ['name' => 'Consulting', 'quantity' => 4, 'price' => 150.0],
                ['name' => 'Hosting', 'quantity' => 1, 'price' => 50.0],
            ],
            'name' => 'Folio',
        ];
    }
}
