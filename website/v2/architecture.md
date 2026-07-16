# Architecture

Folio 2.0 is built around an onion / hexagonal architecture. The idea is simple:
**dependencies always point inward**. The domain (documents, layout, styles) knows
nothing about PDF bytes, files, HTTP, or the CLI. All of those concerns live at
the edges as replaceable adapters.

## Overview

A request to render a template travels through these layers:

```
Template ──► PhpTemplateCompiler ──► Node tree
                                          │
                                          ▼
                              LayoutEngine + StyleEngine
                                          │
                                          ▼
                              Paginator (optional)
                                          │
                                          ▼
                              Pdf1_7Renderer
```

The same pipeline is used whether the document originates from a `.folio`
template or from the PHP builder API.

## Layers

| Layer | Responsibility | Key classes |
|-------|----------------|-------------|
| **Domain** | Value objects, layout rules, pagination, style resolution | `Node`, `Style`, `ComputedStyle`, `LayoutBox`, `TextWrapResult` |
| **Application** | Orchestrate template compilation, layout and rendering | `TemplateEngine`, `FolioCli` |
| **Ports** | Interfaces the domain depends on | `RendererPort`, `FontMetricsPort`, `CachePort` |
| **Infrastructure** | Concrete adapters for PDF output, font metrics, file cache | `Pdf1_7Renderer`, `Core14FontMetrics` |

## Domain layer

### Node tree

Everything in a Folio document is a `Node`. There are no side effects and no
mutable state:

- `Page` — a single page with a size and content.
- `Column` / `Row` — flex containers.
- `Text` / `Heading` — leaf text nodes.
- `Table` / `TableRow` / `TableCell` — table structures.
- `Component` / `Partial` — reusable template fragments.

Each node exposes `style()` and `children()`. A node is built once and then
passed down the pipeline.

### StyleEngine

`PandaStyleEngine` resolves a `Style` (author-level declarations) and a parent
`ComputedStyle` into a final `ComputedStyle`. It is a pure function: given the
same node and context it always produces the same result.

`ComputedStyle` is split into sub-objects:

- `BoxStyle` — padding, margins, borders, background, width, height.
- `TextStyle` — font, size, weight, color, alignment, line height.
- `LayoutStyle` — display, grow, shrink, gap.
- `PaintStyle` — fill, opacity.

### LayoutEngine

`LayoutEngine` turns the `Node` tree into a `LayoutTree` of `LayoutBox`
objects. Each box has a position, size, resolved style and a reference to the
source node.

The engine supports:

- Flex columns and rows with `gap` and `grow`.
- Percentage widths.
- Text wrapping using real Core14 font metrics.
- Tables with `colspan`, headers and alternating row backgrounds.
- Page breaks based on available page height.

### Pagination

`Paginator` walks the `LayoutTree` and splits overflowing content across pages.
It can break wrapped paragraphs between lines and table rows between pages.

## Ports

Ports are tiny interfaces. The domain depends only on these abstractions.

```php
interface RendererPort
{
    public function render(Document $document, LayoutResult $layout): string;
}

interface FontMetricsPort
{
    public function measure(TextRun $run): TextMetrics;
    public function lineHeight(Font $font, float $size): float;
}
```

This makes the core testable. For example, unit tests can use a fake
`RendererPort` that returns the layout metadata instead of PDF bytes.

## Infrastructure adapters

### Pdf1_7Renderer

The concrete `RendererPort` writes PDF 1.7 (ISO 32000-1) output. It:

- emits object streams,
- compresses content streams with `zlib`,
- supports multiple Core14 fonts (Helvetica, Helvetica-Bold, Times-Roman,
  Courier),
- draws text, rectangles and clipped content.

### Core14FontMetrics

Built-in metrics for the 14 standard PDF fonts. It returns glyph widths and
line heights so text wrapping is accurate without external font files.

## Why this matters

Because the PDF writer is an adapter, you can:

- swap in a different renderer (e.g. SVG or PNG) without touching layout,
- test layout and pagination without generating real PDFs,
- embed custom font metrics later by implementing `FontMetricsPort`,
- keep the core fast and free of heavy dependencies.
