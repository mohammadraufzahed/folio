# Folio PDF

Language support for the Folio PDF template language in Visual Studio Code.

## Features

- Syntax highlighting for `.folio` and `.pdf-template` files
- Document formatting (`Shift+Alt+F`)
- Language-server powered autocomplete, diagnostics, and hover
- Compile a template to generated PHP with a single command

## Requirements

- PHP 8.3 or later
- The Folio PHP package installed in your workspace (`composer require mohammadraufzahed/folio`)

The extension looks for the language server automatically in these locations:

1. A configured absolute path (`folio-pdf.lsp.serverPath`)
2. `<workspace>/lsp/lsp.php` (when working on Folio itself)
3. `<workspace>/vendor/mohammadraufzahed/folio/lsp/lsp.php`
4. `<workspace>/vendor/folio/pdf/lsp/lsp.php`

## Extension Settings

| Setting | Default | Description |
|---------|---------|-------------|
| `folio-pdf.format.enable` | `true` | Enable formatting |
| `folio-pdf.format.indentSize` | `4` | Indentation width |
| `folio-pdf.lsp.enable` | `true` | Enable the language server |
| `folio-pdf.lsp.phpPath` | `php` | Path to the PHP binary |
| `folio-pdf.lsp.serverPath` | `''` | Absolute path to `lsp/lsp.php` |

## Commands

- `Folio PDF: Format Document` — format the active template
- `Folio PDF: Compile Template` — compile the template to PHP
- `Folio PDF: Restart Language Server` — restart the LSP

## Install from the Marketplace

Search for **Folio PDF** in the VS Code Extensions panel, or install from the command line:

```bash
code --install-extension folio-pdf.folio-pdf
```

## Links

- Documentation: https://mohammadraufzahed.github.io/folio/
- Repository: https://github.com/mohammadraufzahed/folio
- Issues: https://github.com/mohammadraufzahed/folio/issues
