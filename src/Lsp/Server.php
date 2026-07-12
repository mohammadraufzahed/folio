<?php

declare(strict_types=1);

namespace Folio\Pdf\Lsp;

use Folio\Pdf\Template\Lexer;
use Folio\Pdf\Template\Parser;
use Folio\Pdf\Template\TokenType;

/**
 * Language Server Protocol implementation for Folio PDF templates.
 * Speaks LSP over stdio using Content-Length framing.
 */
final class Server
{
    /** @var array<string, string> */
    private array $documents = [];

    private bool $shutdownRequested = false;

    public function start(): void
    {
        $this->log('LSP server started');

        while (!$this->shutdownRequested) {
            $message = $this->readMessage();
            if ($message === null) {
                break;
            }

            try {
                $request = json_decode($message, true, flags: JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $this->log('Invalid JSON: ' . $e->getMessage());
                continue;
            }

            $this->dispatch($request);
        }

        $this->log('LSP server stopped');
    }

    private function dispatch(array $request): void
    {
        $method = $request['method'] ?? null;
        $id = $request['id'] ?? null;
        $params = $request['params'] ?? [];

        // Notifications have no id
        if ($method === null) {
            return;
        }

        if ($id === null) {
            $this->handleNotification($method, $params);
            return;
        }

        $response = match ($method) {
            'initialize' => $this->initialize($id, $params),
            'shutdown' => $this->shutdown($id),
            'textDocument/completion' => $this->completion($id, $params),
            'textDocument/hover' => $this->hover($id, $params),
            'textDocument/formatting' => $this->formatting($id, $params),
            default => $this->methodNotFound($id, $method),
        };

        $this->send($response);
    }

    private function handleNotification(string $method, array $params): void
    {
        match ($method) {
            'initialized' => $this->log('Client initialized'),
            'exit' => $this->shutdownRequested = true,
            'textDocument/didOpen' => $this->didOpen($params),
            'textDocument/didChange' => $this->didChange($params),
            'textDocument/didClose' => $this->didClose($params),
            'textDocument/didSave' => null,
            default => $this->log("Unhandled notification: {$method}"),
        };
    }

    private function initialize(int|string $id, array $params): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => [
                'capabilities' => [
                    'textDocumentSync' => [
                        'openClose' => true,
                        'change' => 1, // Full
                    ],
                    'completionProvider' => [
                        'resolveProvider' => false,
                        'triggerCharacters' => [' ', '{', '@', '(', '"'],
                    ],
                    'hoverProvider' => true,
                    'documentFormattingProvider' => true,
                ],
                'serverInfo' => [
                    'name' => 'Folio PDF LSP',
                    'version' => '1.0.0',
                ],
            ],
        ];
    }

    private function shutdown(int|string $id): array
    {
        $this->shutdownRequested = true;
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => null,
        ];
    }

    private function didOpen(array $params): void
    {
        $uri = $params['textDocument']['uri'] ?? '';
        $text = $params['textDocument']['text'] ?? '';
        $this->documents[$uri] = $text;
        $this->publishDiagnostics($uri, $text);
    }

    private function didChange(array $params): void
    {
        $uri = $params['textDocument']['uri'] ?? '';
        $changes = $params['contentChanges'] ?? [];
        if ($changes === []) {
            return;
        }

        // Full document sync
        $text = $changes[array_key_last($changes)]['text'] ?? '';
        $this->documents[$uri] = $text;
        $this->publishDiagnostics($uri, $text);
    }

    private function didClose(array $params): void
    {
        $uri = $params['textDocument']['uri'] ?? '';
        unset($this->documents[$uri]);
    }

    private function completion(int|string $id, array $params): array
    {
        $items = [
            $this->item('page', 'Document page', 'page { $0 }', 14),
            $this->item('column', 'Vertical container', 'column { $0 }', 14),
            $this->item('row', 'Horizontal container', 'row { $0 }', 14),
            $this->item('text', 'Text content', 'text "$0"', 14),
            $this->item('heading', 'Heading', 'heading "$0"', 14),
            $this->item('table', 'Table', "table {\n    header {\n        th \"$1\"\n    }\n    tr {\n        td \"$0\"\n    }\n}", 14),
            $this->item('header', 'Table header row', "header {\n    th \"$0\"\n}", 14),
            $this->item('tr', 'Table row', "tr {\n    td \"$0\"\n}", 14),
            $this->item('th', 'Header cell', 'th "$0"', 14),
            $this->item('td', 'Data cell', 'td "$0"', 14),
            $this->item('if', 'Conditional', "if $1 {\n    $0\n}", 14),
            $this->item('foreach', 'Loop', "foreach $1 as $2 {\n    $0\n}", 14),
            $this->item('else', 'Else branch', "else {\n    $0\n}", 14),
            $this->item('@header', 'Page header directive', '@header', 3),
            $this->item('@footer', 'Page footer directive', '@footer', 3),
            $this->item('colspan', 'Column span attribute', 'colspan=$0', 10),
            $this->item('rowspan', 'Row span attribute', 'rowspan=$0', 10),
        ];

        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => [
                'isIncomplete' => false,
                'items' => $items,
            ],
        ];
    }

    private function item(string $label, string $detail, string $insert, int $kind): array
    {
        return [
            'label' => $label,
            'kind' => $kind,
            'detail' => $detail,
            'insertText' => $insert,
            'insertTextFormat' => 2, // Snippet
        ];
    }

    private function hover(int|string $id, array $params): array
    {
        $uri = $params['textDocument']['uri'] ?? '';
        $position = $params['position'] ?? [];
        $text = $this->documents[$uri] ?? '';
        $word = $this->wordAt($text, (int) ($position['line'] ?? 0), (int) ($position['character'] ?? 0));

        $docs = match ($word) {
            'page' => "**page** — Creates a document page\n\n```folio\npage {\n    column { ... }\n}\n```",
            'column' => "**column** — Vertical layout container\n\n```folio\ncolumn {\n    heading \"Title\"\n    text \"Body\"\n}\n```",
            'row' => "**row** — Horizontal layout container\n\n```folio\nrow { text \"A\" text \"B\" }\n```",
            'text' => "**text** — Text content\n\n```folio\ntext \"Hello\"\n```",
            'heading' => "**heading** — Heading element\n\n```folio\nheading \"Title\"\n```",
            'table' => "**table** — Table with rows and cells\n\n```folio\ntable {\n    header { th \"Name\" th \"Age\" }\n    tr { td \"John\" td \"30\" }\n}\n```",
            'header' => "**header** — Table header row\n\n```folio\nheader { th \"Col\" }\n```",
            'tr' => "**tr** — Table body row\n\n```folio\ntr { td \"value\" }\n```",
            'th' => "**th** — Header cell (supports colspan/rowspan)\n\n```folio\nth(colspan=2) \"Title\"\n```",
            'td' => "**td** — Data cell (can nest tables)\n\n```folio\ntd \"value\"\n```",
            'if' => "**if** — Conditional\n\n```folio\nif showDetails { text \"...\" }\n```",
            'foreach' => "**foreach** — Loop\n\n```folio\nforeach items as item { text item }\n```",
            default => $word === '' ? null : "No documentation for `{$word}`",
        };

        if ($docs === null) {
            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => null,
            ];
        }

        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => [
                'contents' => [
                    'kind' => 'markdown',
                    'value' => $docs,
                ],
            ],
        ];
    }

    private function formatting(int|string $id, array $params): array
    {
        $uri = $params['textDocument']['uri'] ?? '';
        $text = $this->documents[$uri] ?? '';
        $options = $params['options'] ?? [];
        $tabSize = (int) ($options['tabSize'] ?? 4);
        $formatted = $this->format($text, $tabSize);

        if ($formatted === $text) {
            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => [],
            ];
        }

        $lines = explode("\n", $text);
        $lastLine = max(0, count($lines) - 1);
        $lastChar = strlen($lines[$lastLine] ?? '');

        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => [[
                'range' => [
                    'start' => ['line' => 0, 'character' => 0],
                    'end' => ['line' => $lastLine, 'character' => $lastChar],
                ],
                'newText' => $formatted,
            ]],
        ];
    }

    private function format(string $template, int $indentSize): string
    {
        $indent = str_repeat(' ', $indentSize);
        $level = 0;
        $out = [];

        foreach (explode("\n", $template) as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                if ($out !== [] && end($out) !== '') {
                    $out[] = '';
                }
                continue;
            }

            if (str_starts_with($trimmed, '}')) {
                $level = max(0, $level - 1);
            }

            $out[] = str_repeat($indent, $level) . $trimmed;

            $opens = substr_count($trimmed, '{');
            $closes = substr_count($trimmed, '}');
            // Net open braces increase indent for following lines
            $net = $opens - $closes;
            if (!str_starts_with($trimmed, '}') && $net > 0) {
                $level += $net;
            } elseif (str_starts_with($trimmed, '}') && $opens > $closes) {
                $level += $opens - $closes;
            } elseif (!str_starts_with($trimmed, '}') && $net < 0) {
                // already handled closes at start; remaining net closes
            }
        }

        while ($out !== [] && end($out) === '') {
            array_pop($out);
        }

        return implode("\n", $out) . "\n";
    }

    private function publishDiagnostics(string $uri, string $text): void
    {
        $diagnostics = [];

        try {
            $lexer = new Lexer($text);
            $tokens = $lexer->tokenize();

            foreach ($tokens as $token) {
                if ($token->type === TokenType::Unknown) {
                    $pos = $this->offsetToPosition($text, $token->position);
                    $end = $this->offsetToPosition($text, $token->position + max(1, strlen($token->value)));
                    $diagnostics[] = [
                        'range' => ['start' => $pos, 'end' => $end],
                        'severity' => 1,
                        'source' => 'folio-pdf',
                        'message' => "Unknown token: {$token->value}",
                    ];
                }
            }

            try {
                $parser = new Parser($tokens);
                $parser->parse();
            } catch (\Throwable $e) {
                $diagnostics[] = [
                    'range' => [
                        'start' => ['line' => 0, 'character' => 0],
                        'end' => ['line' => 0, 'character' => 1],
                    ],
                    'severity' => 1,
                    'source' => 'folio-pdf',
                    'message' => 'Parse error: ' . $e->getMessage(),
                ];
            }
        } catch (\Throwable $e) {
            $diagnostics[] = [
                'range' => [
                    'start' => ['line' => 0, 'character' => 0],
                    'end' => ['line' => 0, 'character' => 1],
                ],
                'severity' => 1,
                'source' => 'folio-pdf',
                'message' => $e->getMessage(),
            ];
        }

        $this->send([
            'jsonrpc' => '2.0',
            'method' => 'textDocument/publishDiagnostics',
            'params' => [
                'uri' => $uri,
                'diagnostics' => $diagnostics,
            ],
        ]);
    }

    private function methodNotFound(int|string $id, string $method): array
    {
        // Don't error on unknown methods that are optional — return null result
        // for requests we don't implement, except true unknowns.
        $optional = [
            'workspace/didChangeConfiguration',
            'workspace/didChangeWatchedFiles',
            '$/cancelRequest',
            '$/setTrace',
        ];

        if (in_array($method, $optional, true) || str_starts_with($method, '$/')) {
            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => null,
            ];
        }

        $this->log("Method not found: {$method}");
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => [
                'code' => -32601,
                'message' => "Method not found: {$method}",
            ],
        ];
    }

    private function wordAt(string $text, int $line, int $character): string
    {
        $lines = explode("\n", $text);
        $lineText = $lines[$line] ?? '';
        if ($lineText === '') {
            return '';
        }

        $len = strlen($lineText);
        $i = min($character, $len);
        if ($i > 0 && $i < $len && !preg_match('/[A-Za-z_@]/', $lineText[$i])) {
            $i--;
        }

        $start = $i;
        while ($start > 0 && preg_match('/[A-Za-z0-9_@]/', $lineText[$start - 1])) {
            $start--;
        }
        $end = $i;
        while ($end < $len && preg_match('/[A-Za-z0-9_@]/', $lineText[$end])) {
            $end++;
        }

        return substr($lineText, $start, $end - $start);
    }

    private function offsetToPosition(string $text, int $offset): array
    {
        $offset = max(0, min($offset, strlen($text)));
        $before = substr($text, 0, $offset);
        $line = substr_count($before, "\n");
        $lastNl = strrpos($before, "\n");
        $character = $lastNl === false ? $offset : $offset - $lastNl - 1;

        return ['line' => $line, 'character' => $character];
    }

    private function readMessage(): ?string
    {
        $headers = '';
        while (true) {
            $line = fgets(STDIN);
            if ($line === false) {
                return null;
            }
            if ($line === "\r\n" || $line === "\n") {
                break;
            }
            $headers .= $line;
        }

        if (!preg_match('/Content-Length:\s*(\d+)/i', $headers, $m)) {
            $this->log('Missing Content-Length header');
            return null;
        }

        $length = (int) $m[1];
        $body = '';
        while (strlen($body) < $length) {
            $chunk = fread(STDIN, $length - strlen($body));
            if ($chunk === false || $chunk === '') {
                return null;
            }
            $body .= $chunk;
        }

        return $body;
    }

    private function send(array $message): void
    {
        $content = json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($content === false) {
            return;
        }

        $frame = 'Content-Length: ' . strlen($content) . "\r\n\r\n" . $content;
        fwrite(STDOUT, $frame);
        fflush(STDOUT);
    }

    private function log(string $message): void
    {
        fwrite(STDERR, '[folio-pdf-lsp] ' . $message . "\n");
    }
}
