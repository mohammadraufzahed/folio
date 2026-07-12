# Folio PDF

A modern, production-quality PDF generation library for PHP 8.3+.

> Flutter / SwiftUI / Jetpack Compose for PDFs.

## Installation

```bash
composer require folio/pdf
```

## Quick Start

```php
use Folio\Pdf\Document\Pdf;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Text;

Pdf::make()
    ->page(
        Page::a4()->withContent(
            Column::make()
                ->addChildren([
                    Heading::h1('Invoice'),
                    Text::make('Customer: John Doe'),
                    Text::make('Amount: $100.00'),
                ])
        )
    )
    ->save('invoice.pdf');
```

## Documentation

Full documentation is available at [docs.folio-pdf.dev](https://docs.folio-pdf.dev) or in the `website/` directory.

- [Getting Started](website/guide/getting-started.md)
- [API Reference](website/api/pdf.md)
- [Template Language](website/template-language/overview.md)
- [Tooling](website/tooling/formatter.md)

## License

MIT
