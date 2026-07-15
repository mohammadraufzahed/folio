# Testing

Folio uses PHPUnit for tests and PHPStan for static analysis. Run both before opening a pull request.

## Running Tests

Run the full suite:

```bash
./vendor/bin/phpunit
```

Run a specific test file:

```bash
./vendor/bin/phpunit tests/Styling/ColorTest.php
```

Run a specific test method:

```bash
./vendor/bin/phpunit --filter testRgb
```

Generate a coverage report:

```bash
./vendor/bin/phpunit --coverage-html coverage
```

## Static Analysis

Run PHPStan with the project's configuration:

```bash
./vendor/bin/phpstan analyse --no-progress
```

The project uses level 5. Higher levels are welcome in focused pull requests.

## Code Style

Check the code style without modifying files:

```bash
composer cs-check
```

Apply fixes automatically:

```bash
composer cs-fix
```

## Writing Tests

Add unit tests next to the code they cover, under `tests/`. Name test classes `*Test` and extend `PHPUnit\Framework\TestCase`.

```php
<?php

namespace Folio\Pdf\Tests\Styling;

use PHPUnit\Framework\TestCase;
use Folio\Pdf\Styling\Color;

final class ColorTest extends TestCase
{
    public function testHex(): void
    {
        $color = Color::hex('#333333');

        $this->assertEqualsWithDelta(0.2, $color->red(), 0.01);
        $this->assertEqualsWithDelta(0.2, $color->green(), 0.01);
        $this->assertEqualsWithDelta(0.2, $color->blue(), 0.01);
    }
}
```

## Integration Tests

For end-to-end PDF generation, generate a file and assert its existence and structure:

```php
<?php

namespace Folio\Pdf\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Folio\Pdf\Document\Pdf;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Text;

final class PdfGenerationTest extends TestCase
{
    public function testGeneratesValidPdfHeader(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'folio-') . '.pdf';

        Pdf::make()
            ->page(Page::a4()->withContent(
                Column::make(null, [
                    Heading::h1('Test'),
                    Text::make('Body'),
                ])
            ))
            ->save($path);

        $this->assertFileExists($path);
        $this->assertStringStartsWith('%PDF-1.7', file_get_contents($path));
    }
}
```

## CI

The GitHub Actions workflow runs `composer test`, `composer analyze`, and `composer cs-check` against PHP 8.3 and 8.4.
