# Template Language Overview

The Folio template language is a declarative DSL for generating PDF documents. Templates are compiled to PHP closures that accept a data array and return a `Pdf` instance.

## Why a Template Language?

While the PHP builder API provides full programmatic control, the template language offers:

- **Declarative syntax** - Describe what you want, not how to build it
- **Separation of concerns** - Keep templates separate from business logic
- **Reusability** - Share templates across projects
- **IDE support** - Syntax highlighting, autocomplete, and diagnostics

## Syntax Overview

A simple template:

```folio
var title = "Default Title"

page {
  heading "My Document"
  text "Hello, world!"
}
```

## Compilation

Templates are compiled to PHP:

```php
use Folio\Template\PhpTemplateCompiler;

$compiler = new PhpTemplateCompiler();
$template = $compiler->compile(file_get_contents('template.folio'));

$pdf = $template(['title' => 'Custom Title']);
$pdf->save('output.pdf');
```

## Next Steps

- [Syntax Reference](./syntax.md)
- [Elements](./elements.md)
- [Control Flow](./control-flow.md)
- [Directives](./directives.md)
