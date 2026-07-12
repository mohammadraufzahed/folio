# Folio PDF API Reference

## Quick Start

```php
use Folio\Pdf\Document\Pdf;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Text;

Pdf::make()
    ->page(
        Page::a4()->withContent(
            Column::make()->addChildren([
                Heading::h1('Hello World'),
                Text::make('This is a PDF document'),
            ])
        )
    )
    ->save('output.pdf');
```

## Core Classes

### Pdf

Main builder class for creating PDF documents.

**Methods:**
- `make(): self` - Create a new Pdf instance
- `page(Page $page): self` - Add a page to the document
- `save(string $path): void` - Generate and save the PDF

**Example:**
```php
Pdf::make()
    ->page(Page::a4()->withContent(...))
    ->save('document.pdf');
```

### Page

Represents a document page with size presets.

**Methods:**
- `make(float $width = 595.0, float $height = 842.0): self` - Create custom size page
- `a4(): self` - Create A4 page (595×842 points)
- `letter(): self` - Create Letter page (612×792 points)
- `a3(): self` - Create A3 page (842×1191 points)
- `withSize(float $width, float $height): self` - Set custom size
- `withContent(?Node $content): self` - Set page content
- `width(): float` - Get page width
- `height(): float` - Get page height
- `content(): ?Node` - Get page content

**Example:**
```php
Page::a4()->withContent(
    Column::make()->addChildren([...])
)
```

### Column

Vertical container for layout.

**Methods:**
- `make(?Style $style = null): self` - Create a column
- `addChildren(array $children): self` - Add child nodes
- `withChildren(array $children): self` - Replace children
- `withStyle(?Style $style): self` - Set style

**Example:**
```php
Column::make()
    ->addChildren([
        Heading::h1('Title'),
        Text::make('Content'),
    ])
```

### Row

Horizontal container for layout.

**Methods:**
- `make(?Style $style = null): self` - Create a row
- `addChildren(array $children): self` - Add child nodes
- `withChildren(array $children): self` - Replace children
- `withStyle(?Style $style): self` - Set style

**Example:**
```php
Row::make()
    ->addChildren([
        Text::make('Item 1'),
        Text::make('Item 2'),
    ])
```

### Text

Text content node.

**Methods:**
- `make(string $text, ?Style $style = null): self` - Create text node
- `withText(string $text): self` - Set text content
- `withStyle(?Style $style): self` - Set style
- `text(): string` - Get text content

**Example:**
```php
Text::make('Hello World')->withStyle(
    Style::make()->withColor(Color::hex('#ff0000'))
)
```

### Heading

Heading node with levels H1-H6.

**Methods:**
- `h1(string $text, ?Style $style = null): self` - Create H1 heading
- `h2(string $text, ?Style $style = null): self` - Create H2 heading
- `h3(string $text, ?Style $style = null): self` - Create H3 heading
- `h4(string $text, ?Style $style = null): self` - Create H4 heading
- `h5(string $text, ?Style $style = null): self` - Create H5 heading
- `h6(string $text, ?Style $style = null): self` - Create H6 heading
- `withLevel(int $level): self` - Set heading level
- `withText(string $text): self` - Set heading text
- `withStyle(?Style $style): self` - Set style
- `level(): int` - Get heading level
- `text(): string` - Get heading text

**Example:**
```php
Heading::h1('Main Title')
Heading::h2('Subtitle')
```

## Styling

### Style

Immutable styling container.

**Methods:**
- `make(): self` - Create empty style
- `withPadding(?float $value): self` - Set all padding
- `withPaddingTop(?float $value): self` - Set top padding
- `withPaddingBottom(?float $value): self` - Set bottom padding
- `withPaddingLeft(?float $value): self` - Set left padding
- `withPaddingRight(?float $value): self` - Set right padding
- `withMargin(?float $value): self` - Set all margin
- `withMarginTop(?float $value): self` - Set top margin
- `withMarginBottom(?float $value): self` - Set bottom margin
- `withMarginLeft(?float $value): self` - Set left margin
- `withMarginRight(?float $value): self` - Set right margin
- `withColor(?Color $color): self` - Set text color
- `withFontSize(?float $size): self` - Set font size
- `withFontWeight(?FontWeight $weight): self` - Set font weight
- `withLineHeight(?float $height): self` - Set line height
- `withAlignment(?Alignment $alignment): self` - Set text alignment

**Example:**
```php
Style::make()
    ->withPadding(20.0)
    ->withColor(Color::hex('#333333'))
    ->withFontSize(14.0)
```

### Color

Color representation with helper methods.

**Methods:**
- `rgb(int $red, int $green, int $blue, float $alpha = 1.0): self` - Create from RGB
- `hex(string $hex): self` - Create from hex string
- `black(): self` - Black color
- `white(): self` - White color
- `gray(float $gray): self` - Grayscale color
- `red(): self` - Red color
- `green(): self` - Green color
- `blue(): self` - Blue color
- `withAlpha(float $alpha): self` - Set alpha channel
- `toHex(): string` - Convert to hex string

**Example:**
```php
Color::hex('#ff0000')
Color::rgb(255, 0, 0)
Color::black()
```

## Template Engine

### PhpTemplateCompiler

Compiles template strings to PHP code.

**Methods:**
- `__construct(string $cacheDir = '/tmp/folio-pdf-cache')` - Create compiler
- `compile(string $template): string` - Compile template string
- `compileFile(string $path): string` - Compile template file
- `getCachePath(string $template): string` - Get cache file path

**Example:**
```php
$compiler = new PhpTemplateCompiler();
$phpCode = $compiler->compile('page { column { heading "Title" } }');
```

## Template Syntax

### Basic Structure

```
page {
    column {
        heading "Title"
        text "Content"
    }
}
```

### Control Structures

**If/Else:**
```
if condition {
    // content
} else {
    // alternative
}
```

**Foreach:**
```
foreach items as item {
    text item
}
```

### Directives

```
@header "Page Header"
@footer "Page Footer"
```

## Layout Engines

### LayoutEngine

Main layout orchestrator.

**Methods:**
- `layout(Document $document): LayoutResult` - Layout entire document
- `layoutNode(Node $node, LayoutContext $context): LayoutBox` - Layout single node

### FlexLayout

CSS-like flexbox layout.

**Methods:**
- `layout(Node $node, LayoutContext $context): LayoutBox` - Layout with flex

### GridLayout

2D grid layout.

**Methods:**
- `__construct(int $columns = 2, float $gap = 10.0)` - Create grid layout
- `layout(Node $node, LayoutContext $context): LayoutBox` - Layout with grid

## Pagination

### PaginationEngine

Automatic page breaking.

**Methods:**
- `__construct(float $pageWidth, float $pageHeight, float $margin, ...)` - Create pagination engine
- `paginate(Node $content): Document` - Paginate content

## Best Practices

1. **Use Fluent Chaining** - Chain methods for clean, readable code
2. **Reuse Styles** - Define styles once and reuse them
3. **Compose Layouts** - Build complex layouts from simple components
4. **Cache Templates** - Use template compiler caching for performance
5. **Test Incrementally** - Test small components before building complex documents

## Complete Example

```php
use Folio\Pdf\Document\Pdf;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Row;
use Folio\Pdf\Nodes\Text;
use Folio\Pdf\Styling\Color;
use Folio\Pdf\Styling\FontWeight;
use Folio\Pdf\Styling\Style;

Pdf::make()
    ->page(
        Page::a4()->withContent(
            Column::make()
                ->addChildren([
                    Heading::h1('Invoice'),
                    Text::make('Invoice #001'),
                    
                    Row::make()
                        ->addChildren([
                            Column::make()
                                ->addChildren([
                                    Text::make('Item 1 - $10.00'),
                                    Text::make('Item 2 - $20.00'),
                                ]),
                            Column::make()
                                ->addChildren([
                                    Text::make('Total: $30.00'),
                                ]),
                        ]),
                ])
        )
    )
    ->save('invoice.pdf');
```
