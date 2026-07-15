# Getting Started

Folio is a PDF generation library for PHP 8.3+. It treats PDFs as structured documents rather than rendered web pages, giving you predictable, repeatable output without the fragility of HTML-to-PDF pipelines.

## Why Folio?

Most PHP PDF tools fall into one of two camps: low-level PDF writers that force you to manage every byte, or HTML-to-PDF converters that depend on headless browsers and fragile CSS. Folio sits in the middle.

You describe the document — pages, columns, rows, text, tables, and styles — and Folio measures, lays out, paginates, and renders it. The result is a clean PDF file with no browser, no DOM, and no external runtime dependencies.

## What You Get

- **Immutable document AST** — every node is a value object. Refactor without side effects.
- **Fluent PHP builder API** — compose documents with readable, chained method calls.
- **Declarative template language** — separate design from data with `.folio` templates.
- **Built-in layout engine** — Folio calculates sizes and positions itself.
- **Strongly typed styling** — colors, lengths, borders, and font weights are typed, not loose strings.
- **Pure PHP** — `composer require` and you are done.

## When to Use Folio

Folio fits projects that need reliable, template-driven PDFs:

- Invoices, reports, and statements
- Certificates and labels
- Multi-page legal or compliance documents
- Batch document generation where consistency matters more than pixel-perfect web fidelity

If you need a pixel-perfect reproduction of a complex web page, an HTML-to-PDF tool may still be the right choice. Folio is for teams that want a document model they can reason about, version, and test.

## Next Steps

- [Installation](./installation.md)
- [Quick Start](./quick-start.md)
- [Architecture](./architecture.md)
