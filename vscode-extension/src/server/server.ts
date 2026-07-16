import {
    createConnection,
    TextDocuments,
    InitializeParams,
    TextDocumentSyncKind,
    CompletionItem,
    CompletionItemKind,
    Hover,
    TextEdit,
    DocumentSymbol,
    SymbolKind,
    DocumentLink,
    Range,
    Diagnostic,
    DiagnosticSeverity,
} from 'vscode-languageserver/node';
import { TextDocument } from 'vscode-languageserver-textdocument';
import * as path from 'path';
import { tokenize, TokenType, Token, getWordAt, getTokenAt } from './lexer';

const connection = createConnection();
const documents = new TextDocuments(TextDocument);

interface PropInfo {
    name: string;
    range: Range;
}

interface PartialInfo {
    path: string;
    range: Range;
}

documents.listen(connection);

connection.onInitialize((_params: InitializeParams) => {
    return {
        capabilities: {
            textDocumentSync: {
                openClose: true,
                change: TextDocumentSyncKind.Incremental,
            },
            completionProvider: {
                resolveProvider: false,
                triggerCharacters: [' ', '{', '@', '(', '"'],
            },
            hoverProvider: true,
            documentFormattingProvider: true,
            documentSymbolProvider: true,
            documentLinkProvider: {},
        },
        serverInfo: {
            name: 'Folio PDF LSP',
            version: '0.4.0',
        },
    };
});

connection.onCompletion((params): CompletionItem[] => {
    const doc = documents.get(params.textDocument.uri);
    if (!doc) {
        return [];
    }
    const text = doc.getText();
    const offset = doc.offsetAt(params.position);
    const before = text.slice(0, offset);
    const lastWord = before.match(/\b([A-Za-z_]\w*)$/)?.[1] ?? '';
    const afterParen = /\(\s*[^)]*$/.test(before);

    const elementItems: CompletionItem[] = [
        { label: 'page', kind: CompletionItemKind.Keyword, detail: 'Document page', insertText: 'page {\n\t$0\n}' },
        { label: 'column', kind: CompletionItemKind.Keyword, detail: 'Vertical container', insertText: 'column($1) {\n\t$0\n}' },
        { label: 'row', kind: CompletionItemKind.Keyword, detail: 'Horizontal container', insertText: 'row($1) {\n\t$0\n}' },
        { label: 'text', kind: CompletionItemKind.Keyword, detail: 'Text content', insertText: 'text($1) "$0"' },
        { label: 'heading', kind: CompletionItemKind.Keyword, detail: 'Heading', insertText: 'heading($1) "$0"' },
        { label: 'table', kind: CompletionItemKind.Keyword, detail: 'Table', insertText: 'table($1) {\n\theader { th "$2" }\n\ttr { td "$0" }\n}' },
        { label: 'header', kind: CompletionItemKind.Keyword, detail: 'Table header row', insertText: 'header {\n\tth "$0"\n}' },
        { label: 'footer', kind: CompletionItemKind.Keyword, detail: 'Table footer row', insertText: 'footer {\n\ttd "$0"\n}' },
        { label: 'tr', kind: CompletionItemKind.Keyword, detail: 'Table row', insertText: 'tr {\n\ttd "$0"\n}' },
        { label: 'th', kind: CompletionItemKind.Keyword, detail: 'Header cell', insertText: 'th($1) "$0"' },
        { label: 'td', kind: CompletionItemKind.Keyword, detail: 'Data cell', insertText: 'td($1) "$0"' },
        { label: 'if', kind: CompletionItemKind.Keyword, detail: 'Conditional', insertText: 'if ${1:condition} {\n\t$0\n}' },
        { label: 'foreach', kind: CompletionItemKind.Keyword, detail: 'Loop', insertText: 'foreach ${1:items} as ${2:item} {\n\t$0\n}' },
        { label: 'prop', kind: CompletionItemKind.Keyword, detail: 'Declare data prop', insertText: 'prop ${1:name} = ${2:""}' },
        { label: '@use', kind: CompletionItemKind.Keyword, detail: 'Include partial', insertText: '@use "${1:partial.folio}"' },
    ];

    const attributeItems: CompletionItem[] = [
        { label: 'background', kind: CompletionItemKind.Property, detail: 'Background color (#hex)', insertText: 'background="#$1"' },
        { label: 'color', kind: CompletionItemKind.Property, detail: 'Text color (#hex)', insertText: 'color="#$1"' },
        { label: 'fontSize', kind: CompletionItemKind.Property, detail: 'Font size in points', insertText: 'fontSize=$1' },
        { label: 'fontWeight', kind: CompletionItemKind.Property, detail: 'bold or normal', insertText: 'fontWeight=${1|bold,normal|}' },
        { label: 'align', kind: CompletionItemKind.Property, detail: 'left | right | center | justify', insertText: 'align=${1|left,right,center,justify|}' },
        { label: 'padding', kind: CompletionItemKind.Property, detail: 'Padding in points', insertText: 'padding=$1' },
        { label: 'gap', kind: CompletionItemKind.Property, detail: 'Gap between children', insertText: 'gap=$1' },
        { label: 'width', kind: CompletionItemKind.Property, detail: 'Width in points or %', insertText: 'width=${1|100%,|}$2' },
        { label: 'height', kind: CompletionItemKind.Property, detail: 'Height in points', insertText: 'height=$1' },
        { label: 'grow', kind: CompletionItemKind.Property, detail: 'Flex grow factor', insertText: 'grow=$1' },
        { label: 'colspan', kind: CompletionItemKind.Property, detail: 'Column span', insertText: 'colspan=$1' },
    ];

    if (afterParen) {
        return [...attributeItems, ...elementItems];
    }

    if (lastWord === '') {
        return elementItems;
    }

    return [...elementItems, ...attributeItems].filter(
        (item) => item.label.toLowerCase().startsWith(lastWord.toLowerCase())
    );
});

connection.onHover((params): Hover | null => {
    const doc = documents.get(params.textDocument.uri);
    if (!doc) {
        return null;
    }
    const text = doc.getText();
    const word = getWordAt(text, params.position.line, params.position.character);
    if (!word) {
        return null;
    }

    const docs: Record<string, string> = {
        page: 'Creates a document page. Use `size` and `background` attributes.',
        column: 'Vertical flex container. Use `gap`, `padding`, `align`, `width` and `grow`.',
        row: 'Horizontal flex container. Use `gap`, `padding`, `align`, `width`.',
        text: 'A paragraph of text. Supports `color`, `fontSize`, `fontWeight`, `align`.',
        heading: 'A heading. Optional `level=N` sets the heading level.',
        table: 'A table. Children: `header`, `tr` and `td`/`th` cells.',
        header: 'A table header row.',
        footer: 'A table footer row.',
        tr: 'A table data row.',
        th: 'A table header cell. Use `colspan` and `align`.',
        td: 'A table data cell. Use `colspan` and `align`.',
        if: 'Conditional block. Supports `else`.',
        foreach: 'Loop: `foreach items as item { ... }`.',
        prop: 'Declares a template prop with an optional default value.',
        partial: 'Inline another `.folio` template.',
    };

    const content = docs[word];
    if (!content) {
        return null;
    }
    return { contents: { kind: 'markdown', value: `**${word}** — ${content}` } };
});

connection.onDocumentFormatting((params): TextEdit[] => {
    const doc = documents.get(params.textDocument.uri);
    if (!doc) {
        return [];
    }
    const text = doc.getText();
    const tabSize = params.options.tabSize || 4;
    const indent = ' '.repeat(tabSize);
    const lines = text.split('\n');
    const out: string[] = [];
    let level = 0;

    for (const rawLine of lines) {
        const trimmed = rawLine.trim();
        if (trimmed === '') {
            if (out.length > 0 && out[out.length - 1] !== '') {
                out.push('');
            }
            continue;
        }

        if (trimmed.startsWith('}')) {
            level = Math.max(0, level - 1);
        }

        out.push(indent.repeat(level) + trimmed);

        const opens = (trimmed.match(/\{/g) || []).length;
        const closes = (trimmed.match(/\}/g) || []).length;
        const net = opens - closes;

        if (!trimmed.startsWith('}') && net > 0) {
            level += net;
        } else if (trimmed.startsWith('}') && net > 0) {
            level += net;
        }
    }

    while (out.length > 0 && out[out.length - 1] === '') {
        out.pop();
    }

    const formatted = out.join('\n') + '\n';
    if (formatted === text) {
        return [];
    }
    const lastLine = Math.max(0, lines.length - 1);
    const lastChar = lines[lastLine]?.length ?? 0;

    return [
        {
            range: {
                start: { line: 0, character: 0 },
                end: { line: lastLine, character: lastChar },
            },
            newText: formatted,
        },
    ];
});

connection.onDocumentSymbol((params): DocumentSymbol[] => {
    const doc = documents.get(params.textDocument.uri);
    if (!doc) {
        return [];
    }
    const text = doc.getText();
    const { tokens } = tokenize(text);
    const symbols: DocumentSymbol[] = [];

    for (let i = 0; i < tokens.length; i++) {
        const token = tokens[i];
        if (token.type === TokenType.Keyword && token.value === 'prop' && tokens[i + 1]?.type === TokenType.Identifier) {
            const nameToken = tokens[i + 1];
            symbols.push({
                name: nameToken.value,
                kind: SymbolKind.Property,
                range: {
                    start: { line: token.line, character: token.character },
                    end: { line: nameToken.line, character: nameToken.character + nameToken.value.length },
                },
                selectionRange: {
                    start: { line: nameToken.line, character: nameToken.character },
                    end: { line: nameToken.line, character: nameToken.character + nameToken.value.length },
                },
            });
        }
        if (token.type === TokenType.Directive && token.value === '@use' && tokens[i + 1]?.type === TokenType.String) {
            const pathToken = tokens[i + 1];
            const value = pathToken.value.slice(1, -1); // remove quotes
            symbols.push({
                name: value,
                kind: SymbolKind.Module,
                range: {
                    start: { line: token.line, character: token.character },
                    end: { line: pathToken.line, character: pathToken.character + pathToken.value.length },
                },
                selectionRange: {
                    start: { line: pathToken.line, character: pathToken.character },
                    end: { line: pathToken.line, character: pathToken.character + pathToken.value.length },
                },
            });
        }
    }

    return symbols;
});

connection.onDocumentLinks((params): DocumentLink[] => {
    const doc = documents.get(params.textDocument.uri);
    if (!doc) {
        return [];
    }
    const text = doc.getText();
    const { tokens } = tokenize(text);
    const links: DocumentLink[] = [];
    const docDir = doc.uri.startsWith('file:') ? path.dirname(doc.uri.replace('file:', '')) : '';

    for (let i = 0; i < tokens.length - 1; i++) {
        if (tokens[i].type === TokenType.Directive && tokens[i].value === '@use' && tokens[i + 1]?.type === TokenType.String) {
            const pathToken = tokens[i + 1];
            const raw = pathToken.value.slice(1, -1);
            const target = path.resolve(docDir, raw);
            const start = { line: pathToken.line, character: pathToken.character + 1 };
            const end = { line: pathToken.line, character: pathToken.character + pathToken.value.length - 1 };
            links.push({ range: { start, end }, target: `file://${target}` });
        }
    }

    return links;
});

function validate(text: string): Diagnostic[] {
    const { tokens, errors } = tokenize(text);
    const diagnostics: Diagnostic[] = errors.map((err) => ({
        range: {
            start: { line: err.line, character: err.character },
            end: { line: err.line, character: err.character + err.length },
        },
        severity: DiagnosticSeverity.Error,
        source: 'folio-pdf',
        message: err.message,
    }));

    const braceStack: Token[] = [];
    const parenStack: Token[] = [];

    for (const token of tokens) {
        if (token.type === TokenType.LeftBrace) {
            braceStack.push(token);
        } else if (token.type === TokenType.RightBrace) {
            if (braceStack.length === 0) {
                diagnostics.push({
                    range: {
                        start: { line: token.line, character: token.character },
                        end: { line: token.line, character: token.character + 1 },
                    },
                    severity: DiagnosticSeverity.Error,
                    source: 'folio-pdf',
                    message: 'Unexpected }',
                });
            } else {
                braceStack.pop();
            }
        } else if (token.type === TokenType.LeftParen) {
            parenStack.push(token);
        } else if (token.type === TokenType.RightParen) {
            if (parenStack.length === 0) {
                diagnostics.push({
                    range: {
                        start: { line: token.line, character: token.character },
                        end: { line: token.line, character: token.character + 1 },
                    },
                    severity: DiagnosticSeverity.Error,
                    source: 'folio-pdf',
                    message: 'Unexpected )',
                });
            } else {
                parenStack.pop();
            }
        }
    }

    for (const token of braceStack) {
        diagnostics.push({
            range: {
                start: { line: token.line, character: token.character },
                end: { line: token.line, character: token.character + 1 },
            },
            severity: DiagnosticSeverity.Error,
            source: 'folio-pdf',
            message: 'Unmatched {',
        });
    }
    for (const token of parenStack) {
        diagnostics.push({
            range: {
                start: { line: token.line, character: token.character },
                end: { line: token.line, character: token.character + 1 },
            },
            severity: DiagnosticSeverity.Error,
            source: 'folio-pdf',
            message: 'Unmatched (',
        });
    }

    return diagnostics;
}

async function sendDiagnostics(uri: string, text: string): Promise<void> {
    const diagnostics = validate(text);
    connection.sendDiagnostics({ uri, diagnostics });
}

documents.onDidOpen((event) => {
    void sendDiagnostics(event.document.uri, event.document.getText());
});

documents.onDidChangeContent((event) => {
    void sendDiagnostics(event.document.uri, event.document.getText());
});

documents.onDidClose((event) => {
    connection.sendDiagnostics({ uri: event.document.uri, diagnostics: [] });
});

connection.listen();
