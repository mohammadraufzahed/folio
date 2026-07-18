# Getting Started with Folio 2.0

Folio 2.0 is a pure-PHP PDF generation system. You describe documents as
structured data — pages, columns, rows, text, headings and tables — and Folio
measures, lays out, paginates and renders them into a PDF file.

## Installation

Folio 2.0 requires PHP 8.3 or newer and has no runtime dependencies beyond
Composer:

```bash
composer require mohammadraufzahed/folio
```

If you want to run the test suite, install the development dependencies as
well:

```bash
composer install --dev
```

## Your first template

Create a file called `invoice.folio`:

```folio
prop company = "Acme Inc."
prop amount = ""

page(background="#ffffff") {
    column(width="100%", padding=48, gap=24) {
        heading(color="#0f172a", fontSize=24) "Invoice"
        text "From: {company}"
        text "Total: {amount}"
    }
}
```

This template declares two **props** (`company` and `amount`) and renders a
single page with a heading and two paragraphs.

## Render from PHP

Use the `TemplateEngine` to compile and render the template:

```php
<?php
require 'vendor/autoload.php';

use Folio\Pdf\Template\TemplateEngine;

$engine = new TemplateEngine();
$pdf = $engine->renderFile('invoice.folio', [
    'company' => 'Acme Inc.',
    'amount'  => '$1,250.00',
]);

file_put_contents('invoice.pdf', $pdf);
```

The `TemplateEngine` compiles the `.folio` template into PHP, which supports
`@use`, `@theme`, `@style`, `prop`, string interpolation, `if` and `foreach`.

## Your first PHP-only document

You can also build documents directly in PHP without a template:

```php
<?php
require 'vendor/autoload.php';

use Folio\Pdf\Document\Pdf;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Text;
use Folio\Pdf\Styling\Style;

$pdf = Pdf::make()->page(
    Page::a4()->withContent(
        Column::make(
            Style::make()->padding(48.0)->gap(24.0)->width('100%'),
            [
                Text::make('Hello, Folio!'),
            ]
        )
    )
);

file_put_contents('hello.pdf', $pdf->toString());
```

Both paths produce the same PDF bytes through the `LayoutEngine` and
`Pdf1_7Renderer`.

## Next steps

- [Template language reference](./template-language.md)
- [Styling guide](./styling.md)
- [CLI tools](./cli.md)
- [Examples](./examples.md)
- [Architecture overview](./architecture.md)
