# Architecture

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
- `Style` - Style container
- `Color` - Color representation
- `FontWeight` - Font weight constants
- `Alignment` - Text alignment constants

### 3. Layout Engine

The layout engine is responsible for:
- Calculating node positions and sizes
- Handling auto-layout properties
- Managing constraints

### 4. Pagination Engine

The pagination engine handles:
- Page breaking
- Content flow across pages
- Header/footer placement

### 5. Paint Engine

The paint engine renders:
- Text with proper metrics
- Shapes and borders
- Background colors

### 6. PDF Writer

The PDF writer generates the final PDF file with:
- Proper PDF structure
- Font embedding
- Compression
