# Directives

Directives provide special functionality for templates.

## @header

Define a header for pages.

```folio
@header {
  text "Company Name"
}

page {
  heading "Document"
}
```

## @footer

Define a footer for pages.

```folio
@footer {
  text "Page 1"
}

page {
  heading "Document"
}
```

## @import

Import another template file.

```folio
@import "shared/components.folio"

page {
  heading "Main Document"
  myComponent
}
```

## @style

Apply a style to the following content.

```folio
@style padding=20, margin=10 {
  text "Styled content"
}
```
