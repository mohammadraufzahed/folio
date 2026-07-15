# Document Nodes

All document nodes are immutable value objects that extend `AbstractNode`. Every node has an optional `Style` and zero or more children. Use the factory methods to build documents, and `withStyle()` / `withChildren()` to create modified copies.

## Page

`Page` represents a physical page. It supports common presets and custom sizes.

```php
use Folio\Pdf\Nodes\Page;

Page::a4();
Page::letter();
Page::a3();
Page::make(600, 800); // width, height in points
```

Add content with `withContent()`:

```php
$page = Page::a4()->withContent($column);
```

## Column

A vertical container that stacks its children.

```php
use Folio\Pdf\Nodes\Column;

$column = Column::make(null, [
    $heading,
    $textBlock,
    $table,
]);
```

Use `withStyle()` to add spacing or background:

```php
$column = Column::make(null, $children)->withStyle($style);
```

## Row

A horizontal container that lays out children side by side.

```php
use Folio\Pdf\Nodes\Row;

$row = Row::make(null, [
    Text::make('Left'),
    Text::make('Center'),
    Text::make('Right'),
]);
```

## Text

A text content node.

```php
use Folio\Pdf\Nodes\Text;

$text = Text::make('Hello, world');
$text = Text::make('Styled text')->withStyle($style);
```

## Heading

Headings support six levels.

```php
use Folio\Pdf\Nodes\Heading;

Heading::h1('Title');
Heading::h2('Subtitle');
Heading::h3('Section');
Heading::h4('Subsection');
Heading::h5('Detail');
Heading::h6('Note');
```

Alternatively, use `Heading::make(string $text, int $level = 1, ?Style $style = null)`:

```php
Heading::make('Custom Heading', 2);
```

## Table

Tables are composed of `TableRow` and `TableCell` nodes. The `Table` node accepts a list of rows and optional column widths.

```php
use Folio\Pdf\Nodes\Table;
use Folio\Pdf\Nodes\TableCell;
use Folio\Pdf\Nodes\TableRow;

$table = Table::make([
    TableRow::make([
        TableCell::make(Text::make('Name')),
        TableCell::make(Text::make('Quantity')),
    ]),
    TableRow::make([
        TableCell::make(Text::make('Widget')),
        TableCell::make(Text::make('12')),
    ]),
], [200.0, 80.0]);
```

Show borders:

```php
$table = Table::make($rows, [200.0, 80.0], style: null, showBorders: true);
```

Mark a row as a header:

```php
$header = TableRow::make($cells, showHeaders: true);
```

## Page Header and Footer

`PageHeader` and `PageFooter` are nodes that define repeated page chrome. In templates, use `pageheader` and `pagefooter` elements.

## Common Methods

All nodes expose:

- `style(): ?Style` — get the node's style.
- `children(): array` — get child nodes.
- `hasChildren(): bool` — check for children.
- `type(): string` — node class identifier.
- `withStyle(?Style $style): static` — return a copy with the given style.
- `withChildren(array $children): static` — return a copy with the given children.

Because these methods return new instances, you can safely compose documents from smaller pieces and reuse nodes without mutation.
