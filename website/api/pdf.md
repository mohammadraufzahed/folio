# Pdf

`Folio\Pdf\Document\Pdf` is the entry point for every document. It accumulates pages, applies page headers and footers, and writes the final file.

## Methods

### `make(): self`

Create a new `Pdf` instance.

```php
$pdf = Pdf::make();
```

### `page(Page $page): self`

Add a page to the document.

```php
use Folio\Pdf\Nodes\Page;

$pdf = Pdf::make()
    ->page(Page::a4()->withContent($content))
    ->page(Page::letter()->withContent($moreContent));
```

### `pageHeader(array $header): self`

Define a header for every page, or scope it with `only`.

```php
$pdf = Pdf::make()
    ->pageHeader([
        'title' => 'Acme Corporation',
        'subtitle' => 'Annual Report',
        'only' => 'all',
    ]);
```

For a fully custom header, use the `pageheader` element inside a `.folio` template.

### `pageFooter(array $footer): self`

Define a footer for every page.

```php
$pdf = Pdf::make()
    ->pageFooter([
        'left' => 'Confidential',
        'center' => 'Acme Corporation',
        'showPageNumber' => true,
    ]);
```

### `theme(string $name): self`

Apply a named theme to page headers and footers. Themes control color and spacing defaults.

```php
$pdf = Pdf::make()->theme('navy');
```

### `save(string $path): void`

Generate and save the PDF to disk.

```php
$pdf->save('document.pdf');
```

### `toString(): string`

Return the PDF content as a string.

### `toBytes(): string`

Return the PDF content as raw bytes.

## Complete Example

```php
use Folio\Pdf\Document\Pdf;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Text;

Pdf::make()
    ->pageHeader([
        'title' => 'Acme Corporation',
        'subtitle' => 'Quarterly Report',
    ])
    ->pageFooter([
        'center' => 'Internal Use Only',
        'showPageNumber' => true,
    ])
    ->page(
        Page::a4()->withContent(
            Column::make(null, [
                Heading::h1('Quarterly Report'),
                Text::make('Prepared for internal review.'),
            ])
        )
    )
    ->save('report.pdf');
```

## Template Equivalent

```folio
pageheader {
  text "Acme Corporation"
  text "Quarterly Report"
}

pagefooter {
  text "Internal Use Only"
  pagenum(format="Page {page} of {pages}", size=8)
}

page {
  heading "Quarterly Report"
  text "Prepared for internal review."
}
```

Compile and render:

```php
use Folio\Pdf\Template\PhpTemplateCompiler;

$compiler = new PhpTemplateCompiler();
$pdf = $compiler->render(file_get_contents('report.folio'));
$pdf->save('report.pdf');
```
