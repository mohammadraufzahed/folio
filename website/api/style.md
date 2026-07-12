# Style API

## PHP Builder API

### Style

Main style container class.

```php
use Folio\Pdf\Styling\Style;

$style = Style::make()
    ->withPadding(20.0)
    ->withMargin(10.0)
    ->withColor(Color::hex('#333333'))
    ->withFontSize(14.0)
    ->withFontWeight(FontWeight::Bold)
    ->withAlignment(Alignment::Center);
```

### Methods

- `withPadding(float $padding)` - Set padding on all sides
- `withMargin(float $margin)` - Set margin on all sides
- `withColor(Color $color)` - Set text color
- `withFontSize(float $size)` - Set font size
- `withFontWeight(FontWeight $weight)` - Set font weight
- `withAlignment(Alignment $alignment)` - Set text alignment

### Color

Color representation.

```php
use Folio\Pdf\Styling\Color;

Color::hex('#333333')      // From hex string
Color::rgb(51, 51, 51)     // From RGB values (0-255)
Color::rgba(51, 51, 51, 1.0) // From RGBA values (0-255, alpha 0-1)
```

### FontWeight

Font weight constants.

```php
use Folio\Pdf\Styling\FontWeight;

FontWeight::Regular
FontWeight::Bold
```

### Alignment

Text alignment constants.

```php
use Folio\Pdf\Styling\Alignment;

Alignment::Left
Alignment::Center
Alignment::Right
```

## Template Language

### Style Directive

```folio
@style padding=20, margin=10, color="#333333", fontSize=14, fontWeight=bold, alignment=center {
  text "Styled text"
}
```

### Inline Attributes

```folio
page(size=a4, orientation=landscape) {
  heading "Page"
}
```

### Color Values

Use hex color strings in templates:

```folio
@style color="#333333" {
  text "Dark text"
}

@style color="#FF0000" {
  text "Red text"
}
```
