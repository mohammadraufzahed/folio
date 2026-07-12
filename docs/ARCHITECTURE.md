# Architecture

This document describes the architecture of Folio PDF, including design decisions, component relationships, and extension points.

## Overview

Folio PDF is a modern PDF generation library for PHP 8.3+ that implements its own document model, layout engine, template compiler, and PDF writer. The architecture follows a composable, immutable design inspired by Flutter, SwiftUI, and Jetpack Compose.

## Core Principles

1. **Immutability**: All document nodes are immutable. Modifications return new instances.
2. **Composition**: Complex documents are built from simple, composable components.
3. **Type Safety**: Strict types throughout with comprehensive PHPDoc.
4. **Zero Dependencies**: Pure PHP implementation with no runtime dependencies.
5. **Separation of Concerns**: Clear boundaries between document model, layout, styling, and rendering.

## Directory Structure

```
src/
├── Contracts/          # Interfaces defining core abstractions
├── Document/           # Document builder and PDF generation
├── Layout/             # Layout engine and calculations
├── Nodes/              # Document node types
├── Pagination/         # Automatic page breaking
├── Pdf/                # PDF writer implementation
├── Styling/            # Style system and properties
├── Support/            # Utility traits and helpers
├── Template/           # Template language compiler
└── Lsp/                # Language Server Protocol implementation
```

## Core Components

### Document Model (`src/Nodes/`)

The document model represents PDF content as an immutable AST (Abstract Syntax Tree).

**Base Classes:**
- `AbstractNode`: Base class for all nodes with immutable pattern
- `Node`: Interface defining the contract for all nodes

**Node Types:**
- `Page`: Represents a PDF page with size and content
- `Column`: Vertical layout container
- `Row`: Horizontal layout container
- `Text`: Text content
- `Heading`: Headings with levels (h1-h6)
- `Table`: Tables with rows and cells
- `TableRow`: Table row
- `TableCell`: Table cell

**Design Pattern:**
```php
final class Text implements Node
{
    public function __construct(
        private readonly string $text,
        private readonly ?Style $style = null
    ) {}

    public function withStyle(?Style $style): self
    {
        return new self($this->text, $style);
    }
}
```

### Layout Engine (`src/Layout/`)

The layout engine calculates positions and sizes for document nodes.

**Key Classes:**
- `LayoutEngine`: Main orchestrator for layout calculations
- `LayoutBox`: Represents a node's calculated position and size
- `LayoutContext`: Provides available space and constraints
- `FlexLayout`: Flex-based layout calculations
- `GridLayout`: Grid-based layout calculations
- `Point` and `Size`: Value objects for geometry

**Layout Process:**
1. Create layout context with available width/height
2. Recursively layout nodes from root to leaves
3. Calculate positions and sizes based on constraints
4. Return layout result with bounding boxes

### Styling System (`src/Styling/`)

The styling system provides comprehensive styling capabilities with type safety.

**Key Classes:**
- `Style`: Immutable style object with fluent API
- `Color`: Color utilities (hex, RGB, named colors)
- `FontWeight`: Strongly typed font weights
- `Alignment`: Text alignment options
- `Border`: Border styling
- `Shadow`: Box shadows
- `Length`: Dimension units

**Style Properties:**
- Spacing: padding, margin
- Typography: font, fontSize, fontWeight, lineHeight, letterSpacing
- Colors: color, background
- Effects: opacity, rotation, scale, shadow
- Layout: width, height, minWidth, maxWidth, alignment, flex

### Template Compiler (`src/Template/`)

The template compiler converts the Folio template language to PHP code.

**Pipeline:**
1. **Lexer** (`Lexer.php`): Tokenizes template string into tokens
2. **Parser** (`Parser.php`): Builds AST from tokens using recursive descent
3. **Compiler** (`PhpTemplateCompiler.php`): Generates PHP code from AST
4. **Runtime** (`Runtime.php`): Executes compiled templates with data

**Template Features:**
- Variables and declarations
- Property access and expressions
- Conditionals (if/else/elseif)
- Loops (foreach with empty clause)
- Partials and includes
- Custom attributes

**Caching:**
- Disk cache based on file modification time
- In-memory cache for compiled renderers
- Cache invalidation on template changes

### PDF Writer (`src/Pdf/`)

The PDF writer generates PDF files directly without external libraries.

**Key Classes:**
- `PdfFileWriter`: Implements PDF generation from scratch
- `PdfWriter`: Interface for PDF writing abstraction

**PDF Generation:**
1. Create PDF objects (pages, fonts, content streams)
2. Generate cross-reference table
3. Write document catalog and trailer
4. Output complete PDF 1.7 document

### Document Builder (`src/Document/`)

The document builder provides a fluent API for creating PDFs.

**Key Classes:**
- `Pdf`: Main builder class with fluent API
- `Document`: Internal document representation
- `PdfWriter`: Interface for PDF output

**Builder Pattern:**
```php
Pdf::make()
    ->pageHeader(['title' => 'Report'])
    ->pageFooter(['showPageNumber' => true])
    ->page(Page::a4()->withContent($content))
    ->save('output.pdf');
```

### Pagination Engine (`src/Pagination/`)

The pagination engine handles automatic page breaking.

**Key Classes:**
- `PaginationEngine`: Orchestrates content splitting across pages

**Pagination Strategy:**
1. Measure content height
2. Split content that exceeds page height
3. Preserve node structure when possible
4. Handle headers, footers, and page numbers

## Contracts (`src/Contracts/`)

Interfaces define the core abstractions:

- `Node`: Contract for document nodes
- `Layoutable`: Contract for layout-capable objects
- `Renderable`: Contract for renderable objects
- `PdfWriter`: Contract for PDF generation
- `TemplateCompiler`: Contract for template compilation
- `FontLoader`: Contract for font loading
- `ImageLoader`: Contract for image loading

## Extension Points

### Custom Nodes

Create custom nodes by implementing the `Node` interface:

```php
final class CustomNode implements Node
{
    public function __construct(
        private readonly string $content,
        private readonly ?Style $style = null
    ) {}

    public function style(): ?Style { return $this->style; }
    public function children(): array { return []; }
    public function hasChildren(): bool { return false; }
    public function type(): string { return 'custom'; }
}
```

### Custom Layout

Extend the layout engine for custom layout algorithms:

```php
final class CustomLayout
{
    public function layout(Node $node, LayoutContext $context): LayoutBox
    {
        // Custom layout logic
    }
}
```

### Custom Styles

Extend the styling system with custom style properties:

```php
final class CustomStyle
{
    public function withCustomProperty(string $value): Style
    {
        return $this->style->withCustomProperty($value);
    }
}
```

### Template Functions

Add custom functions to the template compiler by extending the code generation.

## Data Flow

### PDF Generation Flow

```
User Code
    ↓
Pdf Builder (Document)
    ↓
Layout Engine
    ↓
Layout Boxes
    ↓
PDF Writer
    ↓
PDF File
```

### Template Compilation Flow

```
Template String
    ↓
Lexer → Tokens
    ↓
Parser → AST
    ↓
Compiler → PHP Code
    ↓
Cache (Disk/Memory)
    ↓
Renderer (Callable)
    ↓
PDF Document
```

## Performance Considerations

### Caching

- Template compilation is cached on disk and in memory
- Layout calculations are not cached (recomputed each time)
- Consider adding layout cache for static documents

### Memory

- Immutable pattern creates many intermediate objects
- Large documents may require significant memory
- Consider streaming for very large documents

### Optimization Opportunities

- Lazy layout calculation
- Incremental rendering
- Parallel template compilation
- Layout result caching

## Security Considerations

### Template Security

- Compiled templates run in isolated scope
- Strict mode catches undefined variables
- Validate template file paths to prevent directory traversal
- Set appropriate cache directory permissions

### Input Validation

- Validate all user input before passing to template compiler
- Sanitize template strings to prevent injection
- Use strict mode in production: `$compiler->setStrict(true)`

## Testing Strategy

### Unit Tests

- Test individual components in isolation
- Mock external dependencies
- Focus on business logic

### Integration Tests

- Test component interactions
- Test template compilation and rendering
- Test PDF generation end-to-end

### Test Coverage

Current coverage focuses on:
- Template compiler (lexer, parser, compiler)
- Node creation and manipulation
- Styling system
- Basic layout calculations

Areas for expanded coverage:
- Complex layout scenarios
- Pagination edge cases
- Error handling
- Performance benchmarks

## Future Enhancements

### Planned Features

- Keep-with-next and keep-together pagination controls
- Image support with custom loaders
- Custom font loading
- Advanced table features (merged cells, spanning)
- Multi-column layouts
- Watermarks and backgrounds
- Digital signatures
- PDF/A compliance

### Architectural Improvements

- Layout result caching
- Streaming PDF generation
- Plugin system for extensions
- Event hooks for customization
- Performance profiling tools

## Design Decisions

### Why Custom PDF Writer?

- Zero dependencies
- Full control over PDF generation
- No HTML-to-PDF limitations
- Optimized for document model

### Why Immutable Design?

- Predictable state management
- Easy to reason about
- Thread-safe by default
- Enables efficient caching

### Why Template Language?

- Declarative syntax
- Type-safe compilation
- Better error messages
- Performance through caching
- Separation of concerns

### Why PHP 8.3+?

- Modern language features
- Strict types
- Performance improvements
- Better error handling
- Future-proof design
