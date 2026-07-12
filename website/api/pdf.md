# Pdf API

Main builder class for creating PDF documents.

## PHP Builder API

### Methods

#### `make(): self`

Create a new Pdf instance.

```php
$pdf = Pdf::make();
```

#### `page(Page $page): self`

Add a page to the document.

```php
Pdf::make()
    ->page(Page::a4()->withContent(...))
    ->page(Page::a4()->withContent(...));
```

#### `save(string $path): void`

Generate and save the PDF.

```php
Pdf::make()
    ->page(Page::a4()->withContent(...))
    ->save('document.pdf');
```

## Example

```php
use Folio\Pdf\Document\Pdf;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Text;

Pdf::make()
    ->page(
        Page::a4()->withContent(
            Column::make()->addChildren([
                Heading::h1('Hello World'),
                Text::make('This is a PDF document'),
            ])
        )
    )
    ->save('output.pdf');
```

## Template Language Equivalent

For template-based PDF generation, use the template compiler:

```php
use Folio\Template\PhpTemplateCompiler;

$compiler = new PhpTemplateCompiler();
$template = $compiler->compile(file_get_contents('document.folio'));

$pdf = $template(['title' => 'Hello World']);
$pdf->save('output.pdf');
```

Template file (`document.folio`):

```folio
var title = "Default Title"

page {
  heading title
  text "This is a PDF document"
}
```
