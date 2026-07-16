# VS Code Extension

The Folio VS Code extension adds language support for `.folio` templates: syntax highlighting, formatting, and an LSP for autocomplete and diagnostics.

## Features

- Syntax highlighting for `.folio` and `.pdf-template` files
- Code formatting with `Shift+Alt+F`
- LSP-powered autocomplete, hover, and diagnostics
- Compile a template to generated PHP with a single command

## Install from a GitHub Release

The easiest way to install the extension is to grab the latest `.vsix` from the [GitHub Releases](https://github.com/mohammadraufzahed/folio/releases) page.

1. Download the `folio-pdf-*.vsix` asset from the latest `vscode-v*` release.
2. In VS Code, open the Extensions panel (`Ctrl+Shift+X`).
3. Click the **...** menu → **Install from VSIX...**.
4. Select the downloaded `.vsix` file.

Or install from the command line:

```bash
code --install-extension folio-pdf-0.2.0.vsix
```

## Requirements

- PHP 8.3 or later
- The Folio PHP package installed in your workspace (`composer require mohammadraufzahed/folio`)

The extension looks for the language server automatically in these locations:

1. A configured absolute path (`folio-pdf.lsp.serverPath`)
2. `<workspace>/lsp/lsp.php` (when working on Folio itself)
3. `<workspace>/vendor/mohammadraufzahed/folio/lsp/lsp.php`
4. `<workspace>/vendor/folio/pdf/lsp/lsp.php`

## Configuration

Set Folio as the default formatter for `.folio` files:

```json
{
  "[folio]": {
    "editor.defaultFormatter": "folio-pdf.folio-pdf",
    "editor.formatOnSave": true
  }
}
```

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

## Install from Source

1. Clone the repository.
2. Open the `vscode-extension` directory.
3. Install dependencies and compile:

   ```bash
   npm install
   npm run compile
   ```

4. Package the extension:

   ```bash
   npm run package
   ```

5. Install the generated `.vsix` file through VS Code's Extensions panel.

## Development Mode

1. Open `vscode-extension` in VS Code.
2. Press `F5` to launch the Extension Development Host.
3. Open any `.folio` file to test highlighting and formatting.

## Links

- Releases: https://github.com/mohammadraufzahed/folio/releases
- Documentation: https://mohammadraufzahed.github.io/folio/
- Repository: https://github.com/mohammadraufzahed/folio
- Issues: https://github.com/mohammadraufzahed/folio/issues
