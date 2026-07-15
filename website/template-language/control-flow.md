# Control Flow

Folio templates support two control-flow constructs: `if` for conditionals and `foreach` for iteration. Both compile to native PHP control structures in the generated closure.

## If Statements

Use `if` for conditional content.

```folio
if showHeader {
  heading "Report Header"
}
```

Use `else` for the opposite branch:

```folio
if isPremium {
  text "Thank you for being a premium customer."
} else {
  text "Upgrade today."
}
```

Use `elseif` for multiple branches:

```folio
if status == "success" {
  text "Operation completed."
} elseif status == "pending" {
  text "Please wait."
} else {
  text "An error occurred."
}
```

## Foreach Loops

Iterate over arrays and render content for each item.

```folio
foreach products as product {
  column {
    heading product.name
    text product.description
  }
}
```

The loop variable must be a plain identifier, not a template keyword. Use names such as `product`, `item`, or `row` (if the context is unambiguous).

## Nested Control Flow

Control-flow statements can be nested inside elements and other control-flow statements.

```folio
foreach sections as section {
  if section.visible {
    heading section.title
    foreach section.items as item {
      text item.label
    }
  }
}
```

## Expressions in Conditions

Conditions support:

- Comparison: `==`, `!=`, `<`, `>`, `<=`, `>=`
- Logical operators: `&&`, `||`, `!`
- Dot notation for nested properties: `user.active`, `order.total`
- Numeric and string literals

```folio
if user.active && user.role == "admin" {
  text "Admin dashboard"
}

if total > 100 && total <= 500 {
  text "Tier 2 discount"
}
```

## Control Flow Scope

Each `foreach` introduces a new scope. Variables declared inside a loop body are not visible outside of it. The outer scope is restored automatically when the loop ends.
