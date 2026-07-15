# Tree-sitter Grammar

The `tree-sitter-folio-pdf` package provides a [Tree-sitter](https://tree-sitter.github.io/tree-sitter/) grammar for the Folio template language. It powers accurate syntax highlighting and parsing in editors that support Tree-sitter.

## Setup

```bash
cd tree-sitter-folio-pdf
npm install
```

## Commands

Generate the parser from `grammar.js`:

```bash
npm run generate
```

Run the grammar test suite:

```bash
npm run test
```

Parse a template file:

```bash
npm run parse example.folio
```

## Grammar Coverage

- Elements: `page`, `column`, `row`, `text`, `heading`, `table`, `tr`, `td`, `th`
- Page chrome: `pageheader`, `pagefooter`, `pagenum`
- Control flow: `if` / `else` / `elseif`, `foreach`
- Declarations: `var`, `prop`
- Expressions: comparison, logical operators, dot notation
- Comments: single-line `//`

## Who Uses It

The grammar is used by:

- The [VS Code extension](./vscode.md) for syntax highlighting
- The [Language Server](./lsp.md) for parsing and analysis
- Any editor with Tree-sitter support

## Updating the Grammar

1. Edit `grammar.js`.
2. Run `npm run generate`.
3. Run `npm run test`.
4. Update the VS Code extension and LSP with the generated parser.
