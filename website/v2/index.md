# Folio 2.0

Folio 2.0 is a ground-up redesign of the Folio PDF engine. It keeps the
zero-dependency, pure-PHP philosophy while adopting an onion/hexagonal
architecture and a much richer developer experience.

## What is new

- **Onion / hexagonal architecture** — domain, application and infrastructure
  layers with explicit ports and adapters.
- **StyleEngine** — PandaCSS-inspired design tokens, recipes, slot recipes,
  text/layer styles, cascade layers and conditions.
- **Layout tree** — constraint-based layout, gap/grow/align, real tables, and
  pagination on the layout tree.
- **Pdf1_7Renderer** — object streams, compression, multiple fonts and rich
  content streams.
- **Template language 2.0** — typed props, `@use`, `@theme`, string
  interpolation and components.
- **CLI and dev server** — `folio render`, `folio compile`, `folio serve` and
  `folio cache:clear`.
- **Benchmarks & golden tests** — automated performance and regression
  testing.

## Migration from v1

Folio 2.0 is a major version. The `Pdf` builder and `Document` API are still
available, but new code should prefer the v2 `TemplateEngine` and the
`LayoutEngine` → `Paginator` → `Pdf1_7Renderer` pipeline.

```php
use Folio\Pdf\Template\TemplateEngine;

$pdf = (new TemplateEngine())
    ->enableFolio2Syntax()
    ->renderFile('invoice.folio', $data);
```
