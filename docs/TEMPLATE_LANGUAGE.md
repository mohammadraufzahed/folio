# Folio Template Language Reference

The Folio template language is a declarative DSL for generating PDF documents. Templates are compiled to PHP closures that accept a data array and return a `Pdf` instance.

## Syntax Overview

```
var title = "Default Title"

page {
  heading "My Document"
  text "Hello, world!"
}
```

## Statements

### Variables

```
var name = "default value"
prop name = "default value"
```

Declare variables with default values. Defaults are merged with the data array at render time (data overrides defaults).

### Page

```
page { ... }
page(size=a3) { ... }
page(size=letter, orientation=landscape) { ... }
page(size="600x800") { ... }
```

Creates a page node. Supported attributes:

| Attribute     | Values                          | Default   |
|---------------|---------------------------------|-----------|
| `size`        | `a4`, `letter`, `a3`, `"WxH"`  | `a4`      |
| `orientation` | `portrait`, `landscape`         | `portrait`|

### Elements

```
text "content"
heading "Title"
column { ... }
row { ... }
table { ... }
tr { ... }
td { ... }
th { ... }
```

### Control Flow

#### if / else if / else

```
if condition { ... }
if condition { ... } else { ... }
if condition { ... } else if other { ... } else { ... }
if condition { ... } elseif other { ... } else { ... }
```

#### foreach

```
foreach items as item { ... }
foreach items as index, item { ... }
foreach items as item { ... } empty { ... }
```

The `empty` block renders when the collection is empty or not iterable.

### Partials

```
partial "path/to/partial"
partial partialName
```

Partials are resolved relative to the template file's directory (or `baseDir`). The `.folio` extension is added automatically. Partials are compiled as fragments and inlined at compile time.

### Page Chrome

```
pageheader { ... }
pagefooter { ... }
```

Define repeating header/footer content for all pages.

### Chrome Widgets

```
monogram "AB"
badge "New"
spacer
rule
box { ... }
pagenum
img "path/to/image.png"
```

## Expressions

### Literals

- **Strings**: `"hello"` or `'hello'`
- **Numbers**: `42`, `3.14`
- **Identifiers**: `name` (resolved from scope/data)

### Property Access

```
company.name
item.price
user.address.city
```

Dotted paths resolve nested properties from arrays or objects.

### Comparisons

```
x == y
x != y
x < y
x <= y
x > y
x >= y
```

### Boolean Operators

```
a and b
a or b
not a
```

### Grouping

```
(a or b) and c
not (x == y)
```

### Operator Precedence

From lowest to highest:

1. `or`
2. `and`
3. `not` (unary)
4. Comparisons (`==`, `!=`, `<`, `<=`, `>`, `>=`)
5. Property access (`.`)
6. Primary (literals, identifiers, grouped expressions)

## Style Attributes

Style attributes can be applied to `text`, `heading`, `column`, and `row` elements:

```
text(color=red, fontSize=14, align=center) "Centered red text"
heading(fontWeight=bold, color="#0066cc") "Blue Bold Heading"
column(padding=10, background=white) { ... }
row(margin=5, align=center) { ... }
```

### Supported Style Attributes

| Attribute      | Values                                    |
|----------------|-------------------------------------------|
| `color`        | Hex (`#ff0000`), named (`red`, `blue`)    |
| `background`   | Hex or named color                        |
| `fontSize`     | Number (`14`, `14.5`)                     |
| `fontWeight`   | `thin`, `light`, `normal`, `medium`, `semibold`, `bold`, `extrabold`, `black` or numeric (`100`–`900`) |
| `font`         | Font family name                          |
| `padding`      | Number                                    |
| `margin`       | Number                                    |
| `align`        | `left`, `center`, `right`, `justify`      |
| `lineHeight`   | Number                                    |
| `opacity`      | Number (0–1)                              |
| `width`        | Number or length string (`"100pt"`, `"50%"`, `"200px"`) |
| `height`       | Number or length string                   |
| `radius`       | Number                                    |

### Named Colors

`black`, `white`, `red`, `green`, `blue`, `yellow`, `cyan`, `magenta`, `gray`, `grey`, `orange`, `purple`, `pink`, `brown`, `navy`, `teal`, `lime`, `silver`, `maroon`, `olive`

## Data Binding

Templates receive data as an array. Variable references resolve in this order:

1. **Locals** (foreach loop variables: `item`, `index`)
2. **Data** (the data array passed to the template)
3. **Parent scope** (for nested foreach loops)

### Strict Mode

When strict mode is enabled (`$compiler->setStrict(true)`), accessing undefined variables or properties throws a `TemplateError` at render time. In non-strict mode (default), undefined values resolve to an empty string.

## Error Handling

All lexing and parsing errors throw `TemplateError` with source location information:

```
TemplateError: 3:5 — Unexpected token RightBrace '}'
```

The error includes line and column numbers for precise error reporting.

## Directives

```
@directive value
```

Directives are metadata markers (e.g., `@template company-report`) that don't produce rendered output.

## Comments

```
// This is a comment
```

Single-line comments starting with `//` are supported.
