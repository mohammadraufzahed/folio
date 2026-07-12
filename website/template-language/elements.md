# Elements

## Page

Document pages with size and orientation options.

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

## Heading

Headings levels 1-6.

```folio
heading "Main Title"
heading "Subtitle"
heading "Section"
```

## Text

Text content.

```folio
text "Hello, world!"
text name  // Variable interpolation
```

## Column

Vertical container for stacking content.

```folio
column {
  heading "Section"
  text "Content"
  text "More content"
}
```

## Row

Horizontal container for side-by-side content.

```folio
row {
  text "Left"
  text "Center"
  text "Right"
}
```
