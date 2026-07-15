# Styling System

Folio styles are typed value objects. You describe padding, margins, colors, typography, alignment, borders, and shadows with explicit method calls, and the layout engine applies them during document generation.

## Style Objects

A `Style` object is immutable. Each `with*` method returns a new `Style` instance:

```php
use Folio\Pdf\Styling\Style;
use Folio\Pdf\Styling\Color;
use Folio\Pdf\Styling\FontWeight;
use Folio\Pdf\Styling\Alignment;

$style = Style::make()
    ->withPadding(20.0)
    ->withMargin(10.0)
    ->withColor(Color::hex('#333333'))
    ->withFontSize(14.0)
    ->withFontWeight(FontWeight::Bold)
    ->withAlignment(Alignment::Center);
```

Apply a style to any node:

```php
Text::make('Hello')->withStyle($style);
Column::make(null, [...])->withStyle($style);
Heading::h1('Title')->withStyle($style);
```

## Styling in Templates

Elements accept styling attributes directly:

```folio
page {
  column(padding=20, margin=10) {
    heading(color="#1e293b", fontSize=18) "Styled Heading"
    text(color="#475569", fontSize=11) "Styled body text."
  }
}
```

Common attributes:

| Attribute | Type | Description |
|-----------|------|-------------|
| `padding` | number | Space inside the element |
| `margin` | number | Space outside the element |
| `color` | hex or name | Text color |
| `fontSize` | number | Text size in points |
| `fontWeight` | `normal`, `bold`, etc. | Text weight |
| `align` | `left`, `center`, `right`, `justify` | Text alignment |
| `background` | hex or name | Background color |
| `width` | number, length, or percent | Width |
| `height` | number, length, or percent | Height |

## Colors

Create colors from hex, RGB, or named values:

```php
use Folio\Pdf\Styling\Color;

Color::hex('#333333');
Color::rgb(51, 51, 51);
Color::rgba(51, 51, 51, 1.0);
```

In templates, use hex strings directly:

```folio
text(color="#333333") "Dark text"
text(color="red") "Named color"
```

## Lengths and Percentages

Numeric values default to points. You can also use explicit units:

```folio
column(width=50%) { ... }
page(size="600x800") { ... }
```

Supported units include `pt`, `px`, `cm`, `mm`, and `%`.

## Borders and Shadows

`Border` and `Shadow` objects are first-class values and can be attached through `Style`:

```php
use Folio\Pdf\Styling\Border;
use Folio\Pdf\Styling\Shadow;
use Folio\Pdf\Styling\Color;

$style = Style::make()
    ->withBorder(Border::solid(Color::hex('#cbd5e1'), 1.0))
    ->withShadow(Shadow::make(Color::hex('#000000'), 2.0, 2.0, 4.0));
```

## Inheritance

Styles are not automatically inherited. Each node carries its own style object. To apply a common look to a group, set the style on the container (`Column`, `Row`, `Page`) and style its children explicitly where they differ. This makes the document tree easier to inspect and debug.
