# Tooling

Folio 2.0 ships with first-class developer tools that work without a PHP
runtime: a TypeScript language server, a VS Code extension, a CLI, and a benchmark
harness.

## Language Server (LSP)

The Folio 2.0 language server is written in TypeScript and runs as a Node
process inside the VS Code extension. It no longer depends on a local PHP binary.

Features:

- **Diagnostics** — unknown tokens, unterminated strings and mismatched braces
  are shown as you type.
- **Completions** — element names (`page`, `column`, `table`, `foreach`, …) and
  common attributes (`background`, `fontSize`, `align`, `padding`) are suggested.
- **Hover** — hover any keyword to see a short description and a code snippet.
- **Formatting** — format the whole document with configurable indentation.
- **Document symbols** — `prop` declarations and `@use` partials appear in the
  outline.
- **Go to partial** — `Ctrl/Cmd + Click` on a `@use` path opens the included file.

## VS Code extension

Install the extension from the marketplace (search **Folio PDF**) or from the
VSIX attached to releases.

What it provides:

- Syntax highlighting for `.folio` and `.pdf-template` files.
- In-editor diagnostics, completion, hover and formatting.
- A command palette action to restart the language server.

The extension bundles the TypeScript language server, so it works as soon as it
is installed. There is no need to configure a `php` path.

## CLI

See the [CLI guide](./cli.md) for the full command reference.

## Benchmarks

See the [Benchmarks guide](./benchmarks.md).

## Reporting issues

If the language server, extension or CLI misbehaves, include:

- Extension or Folio version.
- A minimal `.folio` file that reproduces the problem.
- The output shown in `Output → Folio PDF LSP`.
