# Tree-sitter Grammar for Folio PDF Template Language

This is the Tree-sitter grammar for the Folio PDF template language, providing syntax highlighting and parsing for editor support.

## Installation

```bash
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

## Example Template

```folio
page {
    column {
        heading "Invoice"
        text "Customer: John Doe"
        
        foreach items as item {
            text item
        }
    }
}
```

## Editor Integration

This grammar can be used in:
- VS Code (via tree-sitter extension)
- Neovim (via nvim-treesitter)
- Emacs (via tree-sitter-langs)
- Sublime Text (via tree-sitter-sublime)

## Development

To modify the grammar, edit `grammar.js` and run `npm run generate` to regenerate the parser.
