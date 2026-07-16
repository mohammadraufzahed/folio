# Folio 2.0

Folio 2.0 is a ground-up redesign of the Folio PDF engine. It keeps the
zero-dependency, pure-PHP philosophy while adopting an onion/hexagonal
architecture, a real layout tree, and a modern template language.

## What is new

- **Onion / hexagonal architecture** — the domain (documents, layout,
  pagination, styling) is isolated from PDF bytes, file systems and network
  calls through explicit ports and adapters.
- **Immutable document model** — pages, columns, rows, text, headings and tables
  are value objects that are safe to compose and refactor.
- **StyleEngine** — styles are resolved before layout into a typed
  `ComputedStyle` so the output is predictable.
- **Layout tree** — the engine measures and positions text, rows, columns and
  tables directly, no HTML or browser required.
- **Pagination** — wrapped text and table rows can break across pages.
- **Pdf1_7Renderer** — emits PDF 1.7 object streams with compression and
  multiple Core14 fonts.
- **Template language 2.0** — props, `@use` partials, string interpolation,
  `if`/`else` and `foreach` loops.
- **CLI** — `folio render`, `folio compile`, `folio serve` and
  `folio cache:clear`.
- **Benchmarks & tests** — PHPUnit regression tests, PHPStan, PHP-CS-Fixer
  and a pure-PHP benchmark harness.

## What is in this preview

Folio 2.0.0 is the first stable v2 release. The following features are fully
working and tested:

- All v2 example PDFs: invoice, certificate, shipping label, resume, receipt,
  company report and several table demonstrations.
- `.folio` template compilation to PHP.
- `page`, `column`, `row`, `text`, `heading`, `table`, `header`, `tr`, `td`,
  `th` elements.
- `prop` declarations, `@use` partial inlining, `{var}` and `{obj.prop}`
  string interpolation.
- `if` / `else` and `foreach ... as` control flow.
- Percentage widths, flex `grow`, `gap`, `padding`, `align` and `fontWeight`.
- Multi-level tables with `colspan`.
- Multi-page PHP builder documents with automatic pagination.

## What is planned for later 2.x releases

The v2 proposal includes additional features that are not yet wired into the
engine:

- `@theme` design-token loading and `@style` scoped style blocks.
- PandaCSS-style recipes, slot recipes and text/layer styles.
- Components with typed props and named slots.
- Images, SVG and embedded TrueType/OpenType fonts.
- `pageHeader()` / `pageFooter()` page chrome helpers.
- PDF/A output and digital signatures.
- VS Code extension language server features beyond formatting.

## Quick start

Install the library:

```bash
composer require mohammadraufzahed/folio
```

Create `invoice.folio`:

```folio
prop customer = ""
prop total = ""

page(background="#ffffff") {
    column(padding=48, gap=24) {
        heading(fontSize=24) "Invoice"
        text "Customer: {customer}"
        text "Total: {total}"
    }
}
```

Render it from PHP:

```php
<?php
use Folio\Pdf\Template\TemplateEngine;

$engine = new TemplateEngine();
$pdf = $engine
    ->enableFolio2Syntax(__DIR__)
    ->renderFile('invoice.folio', [
        'customer' => 'Acme Inc.',
        'total' => '$1,250.00',
    ]);

file_put_contents('invoice.pdf', $pdf);
```

## Migration from v1

Folio 2.0 is a major version and is **not** backward compatible with v1. The old
`PdfFileWriter` and `Document::generate()` path has been removed. See the
[migration guide](./migration.md) for a full mapping.

## Learn more

- [Getting Started](./getting-started.md)
- [Template language reference](./template-language.md)
- [Styling guide](./styling.md)
- [Examples](./examples.md)
- [CLI tools](./cli.md)
- [Architecture](./architecture.md)
- [Benchmarks](./benchmarks.md)
- [Contributing](./contributing.md)
- [Migration from v1](./migration.md)
