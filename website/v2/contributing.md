# Contributing to Folio 2.0

Folio 2.0 is a pure-PHP library with TypeScript tooling for the VS Code
extension and VitePress documentation. This guide explains how to set up the
project, run checks, and make changes.

## Requirements

- PHP 8.3 or newer
- Composer 2.x
- Node.js 20+ (only for the website and VS Code extension)

## Install dependencies

```bash
composer install
npm --prefix website install
```

## Project layout

| Path | Purpose |
|------|---------|
| `src/` | Core PHP: nodes, layout engine, template compiler, renderer, style engine, LSP |
| `tests/` | PHPUnit test suite |
| `benchmarks/` | Pure-PHP benchmark harness |
| `examples/` | Runnable `.folio` templates and PHP scripts that generate PDFs |
| `bin/` | `folio` CLI and formatter entry points |
| `website/` | VitePress documentation site |
| `vscode-extension/` | TypeScript VS Code extension |
| `tree-sitter-folio-pdf/` | Tree-sitter grammar for syntax highlighting |

## Quality checks

Run the full quality suite before committing:

```bash
composer test      # PHPUnit unit and golden regression tests
composer analyze   # PHPStan 2.x static analysis
composer cs-check  # PHP-CS-Fixer style check (dry-run)
```

To apply style fixes automatically:

```bash
composer cs-fix
```

## Running examples

Each example is a standalone PHP script:

```bash
php examples/invoice.php
php examples/company-report.php
php examples/tables.php
```

You can also use the CLI:

```bash
php bin/folio.php render --template=examples/templates/invoice.folio --output=/tmp/invoice.pdf
php bin/folio.php compile --template=examples/templates/invoice.folio --output=/tmp/invoice.php
```

## Benchmarks

The benchmark suite is pure PHP and writes results to `benchmarks.json`:

```bash
composer benchmark
```

Scenarios:

- `micro` — minimal document with one text node.
- `document` — 50-section document with headings and paragraphs.
- `stress` — 1,000-row table to exercise layout and rendering.

## Documentation

Build the VitePress site locally:

```bash
npm --prefix website run build
npm --prefix website run preview
```

v2 documentation lives in `website/v2/`. The older v1 guide remains in
`website/guide/`, `website/api/`, `website/template-language/` and
`website/tooling/` and is linked from the `v1.x` nav dropdown.

## Architecture at a glance

Folio 2.0 follows an onion / hexagonal architecture:

1. **Domain** — immutable `Node` tree, `Style`, `ComputedStyle`, layout and
   pagination value objects.
2. **Ports** — narrow interfaces such as `RendererPort`, `FontMetricsPort` and
   `CachePort`.
3. **Adapters** — concrete implementations like `Pdf1_7Renderer` and
   `Core14FontMetrics`.

This keeps the PDF generation logic separate from file I/O, HTTP, or UI
concerns, making the core easy to test.

## Reporting issues

Please open an issue on GitHub with a minimal reproducible example, including:

- PHP version
- Folio version
- The smallest `.folio` template or PHP builder script that triggers the bug
- The expected output and the actual output
