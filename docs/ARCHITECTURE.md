# Folio PDF Architecture

## Overview

Folio PDF is a modern PHP 8.3+ PDF generation library that implements its own document model, layout engine, templating engine, and PDF renderer. It follows a composable, immutable design inspired by Flutter/SwiftUI/Jetpack Compose.

## Architecture Pipeline

```
Application
    ↓
Fluent Builder
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

## Core Components

### 1. Document Model (AST)

The Document Model represents the PDF document as an immutable Abstract Syntax Tree (AST).

**Key Classes:**
- `AbstractNode` - Base class for all document nodes
- `Page` - Document pages with presets (A4, Letter, A3)
- `Column` - Vertical container
- `Row` - Horizontal container
- `Text` - Text content
- `Heading` - Headings (H1-H6)

**Features:**
- Immutable value objects
- Fluent builder pattern
- Strongly typed properties
- Composition over inheritance

### 2. Styling System

The styling system provides strongly typed properties inspired by Flutter.

**Key Classes:**
- `Style` - Immutable styling container
- `Color` - RGBA color with helper methods
- `Border` - Border properties
- `BorderStyle` - Border style enumeration
- `FontWeight` - Font weight enumeration
- `Shadow` - Box shadow properties
- `Alignment` - Text and content alignment
- `Flex` - Flexbox layout properties
- `Length` - Length units and values

**Features:**
- Type-safe styling
- Immutability
- Method chaining
- Helper methods for common values

### 3. Layout Engine

The layout engine calculates positions and sizes for all document elements.

**Key Classes:**
- `LayoutEngine` - Main layout orchestrator
- `LayoutContext` - Layout constraints and available space
- `LayoutBox` - Calculated position and size
- `LayoutResult` - Complete layout results
- `Size` - Width and height
- `Point` - X and Y coordinates

**Layout Strategies:**
- Flow Layout - Simple vertical/horizontal flow
- Flex Layout - CSS-like flexbox with grow/shrink
- Grid Layout - 2D grid layouts with configurable columns

### 4. Pagination Engine

The pagination engine handles automatic page breaking.

**Key Classes:**
- `PaginationEngine` - Automatic page breaking
- Configurable page sizes, margins, headers, footers
- Widow/orphan control (planned)

### 5. Template Engine

The template engine provides a custom domain-specific language for PDF generation.

**Key Classes:**
- `Lexer` - Tokenizes template strings
- `Parser` - Builds AST from tokens
- `AstNode` - Template AST nodes
- `PhpTemplateCompiler` - Compiles templates to PHP code
- `Token` - Lexer tokens
- `TokenType` - Token type enumeration

**Template Syntax:**
```
page { 
    column { 
        heading "Title" 
        text "Content" 
    } 
}
```

### 6. PDF Writer

The PDF writer generates valid PDF 1.7 files.

**Key Classes:**
- `PdfFileWriter` - Main PDF generation class
- Generates PDF objects, pages, fonts, catalog, and info
- Supports basic text rendering
- Extensible for advanced features

## Design Principles

### 1. Immutability

All value objects are immutable. Methods like `withStyle()` return new instances rather than modifying the existing one.

```php
$original = Style::make();
$modified = $original->withPadding(10.0);
// $original is unchanged
```

### 2. Composition

Complex documents are built by composing simple components.

```php
Pdf::make()
    ->page(
        Page::a4()->withContent(
            Column::make()->addChildren([
                Heading::h1('Title'),
                Text::make('Content'),
            ])
        )
    )
```

### 3. Type Safety

Strong typing throughout prevents runtime errors.

```php
Color::rgb(255, 0, 0); // Valid
Color::rgb('invalid'); // Type error
```

### 4. Pure PHP

No external dependencies, no HTML/CSS parsing, no eval().

### 5. PSR-4 Autoloading

Standard PHP autoloading for easy integration.

## File Structure

```
src/
├── Contracts/          # Interfaces and contracts
├── Document/           # Document and builder classes
├── Layout/            # Layout engine
├── Nodes/             # Document AST nodes
├── Pagination/       # Pagination engine
├── Pdf/              # PDF writer
├── Styling/          # Styling system
├── Support/          # Utility classes
└── Template/         # Template engine
```

## Performance Considerations

- Lazy evaluation where possible
- Efficient string concatenation
- Minimal object creation in hot paths
- Caching for compiled templates

## Extensibility

The architecture supports extension through:

1. **Custom Nodes** - Extend AbstractNode
2. **Custom Layouts** - Implement layout strategies
3. **Custom Styling** - Add new style properties
4. **Template Extensions** - Extend the template language
5. **PDF Features** - Extend the PDF writer

## Future Enhancements

- Advanced typography (fonts, kerning, ligatures)
- Images and graphics
- Tables and grids
- Forms and interactive elements
- Digital signatures
- Encryption and permissions
- Accessibility features
- Performance optimizations

## Testing Strategy

- Unit tests for individual components
- Integration tests for complete workflows
- Static analysis with PHPStan
- Example documents for manual verification
