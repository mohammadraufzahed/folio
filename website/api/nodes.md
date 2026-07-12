# Document Nodes

## PHP Builder API

### Page

Document pages with presets (A4, Letter, A3).

```php
use Folio\Pdf\Nodes\Page;

Page::a4()
Page::letter()
Page::a3()
Page::custom(600, 800)  // width, height in points
```

With content:

```php
Page::a4()->withContent($content);
```

### Column

Vertical container for stacking content.

```php
use Folio\Pdf\Nodes\Column;

Column::make()
    ->addChildren([$node1, $node2, $node3]);
```

With style:

```php
Column::make()->withStyle($style);
```

### Row

Horizontal container for side-by-side content.

```php
use Folio\Pdf\Nodes\Row;

Row::make()
    ->addChildren([$node1, $node2]);
```

### Text

Text content.

```php
use Folio\Pdf\Nodes\Text;

Text::make('Hello, World!');
```

With style:

```php
Text::make('Hello')->withStyle($style);
```

### Heading

Headings (H1-H6).

```php
use Folio\Pdf\Nodes\Heading;

Heading::h1('Title')
Heading::h2('Subtitle')
Heading::h3('Section')
Heading::h4('Subsection')
Heading::h5('Detail')
Heading::h6('Note')
```

With style:

```php
Heading::h1('Title')->withStyle($style);
```

## Template Language

### Page

```folio
page {
  heading "Title"
  text "Content"
}

page(size=a3) {
  heading "Large Page"
}

page(size=letter, orientation=landscape) {
  heading "Landscape Letter"
}

page(size="600x800") {
  heading "Custom Size"
}
```

### Column

```folio
column {
  heading "Section"
  text "Content"
  text "More content"
}
```

### Row

```folio
row {
  text "Left"
  text "Center"
  text "Right"
}
```

### Text

```folio
text "Hello, world!"
text name  // Variable interpolation
```

### Heading

```folio
heading "Main Title"
heading "Subtitle"
heading "Section"
```
