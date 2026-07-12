# Tree-sitter Grammar

This is the Tree-sitter grammar for the Folio PDF template language, providing syntax highlighting and parsing for editor support.

## Installation

```bash
cd tree-sitter-folio-pdf
npm install
```

## Usage

### Generate the Parser

```bash
npm run generate
```

### Test the Grammar

```bash
npm run test
```

### Parse a File

```bash
npm run parse example.folio
```

## Grammar Features

- **Elements**: page, column, row, text, heading
- **Control Structures**: if/else, foreach
- **Directives**: @header, @footer, @import
- **Attributes**: Named attributes for elements
- **Comments**: Single-line comments with //
- **Strings**: Double-quoted strings
- **Numbers**: Integer and floating-point numbers
- **Expressions**: Comparisons and logical operations

## Integration

The grammar is used by:
- [VS Code Extension](./vscode.md) for syntax highlighting
- [LSP](./lsp.md) for parsing and analysis
- Other editors that support Tree-sitter

## Development

To modify the grammar:

1. Edit `grammar.js`
2. Run `npm run generate` to regenerate the parser
3. Run `npm run test` to verify changes
4. Update the VS Code extension with the new grammar
