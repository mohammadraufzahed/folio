# Template Language Overview

Folio's template language gives you a clean way to describe documents without mixing design with application logic. Templates are compiled to PHP closures, then rendered with a plain data array.

## Why Use Templates?

- **Separation of concerns** — designers own the `.folio` file; developers own the data.
- **No runtime interpreter** — templates compile to native PHP, so performance is the same as using the builder API directly.
- **Static analysis friendly** — generated code is type-checked by PHP and can be cached on disk.
- **First-class tooling** — the formatter, LSP, VS Code extension, and Tree-sitter grammar all understand the same grammar.

## A Minimal Template

```folio
var title = "Quarterly Report"

page {
  heading title
  text "Prepared for internal review."
}
```

```php
use Folio\Pdf\Template\PhpTemplateCompiler;

$compiler = new PhpTemplateCompiler();
$pdf = $compiler->render(file_get_contents('report.folio'), [
    'title' => 'Q4 2024',
]);

$pdf->save('report.pdf');
```

`render()` returns a `Folio\Pdf\Document\Pdf` instance. You can call `save()`, `toString()`, or `toBytes()` on it.

## How It Works

1. **Lexer** reads the `.folio` source and produces tokens.
2. **Parser** builds an AST from those tokens.
3. **Compiler** walks the AST and emits a PHP closure.
4. **Runtime** executes the closure with your data and returns a `Pdf`.

The compiled output is plain PHP. You can inspect it, commit it, or cache it alongside your templates.

## Next Steps

- [Syntax Reference](./syntax.md)
- [Elements](./elements.md)
- [Control Flow](./control-flow.md)
- [Directives](./directives.md)
