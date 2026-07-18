---
layout: home

hero:
  name: Folio 2.0
  text: PDF generation for PHP, engineered.
  tagline: No HTML-to-PDF wrappers. No runtime dependencies. Just an immutable document model, a real layout engine, and a template language that compiles to native PHP.
  actions:
    - theme: brand
      text: Get Started
      link: /v2/getting-started
    - theme: alt
      text: View on GitHub
      link: https://github.com/mohammadraufzahed/folio

features:
  - title: Native PHP 8.3+
    details: Built with strict types, zero runtime dependencies, and no external binaries to manage.
  - title: Immutable AST
    details: Documents are value objects. Compose pages, columns, rows, and tables with a fluent API that is safe to refactor.
  - title: Real layout engine
    details: Measure, position, wrap text, paginate and render directly — no browser engines or temporary HTML files.
  - title: Template language
    details: Write declarative .folio templates with props, partials, interpolation, if/else and foreach.
  - title: Strongly typed styles
    details: Colors, lengths, borders, font weights and flex layout are first-class values, not loose strings.
  - title: Developer tooling
    details: A TypeScript language server, VS Code extension, CLI, formatter and benchmarks keep your workflow sharp.
---

## What is Folio 2.0?

Folio 2.0 is a ground-up redesign. It keeps the zero-dependency, pure-PHP
philosophy while adopting an onion/hexagonal architecture, a real layout tree,
and a modern template language.

## Quick start

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

Render it:

```php
<?php
use Folio\Pdf\Template\TemplateEngine;

$engine = new TemplateEngine();
$pdf = $engine->renderFile('invoice.folio', [
    'customer' => 'Acme Inc.',
    'total' => '$1,250.00',
]);

file_put_contents('invoice.pdf', $pdf);
```

## Documentation

- [Folio 2.0 docs](/v2/) — the current version
- [v1 archive](/guide/getting-started) — legacy documentation

## What's next?

- [Getting Started](/v2/getting-started)
- [Template Language](/v2/template-language)
- [Examples](/v2/examples)
- [CLI](/v2/cli)
- [Contributing](/v2/contributing)
