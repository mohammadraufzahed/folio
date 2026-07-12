# Folio PDF VS Code Extension

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

## Configuration

Add to your `settings.json`:

```json
{
  "folio-pdf.format.enable": true,
  "folio-pdf.format.indentSize": 4,
  "folio-pdf.lsp.enable": true
}
```

## Commands

- `Folio PDF: Format Document` - Format the current document
- `Folio PDF: Compile Template` - Compile the current template to PHP

## File Extensions

- `.folio` - Folio PDF template files
- `.pdf-template` - PDF template files

## LSP Configuration

The extension automatically starts the LSP server for `.folio` and `.pdf-template` files. The LSP server provides:

- Autocomplete for elements and keywords
- Syntax error detection
- Hover documentation
- Diagnostics

## Formatter

The formatter provides:
- Consistent indentation
- Proper brace alignment
- Clean formatting of control structures

Customize indentation in settings:

```json
{
  "folio-pdf.format.indentSize": 2
}
```

## Development

### Project Structure

```
vscode-extension/
├── src/
│   ├── extension.ts      # Main extension entry point
│   ├── formatter.ts      # Document formatter
│   └── lspClient.ts      # LSP client wrapper
├── syntaxes/
│   └── folio.tmLanguage.json  # TextMate grammar
├── package.json
└── tsconfig.json
```

### Building

```bash
npm run compile
```

### Testing

```bash
npm test
```

### Linting

```bash
npm run lint
```

## Troubleshooting

### LSP Not Starting

Check that PHP is installed and the LSP server path is correct:
```bash
which php
```

### Formatting Not Working

Ensure formatting is enabled in settings:
```json
{
  "folio-pdf.format.enable": true
}
```

### Syntax Highlighting Not Working

Check that the file extension is recognized:
- `.folio` files should be associated with the Folio PDF language
- `.pdf-template` files should be associated with the PDF Template language

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and linting
5. Submit a pull request

## License

MIT
