# Architecture

Folio is split into six layers. Each layer has a single responsibility, and data flows from your application code to a PDF file in one direction.

## Document Pipeline

```
Application
    ↓
PHP Builder / .folio Template
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

## 1. Document AST

The document model is an immutable tree of nodes. Every node implements the `Node` contract and exposes a `Style` and zero or more children.

- `Page` — a physical page with a size and content node.
- `Column` — vertical container.
- `Row` — horizontal container.
- `Text` — text content.
- `Heading` — heading levels 1 through 6.
- `Table`, `TableRow`, `TableCell` — tabular data.

Because the tree is immutable, you can build documents as pure functions, cache intermediate states, and test layouts without side effects.

## 2. Builder API

`Folio\Pdf\Document\Pdf` is the fluent entry point. `Page::a4()->withContent(...)` and `Column::make(...)` create nodes. The builder never mutates state; it returns new `Pdf` and `Node` instances.

## 3. Template Compiler

`.folio` files are tokenized by the `Lexer`, parsed into an AST by the `Parser`, and compiled to a PHP closure by the `PhpTemplateCompiler`. The generated closure accepts a data array and returns a `Pdf` instance.

This keeps templates separate from business logic while still producing native PHP code with no runtime template interpreter.

## 4. Layout Engine

The layout engine walks the AST and computes sizes and positions. It handles:

- Available width and height constraints
- Padding and margins
- Row and column flex behavior
- Table cell sizing and spanning
- Font metrics for text

The result is a `LayoutBox` tree that the paint engine can render.

## 5. Pagination Engine

When a node exceeds the available page height, the pagination engine splits it into multiple pages. It respects header and footer blocks and carries the parent container's style across pages.

## 6. Paint Engine & PDF Writer

The paint engine converts `LayoutBox` objects into PDF drawing instructions. The `PdfFileWriter` then emits a PDF 1.7 file with the correct object structure, cross-reference table, and trailer.

No browser, no DOM, no external renderer. What you describe is what gets drawn.
