# Migrating from Folio v1

Folio 2.0 is a breaking major release. The previous low-level `PdfFileWriter`
and `Document::generate()` path has been removed and replaced with a clean
pipeline:

```
.folio template  →  TemplateEngine  →  LayoutEngine  →  Paginator  →  Pdf1_7Renderer
     or PHP builder
```

## Removed APIs

| v1 API | v2 replacement |
|--------|----------------|
| `Folio\Pdf\Document` with `generate()` | `Folio\Pdf\Document\Pdf` through `LayoutEngine`/`Pdf1_7Renderer`, or `TemplateEngine` |
| `PdfFileWriter` | `Pdf1_7Renderer` |
| Inline `pageHeader()`/`pageFooter()` arrays | Define headers/footers as regular page content for now |

## Template-first workflow

The recommended v2 workflow is a `.folio` template plus the
`TemplateEngine`:

```php
<?php
use Folio\Pdf\Template\TemplateEngine;

$engine = new TemplateEngine();
$pdf = $engine->renderFile('invoice.folio', [
    'customerName' => 'Alice',
    'total' => '$500.00',
]);

file_put_contents('invoice.pdf', $pdf);
```

## Builder workflow

If you prefer PHP, build a `Node` tree and ask `Pdf` to render it:

```php
<?php
use Folio\Pdf\Document\Pdf;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Text;
use Folio\Pdf\Styling\Style;

$pdf = Pdf::make()->page(
    Page::a4()->withContent(
        Column::make(
            Style::make()->padding(48.0)->gap(12.0),
            [Text::make('Hello, Folio 2.0!')]
        )
    )
);

file_put_contents('hello.pdf', $pdf->toString());
```

## Styling differences

- Colors are always 6-digit hex values (`#0f172a`).
- Font weights are `bold` or `normal`.
- Sizes are in points.
- Percentage widths (`width="100%"`) are supported on flex containers.

## Features not yet restored

Some advanced v1 features and proposal items are not in the initial 2.0
release:

- Components with named slots
- Images and SVG
- Embedded custom fonts
- `pageHeader()`/`pageFooter()` chrome helpers
- PDF/A output

These will arrive in subsequent 2.x releases.
