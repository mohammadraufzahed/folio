# Control Flow

## If Statement

Conditional rendering.

```folio
if condition {
  text "Condition is true"
}

if enabled {
  text "Enabled"
} else {
  text "Disabled"
}
```

## Foreach Loop

Iterate over arrays.

```folio
foreach items as item {
  text item.name
}

foreach users as user {
  column {
    heading user.name
    text user.email
  }
}
```

## Nested Control Flow

```folio
foreach sections as section {
  if section.visible {
    heading section.title
    foreach section.items as item {
      text item
    }
  }
}
```
