# Style

`Folio\Pdf\Styling\Style` is an immutable container for visual properties. It does not know how to draw anything; the layout and paint engines read `Style` values when measuring and rendering nodes.

## Style Builder

```php
use Folio\Pdf\Styling\Style;
use Folio\Pdf\Styling\Color;
use Folio\Pdf\Styling\FontWeight;
use Folio\Pdf\Styling\Alignment;

$style = Style::make()
    ->withPadding(20.0)
    ->withMargin(10.0)
    ->withColor(Color::hex('#1e293b'))
    ->withFontSize(12.0)
    ->withFontWeight(FontWeight::Bold)
    ->withAlignment(Alignment::Center);
```

Each `with*` method returns a new `Style` instance. The original is unchanged.

## Color

`Color` represents an RGBA value.

```php
use Folio\Pdf\Styling\Color;

Color::hex('#333333');     // 6- or 8-digit hex
Color::rgb(51, 51, 51);    // RGB 0-255
Color::rgba(51, 51, 51, 1.0); // with alpha
```

Named colors are also accepted in templates:

```folio
text(color="red") "Named color"
text(color="#2563eb") "Hex color"
```

## Length

`Length` supports points, pixels, centimeters, millimeters, and percentages.

```php
use Folio\Pdf\Styling\Length;

Length::pt(12.0);
Length::px(16.0);
Length::cm(1.0);
Length::mm(10.0);
Length::percent(50.0);
```

In templates, numeric values default to points, but explicit units are supported:

```folio
column(width=50%, padding=20pt) { ... }
```

## FontWeight

```php
use Folio\Pdf\Styling\FontWeight;

FontWeight::Regular;
FontWeight::Bold;
```

## Alignment

```php
use Folio\Pdf\Styling\Alignment;

Alignment::Left;
Alignment::Center;
Alignment::Right;
Alignment::Justify;
```

## Border

```php
use Folio\Pdf\Styling\Border;
use Folio\Pdf\Styling\Color;

$border = Border::make(1.0, Color::hex('#cbd5e1'));

$style = Style::make()->withBorder($border);
```

## Shadow

```php
use Folio\Pdf\Styling\Shadow;
use Folio\Pdf\Styling\Color;

$shadow = Shadow::make(
    2.0,                 // offset X
    2.0,                 // offset Y
    4.0,                 // blur radius
    Color::rgba(0, 0, 0, 0.2)
);

$style = Style::make()->withShadow($shadow);
```

## Flex

`Flex` controls how a node expands or shrinks within a `Row` or `Column`.

```php
use Folio\Pdf\Styling\Flex;

$flex = Flex::make(1.0);       // grow factor 1
$flex = Flex::make(1.0, 1.0); // grow and shrink factor 1

$style = Style::make()->withFlex($flex);
```

## Applying Styles

Attach a `Style` to a node:

```php
Text::make('Important')->withStyle($style);
Column::make(null, $children)->withStyle($style);
Page::a4()->withContent($column->withStyle($pageStyle));
```

Styles are not inherited. Each node owns its own `Style`, which keeps the layout tree explicit and prevents surprises.
