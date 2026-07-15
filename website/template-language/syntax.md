# Syntax Reference

Folio templates use a small, predictable grammar. Whitespace is flexible, statements are separated by newlines or semicolons, and blocks are wrapped in braces.

## Variables

Declare variables with a default value. These defaults are merged with the data array passed at render time; data values override defaults.

```folio
var title = "Default Title"
var total = 0.00
```

Use `prop` for values that are required from the caller:

```folio
prop title
```

## Comments

Single-line comments start with `//`:

```folio
// This line is ignored
heading "Title"
```

## Strings

Use double quotes for string literals:

```folio
text "Hello, world"
```

Multiline strings are not supported. For long text, concatenate by placing multiple `text` elements in sequence.

## Numbers

Numeric values default to points. Explicit units are supported for lengths:

```folio
var padding = 20      // 20 pt
var width = 50%       // 50% of container
var margin = 10pt
```

## Expressions

Expressions support comparison, logical operators, and dot notation for array/object properties:

```folio
if total > 100 { ... }
if enabled && visible { ... }
if user.role == "admin" { ... }
```

## Blocks

Elements and control-flow statements use braces for children:

```folio
page {
  column {
    heading "Title"
    text "Body"
  }
}
```

## Page Presets

```folio
page { ... }
page(size=a3) { ... }
page(size=letter, orientation=landscape) { ... }
page(size="600x800") { ... }
```

| Attribute | Values | Default |
|-----------|--------|---------|
| `size` | `a4`, `letter`, `a3`, `"WxH"` | `a4` |
| `orientation` | `portrait`, `landscape` | `portrait` |

## Attributes

Elements accept attributes inside parentheses. Attribute values can be strings, numbers, booleans, or identifiers:

```folio
heading(color="#1e293b", fontSize=18) "Styled Heading"
column(padding=20, margin=10) { ... }
text(align=center, fontWeight=bold) "Centered bold text"
```

## Attribute Reference

Common attributes across elements:

| Attribute | Type | Purpose |
|-----------|------|---------|
| `padding` | number | Inner spacing |
| `margin` | number | Outer spacing |
| `color` | hex or name | Text color |
| `fontSize` | number | Text size in points |
| `fontWeight` | `normal`, `bold` | Text weight |
| `align` | `left`, `center`, `right`, `justify` | Text alignment |
| `background` | hex or name | Background color |
| `width` | number or percent | Element width |
| `height` | number or percent | Element height |

## Variable Interpolation

Variables are referenced by name. Folio does not use embedded string interpolation; instead, place a `text` element where you want the value:

```folio
var customer = "Jane Smith"

text "Customer:"
text customer
```

This keeps the template structure consistent and avoids escaping surprises.
