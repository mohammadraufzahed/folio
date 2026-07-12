# VS Code Extension

VS Code extension for the Folio PDF template language, providing syntax highlighting, formatting, and LSP support.

## Features

- **Syntax Highlighting**: Full syntax highlighting for Folio PDF templates
- **Code Formatting**: Automatic code formatting with configurable indentation
- **LSP Integration**: Language Server Protocol support for autocomplete and diagnostics
- **Template Compilation**: Compile templates directly from VS Code
- **File Association**: Automatic association with `.folio` and `.pdf-template` files

## Installation

### From Source

1. Clone this repository
2. Navigate to the `vscode-extension` directory
3. Install dependencies:
   ```bash
   npm install
   ```
4. Compile the extension:
   ```bash
   npm run compile
   ```
5. Package the extension:
   ```bash
   vsce package
   ```
6. Install the `.vsix` file in VS Code

### Development Mode

1. Press `F5` in VS Code to launch a new Extension Development Host
2. Open a `.folio` file to test the extension

## Commands

- **Folio PDF: Format Document** - Format the current template file
- **Folio PDF: Restart Language Server** - Restart the LSP server

## Configuration

Add to your `settings.json`:

```json
{
  "[folio]": {
    "editor.defaultFormatter": "folio-pdf.folio-pdf",
    "editor.formatOnSave": true
  }
}
```

## File Association

The extension automatically associates with:
- `.folio` files
- `.pdf-template` files

## LSP

The extension starts the LSP server automatically when you open a `.folio` file. Check the Output panel → "Folio PDF LSP" channel for server logs.
