# API Reference

This document provides a comprehensive reference for Folio PDF's public API.

## Main Builder API

### Pdf

The main entry point for creating PDF documents.

```php
use Folio\Pdf\Document\Pdf;

$pdf = Pdf::make();
```

#### Methods

##### `make(): self`

Creates a new Pdf instance.

```php
$pdf = Pdf::make();
```

##### `theme(string $name): self`

Sets the theme for the document.

```php
$pdf = Pdf::make()->theme('navy');
```

##### `pageHeader(array $header): self`

Sets the page header configuration.

```php
$pdf = Pdf::make()->pageHeader([
    'title' => 'Report',
    'subtitle' => 'Q4 2024',
    'badge' => 'CONFIDENTIAL',
    'theme' => 'navy',
]);
```

**Header Options:**
- `title` (string): Main title
- `subtitle` (string): Subtitle
- `badge` (string): Badge text
- `rightTitle` (string): Right-aligned title
- `rightSubtitle` (string): Right-aligned subtitle
- `theme` (string): Theme name (navy, teal, slate, emerald, crimson, violet)
- `style` (string): Style variant (bar, band, minimal, split)
- `showMonogram` (bool): Show monogram tile
- `monogram` (string): Custom monogram text
- `only` (string): Show on specific pages (first, last, notfirst, rest)

##### `pageFooter(array $footer): self`

Sets the page footer configuration.

```php
$pdf = Pdf::make()->pageFooter([
    'left' => 'Confidential',
    'center' => 'Acme Corp',
    'showPageNumber' => true,
]);
```

**Footer Options:**
- `left` (string): Left-aligned text
- `center` (string): Center text
- `right` (string): Right-aligned text
- `showPageNumber` (bool): Show page numbers
- `pageFormat` (string): Page number format (default: "Page {page} of {pages}")
- `theme` (string): Theme name
- `style` (string): Style variant (rule, band, minimal)
- `only` (string): Show on specific pages

##### `page(Page $page): self`

Adds a page to the document.

```php
$pdf = Pdf::make()
    ->page(Page::a4()->withContent($content))
    ->page(Page::a4()->withContent($content2));
```

##### `content(Node $node): self`

Sets the document content (creates a single page).

```php
$pdf = Pdf::make()->content($content);
```

##### `save(string $path): void`

Generates and saves the PDF to a file.

```php
$pdf->save('output.pdf');
```

##### `toString(): string`

Generates and returns the PDF as a string.

```php
$pdfString = $pdf->toString();
```

##### `toBytes(): string`

Generates and returns the PDF as bytes.

```php
$pdfBytes = $pdf->toBytes();
```

## Document Nodes

### Page

Represents a PDF page.

```php
use Folio\Pdf\Nodes\Page;
```

#### Static Factory Methods

##### `make(float $width, float $height): Page`

Creates a page with custom dimensions.

```php
$page = Page::make(595.0, 842.0);
```

##### `a4(): Page`

Creates an A4 page (595 x 842 points).

```php
$page = Page::a4();
```

##### `a3(): Page`

Creates an A3 page (842 x 1191 points).

```php
$page = Page::a3();
```

##### `letter(): Page`

Creates a Letter page (612 x 792 points).

```php
$page = Page::letter();
```

#### Methods

##### `withContent(Node $content): Page`

Sets the page content.

```php
$page = Page::a4()->withContent($column);
```

##### `withSize(float $width, float $height): Page`

Sets the page dimensions.

```php
$page = Page::a4()->withSize(600.0, 800.0);
```

##### `width(): float`

Returns the page width.

##### `height(): float`

Returns the page height.

##### `content(): ?Node`

Returns the page content.

### Column

Vertical layout container.

```php
use Folio\Pdf\Nodes\Column;
```

#### Static Factory Methods

##### `make(): Column`

Creates a new column.

```php
$column = Column::make();
```

#### Methods

##### `addChildren(array $children): Column`

Adds children to the column.

```php
$column = Column::make()->addChildren([
    Heading::h1('Title'),
    Text::make('Content'),
]);
```

##### `withStyle(?Style $style): Column`

Sets the column style.

```php
$column = Column::make()->withStyle($style);
```

### Row

Horizontal layout container.

```php
use Folio\Pdf\Nodes\Row;
```

#### Static Factory Methods

##### `make(): Row`

Creates a new row.

```php
$row = Row::make();
```

#### Methods

##### `addChildren(array $children): Row`

Adds children to the row.

```php
$row = Row::make()->addChildren([
    Text::make('Left'),
    Text::make('Right'),
]);
```

##### `withStyle(?Style $style): Row`

Sets the row style.

```php
$row = Row::make()->withStyle($style);
```

### Text

Text content.

```php
use Folio\Pdf\Nodes\Text;
```

#### Static Factory Methods

##### `make(string $text): Text`

Creates text content.

```php
$text = Text::make('Hello, World!');
```

#### Methods

##### `text(): string`

Returns the text content.

##### `withStyle(?Style $style): Text`

Sets the text style.

```php
$text = Text::make('Hello')->withStyle($style);
```

### Heading

Heading with levels.

```php
use Folio\Pdf\Nodes\Heading;
```

#### Static Factory Methods

##### `h1(string $text): Heading`
##### `h2(string $text): Heading`
##### `h3(string $text): Heading`
##### `h4(string $text): Heading`
##### `h5(string $text): Heading`
##### `h6(string $text): Heading`

Creates headings at different levels.

```php
$h1 = Heading::h1('Main Title');
$h2 = Heading::h2('Section Title');
```

#### Methods

##### `text(): string`

Returns the heading text.

##### `level(): int`

Returns the heading level (1-6).

##### `withStyle(?Style $style): Heading`

Sets the heading style.

```php
$heading = Heading::h1('Title')->withStyle($style);
```

### Table

Table with rows and cells.

```php
use Folio\Pdf\Nodes\Table;
```

#### Static Factory Methods

##### `make(array $rows): Table`

Creates a table.

```php
$table = Table::make([
    TableRow::make([
        TableCell::make('Header 1'),
        TableCell::make('Header 2'),
    ]),
    TableRow::make([
        TableCell::make('Cell 1'),
        TableCell::make('Cell 2'),
    ]),
]);
```

##### `simple(array $rows): Table`

Creates a simple table with borders and headers.

```php
$table = Table::simple($rows);
```

##### `noBorders(array $rows): Table`

Creates a table without borders.

```php
$table = Table::noBorders($rows);
```

##### `withColumnWidths(array $rows, array $widths): Table`

Creates a table with custom column widths.

```php
$table = Table::withColumnWidths($rows, [100.0, 200.0, 150.0]);
```

#### Methods

##### `addRow(TableRow $row): Table`

Adds a row to the table.

```php
$table = $table->addRow($newRow);
```

##### `withStyle(?Style $style): Table`

Sets the table style.

```php
$table = $table->withStyle($style);
```

##### `rows(): array`

Returns the table rows.

##### `rowCount(): int`

Returns the number of rows.

##### `columnCount(): int`

Returns the number of columns.

### TableRow

Table row.

```php
use Folio\Pdf\Nodes\TableRow;
```

#### Static Factory Methods

##### `make(array $cells): TableRow`

Creates a table row.

```php
$row = TableRow::make([
    TableCell::make('Cell 1'),
    TableCell::make('Cell 2'),
]);
```

#### Methods

##### `cells(): array`

Returns the row cells.

##### `cellCount(): int`

Returns the number of cells.

### TableCell

Table cell.

```php
use Folio\Pdf\Nodes\TableCell;
```

#### Static Factory Methods

##### `make(string $content): TableCell`

Creates a table cell.

```php
$cell = TableCell::make('Content');
```

#### Methods

##### `content(): string`

Returns the cell content.

##### `withStyle(?Style $style): TableCell`

Sets the cell style.

```php
$cell = TableCell::make('Content')->withStyle($style);
```

## Styling

### Style

Immutable style object.

```php
use Folio\Pdf\Styling\Style;
```

#### Static Factory Methods

##### `make(): Style`

Creates a new style.

```php
$style = Style::make();
```

#### Methods

##### Spacing

```php
$style->withPadding(float $value): Style
$style->withMargin(float $value): Style
$style->withPaddingTop(float $value): Style
$style->withPaddingRight(float $value): Style
$style->withPaddingBottom(float $value): Style
$style->withPaddingLeft(float $value): Style
$style->withMarginTop(float $value): Style
$style->withMarginRight(float $value): Style
$style->withMarginBottom(float $value): Style
$style->withMarginLeft(float $value): Style
```

##### Typography

```php
$style->withFont(string $font): Style
$style->withFontSize(float $size): Style
$style->withFontWeight(FontWeight $weight): Style
$style->withLineHeight(float $height): Style
$style->withLetterSpacing(float $spacing): Style
```

##### Colors

```php
$style->withColor(Color $color): Style
$style->withBackground(Color $color): Style
```

##### Effects

```php
$style->withOpacity(float $opacity): Style
$style->withRotation(float $rotation): Style
$style->withScale(float $scale): Style
$style->withShadow(Shadow $shadow): Style
```

##### Layout

```php
$style->withBorder(Border $border): Style
$style->withRadius(float $radius): Style
$style->withAlignment(Alignment $alignment): Style
$style->withFlex(Flex $flex): Style
$style->withGrow(float $grow): Style
$style->withShrink(float $shrink): Style
$style->withWidth(Length $width): Style
$style->withHeight(Length $height): Style
$style->withMinWidth(Length $width): Style
$style->withMaxWidth(Length $width): Style
```

### Color

Color utilities.

```php
use Folio\Pdf\Styling\Color;
```

#### Static Factory Methods

##### `hex(string $hex): Color`

Creates a color from hex string.

```php
$color = Color::hex('#ff0000');
```

##### `rgb(float $r, float $g, float $b): Color`

Creates a color from RGB values (0-1).

```php
$color = Color::rgb(1.0, 0.0, 0.0);
```

##### `black(): Color`
##### `white(): Color`
##### `red(): Color`
##### `green(): Color`
##### `blue(): Color`

Named color constants.

```php
$color = Color::black();
```

### FontWeight

Font weight enumeration.

```php
use Folio\Pdf\Styling\FontWeight;
```

#### Constants

```php
FontWeight::Thin
FontWeight::ExtraLight
FontWeight::Light
FontWeight::Regular
FontWeight::Medium
FontWeight::SemiBold
FontWeight::Bold
FontWeight::ExtraBold
FontWeight::Black
```

### Alignment

Text alignment enumeration.

```php
use Folio\Pdf\Styling\Alignment;
```

#### Constants

```php
Alignment::Left
Alignment::Center
Alignment::Right
Alignment::Justify
```

### Length

Dimension unit.

```php
use Folio\Pdf\Styling\Length;
```

#### Static Factory Methods

##### `px(float $value): Length`

Creates a pixel length.

```php
$length = Length::px(100.0);
```

##### `pt(float $value): Length`

Creates a point length.

```php
$length = Length::pt(12.0);
```

## Template Compiler

### PhpTemplateCompiler

Compiles Folio templates to PHP code.

```php
use Folio\Pdf\Template\PhpTemplateCompiler;
```

#### Constructor

##### `__construct(string $cacheDir = '/tmp/folio-pdf-cache')`

Creates a new compiler.

```php
$compiler = new PhpTemplateCompiler('/path/to/cache');
```

#### Methods

##### `compile(string $template): string`

Compiles a template string to PHP code.

```php
$php = $compiler->compile('page { text "Hello" }');
```

##### `compileFile(string $path): string`

Compiles a template file to PHP code.

```php
$php = $compiler->compileFile('template.folio');
```

##### `render(string $template, array $data = []): Pdf`

Compiles and renders a template string.

```php
$pdf = $compiler->render('page { text title }', ['title' => 'Hello']);
```

##### `renderFile(string $path, array $data = []): Pdf`

Compiles and renders a template file.

```php
$pdf = $compiler->renderFile('template.folio', ['title' => 'Hello']);
```

##### `setStrict(bool $strict = true): void`

Enables strict mode (throws on undefined variables).

```php
$compiler->setStrict(true);
```

##### `setBaseDir(string $dir): void`

Sets the base directory for partial resolution.

```php
$compiler->setBaseDir('/path/to/templates');
```

##### `addPartialDir(string $dir): void`

Adds a directory to search for partials.

```php
$compiler->addPartialDir('/path/to/partials');
```

##### `clearRuntimeCache(): void`

Clears the in-memory renderer cache.

```php
$compiler->clearRuntimeCache();
```

## Template Language

### Variables

```folio
var title = "Hello World"
var count = 42
```

### Elements

```folio
page { content }
column { content }
row { content }
text "Content"
heading title
table { rows }
```

### Attributes

```folio
text(fontSize=14, color=red) "Content"
page(size=a4, orientation=landscape) { content }
```

### Conditionals

```folio
if count > 5 {
  text "Big"
} else {
  text "Small"
}
```

### Loops

```folio
foreach items as item {
  text item.name
} empty {
  text "No items"
}
```

### Property Access

```folio
text user.name
text company.address.city
```

### Partials

```folio
partial "header"
partial "footer" (title="Report")
```
