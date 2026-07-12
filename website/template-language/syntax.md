# Syntax Reference

## Statements

### Variables

Declare variables with default values. Defaults are merged with the data array at render time (data overrides defaults).

```folio
var name = "default value"
prop name = "default value"
```

### Page

Creates a page node.

```folio
page { ... }
page(size=a3) { ... }
page(size=letter, orientation=landscape) { ... }
page(size="600x800") { ... }
```

**Attributes:**

| Attribute     | Values                          | Default   |
|---------------|---------------------------------|-----------|
| `size`        | `a4`, `letter`, `a3`, `"WxH"`  | `a4`      |
| `orientation` | `portrait`, `landscape`         | `portrait` |

### Elements

```folio
heading "Title"
text "Content"
column { ... }
row { ... }
```

### Control Flow

```folio
if condition {
  ...
}

foreach items as item {
  ...
}
```

### Comments

```folio
// This is a comment
```

## Strings

Double-quoted strings:

```folio
text "Hello, world!"
```

## Numbers

Integer and floating-point:

```folio
var count = 10
var price = 19.99
```

## Expressions

Comparisons and logical operations:

```folio
if count > 5 { ... }
if enabled && ready { ... }
```
