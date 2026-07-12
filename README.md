# Folio PDF

A modern, production-quality PDF generation library for PHP 8.3+.

## Philosophy

> Flutter / SwiftUI / Jetpack Compose for PDFs.

Folio PDF implements its own document model, layout engine, templating engine, and PDF renderer - no wrapping of HTML-to-PDF solutions.

## Features

- **PHP 8.3+** with strict types everywhere
- **Pure PHP** - zero runtime dependencies
- **PSR-4** autoloading
- **Immutable** document AST
- **Fluent** builder API
- **Composable** components
- **Strongly typed** styling system
- **Custom** template language (coming soon)
- **Tree-sitter** grammar (coming soon)
- **Language Server** (coming soon)

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
use Folio\Pdf\Styling\Color;
use Folio\Pdf\Styling\Style;

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

## Architecture

```
Application
    ↓
Fluent Builder
    ↓
Document AST
    ↓
Layout Engine
    ↓
Pagination Engine
    ↓
Paint Engine
    ↓
PDF Writer
    ↓
PDF File
```

## Styling System

Inspired by Flutter, with strongly typed properties:

```php
Style::make()
    ->withPadding(20.0)
    ->withMargin(10.0)
    ->withColor(Color::hex('#333333'))
    ->withFontSize(14.0)
    ->withFontWeight(FontWeight::Bold)
    ->withAlignment(Alignment::Center);
```

## Document Nodes

- `Page` - Document pages (A4, Letter, A3, custom)
- `Column` - Vertical container
- `Row` - Horizontal container
- `Text` - Text content
- `Heading` - Headings (H1-H6)
- More coming soon...

## License

MIT
