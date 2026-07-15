# Folio PDF

A modern, production-quality PDF generation library for PHP 8.3+.

> Flutter / SwiftUI / Jetpack Compose for PDFs.

Folio PDF implements its own document model, layout engine, templating engine, and PDF renderer - no wrapping of HTML-to-PDF solutions. It provides a composable, immutable design with a fluent builder API and a custom template language for declarative PDF generation.

## Features

- **PHP 8.3+** with strict types everywhere
- **Zero Runtime Dependencies** - pure PHP implementation
- **Composable Document Model** - immutable nodes with fluent builder API
- **Custom Template Language** - declarative syntax with data binding
- **Layout Engine** - flex and grid layouts with automatic pagination
- **Comprehensive Styling** - colors, fonts, borders, shadows, spacing
- **Table Support** - simple, nested, multi-header, and multi-level tables
- **Page Chrome** - themed headers and footers with page numbers
- **Developer Tooling** - LSP, VS Code extension, formatter, tree-sitter grammar

## Installation

Install via Composer:

```bash
composer require folio/pdf
```

### Requirements

- PHP 8.3 or higher
- Composer for dependency management
- No external dependencies or extensions required

## Quick Start

### PHP Builder API

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

### Template Language

```folio
var title = "Invoice"
var customer = "John Doe"

page {
  heading title
  text "Customer: " customer
  text "Amount: $100.00"
}
```

```php
use Folio\Pdf\Template\PhpTemplateCompiler;

$compiler = new PhpTemplateCompiler();
$pdf = $compiler->renderFile('invoice.folio', [
    'title' => 'Invoice',
    'customer' => 'John Doe',
]);
$pdf->save('invoice.pdf');
```

## Documentation

Full documentation is available at [mohammadraufzahed.github.io/folio](https://mohammadraufzahed.github.io/folio)

- [Getting Started](https://mohammadraufzahed.github.io/folio/guide/getting-started) - Installation and basic usage
- [API Reference](https://mohammadraufzahed.github.io/folio/api/pdf) - Complete API documentation
- [Template Language](https://mohammadraufzahed.github.io/folio/template-language/overview) - Template syntax and features
- [Examples](https://mohammadraufzahed.github.io/folio/examples) - Real-world usage examples
- [Tooling](https://mohammadraufzahed.github.io/folio/tooling/formatter) - LSP, formatter, and VS Code extension

## Architecture

Folio PDF is built with a clean, modular architecture:

- **Document Model** - Immutable AST nodes (Page, Column, Row, Text, Table, etc.)
- **Layout Engine** - Flex and grid layout calculations with automatic pagination
- **Template Compiler** - Lexer, parser, and PHP code generator for templates
- **Styling System** - Comprehensive style properties with type safety
- **PDF Writer** - Direct PDF generation without external dependencies

## Key Concepts

### Immutable Design

All document nodes are immutable. Methods like `withStyle()` return new instances:

```php
$page = Page::a4();
$styledPage = $page->withContent($content); // Returns new instance
```

### Composable Layouts

Build complex layouts from simple components:

```php
Column::make()
    ->addChildren([
        Heading::h2('Section 1'),
        Text::make('Content here'),
        Row::make()->addChildren([
            Text::make('Left'),
            Text::make('Right'),
        ]),
    ])
```

### Styling

Apply styles with a fluent API:

```php
Style::make()
    ->withPadding(20.0)
    ->withColor(Color::hex('#333333'))
    ->withFontSize(14.0)
    ->withFontWeight(FontWeight::Bold)
```

## Examples

See the `examples/` directory for complete working examples:

- `hello-world.php` - Basic PDF generation
- `invoice.php` - Invoice with tables and styling
- `company-report.php` - Multi-page document with headers/footers
- `tables.php` - Various table configurations

## Development

### Running Tests

```bash
composer test
```

### Static Analysis

```bash
composer analyze
```

### Code Style

Follow PSR-12 coding standards. Use strict types and type hints throughout.

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

- Report bugs via [GitHub Issues](https://github.com/mohammadraufzahed/folio/issues)
- Submit pull requests for improvements
- Discuss features in [GitHub Discussions](https://github.com/mohammadraufzahed/folio/discussions)

## Security

For security vulnerabilities, please email security@folio-pdf.dev instead of using public issues. See [SECURITY.md](SECURITY.md) for details.

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Acknowledgments

- Inspired by Flutter, SwiftUI, and Jetpack Compose design patterns
- Built with modern PHP 8.3+ features
- Community-driven open-source project

## Links

- [Documentation](https://mohammadraufzahed.github.io/folio)
- [GitHub Repository](https://github.com/mohammadraufzahed/folio)
- [Packagist](https://packagist.org/packages/folio/pdf)
- [Report Issues](https://github.com/mohammadraufzahed/folio/issues)
