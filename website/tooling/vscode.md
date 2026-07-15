# VS Code Extension

The Folio VS Code extension adds language support for `.folio` templates directly in the editor.

## Features

- Syntax highlighting
- Code formatting with `Shift+Alt+F`
- LSP-powered autocomplete and diagnostics
- Snippets for common elements

## Installation

### From Source

1. Clone the repository.
2. Open the `vscode-extension` directory.
3. Install dependencies and compile:

   ```bash
   npm install
   npm run compile
   ```

4. Package the extension:

   ```bash
   npx vsce package
   ```

5. Install the generated `.vsix` file through VS Code's Extensions panel.

### Development Mode

1. Open `vscode-extension` in VS Code.
2. Press `F5` to launch the Extension Development Host.
3. Open any `.folio` file to test highlighting and formatting.

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

The extension automatically associates itself with:

- `.folio`
- `.pdf-template`
