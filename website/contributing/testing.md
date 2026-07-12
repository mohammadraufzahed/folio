# Testing Guide

## Running Tests

### Run All Tests

```bash
./vendor/bin/phpunit
```

### Run Specific Test File

```bash
./vendor/bin/phpunit tests/Styling/ColorTest.php
```

### Run Specific Test Method

```bash
./vendor/bin/phpunit --filter testRgb
```

### Run with Coverage

```bash
./vendor/bin/phpunit --coverage-html coverage
```

## Test Structure

```
tests/
├── Styling/
│   ├── ColorTest.php          # Color class tests
│   └── StyleTest.php          # Style class tests
├── Nodes/
│   └── PageTest.php           # Page node tests
└── Template/
    ├── LexerTest.php          # Template lexer tests
    └── CompilerTest.php       # Template compiler tests
```

## Writing Tests

### Unit Tests

```php
<?php

namespace Folio\Pdf\Tests\Styling;

use PHPUnit\Framework\TestCase;
use Folio\Pdf\Styling\Color;

class ColorTest extends TestCase
{
    public function testHex(): void
    {
        $color = Color::hex('#333333');
        $this->assertEquals(51, $color->red);
        $this->assertEquals(51, $color->green);
        $this->assertEquals(51, $color->blue);
    }
}
```

### Integration Tests

For integration tests that generate PDFs, use fixtures:

```php
<?php

namespace Folio\Pdf\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Folio\Pdf\Document\Pdf;

class PdfGenerationTest extends TestCase
{
    public function testSimplePdf(): void
    {
        $pdf = Pdf::make()
            ->page(/* ... */);
        
        $pdf->save($this->getTempPath('test.pdf'));
        
        $this->assertFileExists($this->getTempPath('test.pdf'));
    }
}
```

## Static Analysis

Run PHPStan for static analysis:

```bash
./vendor/bin/phpstan analyse src --level=8
```
