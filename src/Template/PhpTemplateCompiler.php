<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

use Folio\Pdf\Contracts\TemplateCompiler;
use Folio\Pdf\Document\Pdf;

/**
 * Compiler that converts Folio templates to PHP code with data binding.
 *
 * Compiled templates return a callable: fn(array $data): Pdf
 *
 * File templates use mtime-based disk cache + in-memory renderer cache.
 */
final class PhpTemplateCompiler implements TemplateCompiler
{
    private readonly string $cacheDir;

    /** @var array<string, callable(array<string, mixed>): Pdf> */
    private array $runtimeCache = [];

    public function __construct(string $cacheDir = '/tmp/folio-pdf-cache')
    {
        $this->cacheDir = $cacheDir;

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, recursive: true);
        }
    }

    public function compile(string $template): string
    {
        $cachePath = $this->getCachePath($template);
        if (is_file($cachePath)) {
            $cached = file_get_contents($cachePath);
            if ($cached !== false && $cached !== '') {
                return $cached;
            }
        }

        $php = $this->compileFresh($template);
        file_put_contents($cachePath, $php);

        return $php;
    }

    public function compileFile(string $path): string
    {
        $absolute = $this->resolvePath($path);
        $cachePath = $this->getFileCachePath($absolute);

        if ($this->isFileCacheFresh($absolute, $cachePath)) {
            $cached = file_get_contents($cachePath);
            if ($cached !== false && $cached !== '') {
                return $cached;
            }
        }

        $template = file_get_contents($absolute);
        if ($template === false) {
            throw new \RuntimeException("Unable to read template: {$absolute}");
        }

        $php = $this->compileFresh($template);
        $this->writeFileCache($absolute, $cachePath, $php);

        return $php;
    }

    public function getCachePath(string $template): string
    {
        return $this->cacheDir . '/str_' . md5($template) . '.php';
    }

    /**
     * Compile and render a template file with data.
     * Uses mtime disk cache and in-process renderer cache.
     *
     * @param array<string, mixed> $data
     */
    public function renderFile(string $path, array $data = []): Pdf
    {
        $renderer = $this->loadFileRenderer($path);
        $pdf = $renderer($data);

        if (!$pdf instanceof Pdf) {
            throw new \RuntimeException('Compiled template must return a Pdf instance');
        }

        return $pdf;
    }

    /**
     * Compile and render a template string with data.
     *
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = []): Pdf
    {
        $cacheKey = 'str:' . md5($template);

        if (!isset($this->runtimeCache[$cacheKey])) {
            $cachePath = $this->getCachePath($template);
            if (!is_file($cachePath)) {
                file_put_contents($cachePath, $this->compileFresh($template));
            }

            /** @var mixed $renderer */
            $renderer = require $cachePath;
            if (!is_callable($renderer)) {
                throw new \RuntimeException('Compiled template did not return a callable');
            }

            $this->runtimeCache[$cacheKey] = $renderer;
        }

        $pdf = ($this->runtimeCache[$cacheKey])($data);
        if (!$pdf instanceof Pdf) {
            throw new \RuntimeException('Compiled template must return a Pdf instance');
        }

        return $pdf;
    }

    /**
     * Clear in-memory renderer cache (disk cache kept).
     */
    public function clearRuntimeCache(): void
    {
        $this->runtimeCache = [];
    }

    /**
     * Force recompile of a template file, ignoring mtime cache.
     */
    public function recompileFile(string $path): string
    {
        $absolute = $this->resolvePath($path);
        $template = file_get_contents($absolute);
        if ($template === false) {
            throw new \RuntimeException("Unable to read template: {$absolute}");
        }

        $php = $this->compileFresh($template);
        $cachePath = $this->getFileCachePath($absolute);
        $this->writeFileCache($absolute, $cachePath, $php);
        unset($this->runtimeCache['file:' . $absolute]);

        return $php;
    }

    /**
     * @return callable(array<string, mixed>): Pdf
     */
    private function loadFileRenderer(string $path): callable
    {
        $absolute = $this->resolvePath($path);
        $runtimeKey = 'file:' . $absolute;

        if (isset($this->runtimeCache[$runtimeKey])) {
            return $this->runtimeCache[$runtimeKey];
        }

        $cachePath = $this->getFileCachePath($absolute);

        if (!$this->isFileCacheFresh($absolute, $cachePath)) {
            $template = file_get_contents($absolute);
            if ($template === false) {
                throw new \RuntimeException("Unable to read template: {$absolute}");
            }

            $this->writeFileCache($absolute, $cachePath, $this->compileFresh($template));
        }

        /** @var mixed $renderer */
        $renderer = require $cachePath;
        if (!is_callable($renderer)) {
            throw new \RuntimeException('Compiled template did not return a callable');
        }

        $this->runtimeCache[$runtimeKey] = $renderer;

        return $renderer;
    }

    private function compileFresh(string $template): string
    {
        $lexer = new Lexer($template);
        $tokens = $lexer->tokenize();
        $parser = new Parser($tokens);
        $ast = $parser->parse();

        return $this->generatePhp($ast);
    }

    private function resolvePath(string $path): string
    {
        $absolute = realpath($path);
        if ($absolute === false || !is_file($absolute)) {
            throw new \RuntimeException("Template file not found: {$path}");
        }

        return $absolute;
    }

    private function getFileCachePath(string $absolutePath): string
    {
        return $this->cacheDir . '/file_' . md5($absolutePath) . '.php';
    }

    private function getFileMetaPath(string $absolutePath): string
    {
        return $this->cacheDir . '/file_' . md5($absolutePath) . '.meta.json';
    }

    private function isFileCacheFresh(string $absolutePath, string $cachePath): bool
    {
        if (!is_file($cachePath)) {
            return false;
        }

        $metaPath = $this->getFileMetaPath($absolutePath);
        if (!is_file($metaPath)) {
            return false;
        }

        $metaRaw = file_get_contents($metaPath);
        if ($metaRaw === false) {
            return false;
        }

        /** @var array{mtime?: int|float, size?: int}|null $meta */
        $meta = json_decode($metaRaw, true);
        if (!is_array($meta)) {
            return false;
        }

        $mtime = filemtime($absolutePath);
        $size = filesize($absolutePath);
        if ($mtime === false || $size === false) {
            return false;
        }

        return (int) ($meta['mtime'] ?? -1) === $mtime
            && (int) ($meta['size'] ?? -1) === $size;
    }

    private function writeFileCache(string $absolutePath, string $cachePath, string $php): void
    {
        $mtime = filemtime($absolutePath);
        $size = filesize($absolutePath);
        if ($mtime === false || $size === false) {
            throw new \RuntimeException("Unable to stat template: {$absolutePath}");
        }

        file_put_contents($cachePath, $php);
        file_put_contents($this->getFileMetaPath($absolutePath), json_encode([
            'path' => $absolutePath,
            'mtime' => $mtime,
            'size' => $size,
            'compiled_at' => time(),
        ], JSON_THROW_ON_ERROR));
    }

    private function generatePhp(AstNode $node): string
    {
        // Only Document is top-level full file
        if ($node->type === 'Document') {
            return $this->generateDocument($node);
        }

        return $this->generateExpression($node);
    }

    private function generateDocument(AstNode $node): string
    {
        $php = "<?php\n\n";
        $php .= "use Folio\\Pdf\\Document\\Pdf;\n";
        $php .= "use Folio\\Pdf\\Nodes\\Column;\n";
        $php .= "use Folio\\Pdf\\Nodes\\Heading;\n";
        $php .= "use Folio\\Pdf\\Nodes\\Page;\n";
        $php .= "use Folio\\Pdf\\Nodes\\Row;\n";
        $php .= "use Folio\\Pdf\\Nodes\\Table;\n";
        $php .= "use Folio\\Pdf\\Nodes\\TableCell;\n";
        $php .= "use Folio\\Pdf\\Nodes\\TableRow;\n";
        $php .= "use Folio\\Pdf\\Nodes\\Text;\n\n";

        $php .= "return static function (array \$data = []): Pdf {\n";
        $php .= "    extract(\$data, EXTR_SKIP);\n\n";
        $php .= "    \$pdf = Pdf::make();\n\n";

        foreach ($node->children as $i => $child) {
            $expr = $this->generateExpression($child);
            if ($child->type === 'Element' && ($child->attributes['type'] ?? '') === 'page') {
                $php .= "    \$pdf = \$pdf->{$expr};\n";
            } elseif ($child->type === 'Foreach') {
                // foreach at document level may emit multiple pages
                $php .= $this->generateForeachStatement($child, '    ', true);
            } else {
                $php .= "    \$pdf = \$pdf->page(Page::a4()->withContent({$expr}));\n";
            }
        }

        $php .= "\n    return \$pdf;\n";
        $php .= "};\n";

        return $php;
    }

    private function generateExpression(AstNode $node): string
    {
        return match ($node->type) {
            'Element' => $this->generateElement($node),
            'Block' => $this->generateNodeList($node->children),
            'StringLiteral' => $this->generateStringLiteral($node),
            'NumberLiteral' => $this->generateNumberLiteral($node),
            'Identifier' => $this->generateIdentifier($node),
            'PropertyAccess' => $this->generatePropertyAccess($node),
            'If' => $this->generateIfExpression($node),
            'Foreach' => $this->generateForeachExpression($node),
            'Directive' => 'null',
            default => 'null',
        };
    }

    private function generateElement(AstNode $node): string
    {
        $type = $node->attributes['type'] ?? 'unknown';

        return match ($type) {
            'page' => $this->generatePage($node),
            'column' => $this->generateColumn($node),
            'row' => $this->generateRow($node),
            'text' => $this->generateText($node),
            'heading' => $this->generateHeading($node),
            'table' => $this->generateTable($node),
            'tr', 'header' => $this->generateTableRow($node, $type === 'header'),
            'th' => $this->generateTableCell($node, true),
            'td' => $this->generateTableCell($node, false),
            default => 'null',
        };
    }

    private function generatePage(AstNode $node): string
    {
        $content = $this->generateChildrenAsNode($node);
        return 'page(Page::a4()->withContent(' . $content . '))';
    }

    private function generateColumn(AstNode $node): string
    {
        return 'Column::make(null, ' . $this->generateChildrenArray($node) . ')';
    }

    private function generateRow(AstNode $node): string
    {
        return 'Row::make(null, ' . $this->generateChildrenArray($node) . ')';
    }

    private function generateText(AstNode $node): string
    {
        return 'Text::make((string)(' . $this->generateInlineValue($node) . '))';
    }

    private function generateHeading(AstNode $node): string
    {
        return 'Heading::make((string)(' . $this->generateInlineValue($node) . '))';
    }

    private function generateTable(AstNode $node): string
    {
        return 'Table::simple(' . $this->generateChildrenArray($node) . ')';
    }

    private function generateTableRow(AstNode $node, bool $isHeader): string
    {
        $method = $isHeader ? 'header' : 'make';
        return 'TableRow::' . $method . '(' . $this->generateChildrenArray($node) . ')';
    }

    private function generateTableCell(AstNode $node, bool $isHeader): string
    {
        $attrs = $node->attributes['attributes'] ?? [];
        $rowSpan = (int) ($attrs['rowspan'] ?? $attrs['rowSpan'] ?? 1);
        $colSpan = (int) ($attrs['colspan'] ?? $attrs['colSpan'] ?? 1);
        $content = $this->generateCellContent($node);

        if ($isHeader) {
            return sprintf(
                'TableCell::header(%s, null, %d, %d)',
                $content,
                max(1, $rowSpan),
                max(1, $colSpan)
            );
        }

        if ($rowSpan > 1 || $colSpan > 1) {
            return sprintf(
                'TableCell::withSpan(%s, %d, %d)',
                $content,
                max(1, $rowSpan),
                max(1, $colSpan)
            );
        }

        return 'TableCell::make(' . $content . ')';
    }

    private function generateCellContent(AstNode $node): string
    {
        if ($node->children === []) {
            return 'Text::make("")';
        }

        if (count($node->children) === 1) {
            $child = $node->children[0];
            if (in_array($child->type, ['StringLiteral', 'NumberLiteral', 'Identifier', 'PropertyAccess'], true)) {
                return 'Text::make((string)(' . $this->generateExpression($child) . '))';
            }
            return $this->generateExpression($child);
        }

        return 'Column::make(null, ' . $this->generateChildrenArray($node) . ')';
    }

    private function generateChildrenAsNode(AstNode $node): string
    {
        if ($node->children === []) {
            return 'Text::make("")';
        }

        if (count($node->children) === 1 && $node->children[0]->type !== 'Foreach' && $node->children[0]->type !== 'If') {
            return $this->generateExpression($node->children[0]);
        }

        return 'Column::make(null, ' . $this->generateChildrenArray($node) . ')';
    }

    /**
     * Build a PHP array expression of child nodes, expanding foreach/if.
     */
    private function generateChildrenArray(AstNode $node): string
    {
        return $this->generateNodeList($node->children);
    }

    /**
     * @param array<int, AstNode> $children
     */
    private function generateNodeList(array $children): string
    {
        if ($children === []) {
            return '[]';
        }

        // If any child is Foreach/If, build via array_merge fragments
        $needsMerge = false;
        foreach ($children as $child) {
            if ($child->type === 'Foreach' || $child->type === 'If') {
                $needsMerge = true;
                break;
            }
        }

        if (!$needsMerge) {
            $parts = array_map(fn(AstNode $c) => $this->generateExpression($c), $children);
            return '[' . implode(', ', $parts) . ']';
        }

        $parts = [];
        foreach ($children as $child) {
            if ($child->type === 'Foreach') {
                $parts[] = $this->generateForeachExpression($child);
            } elseif ($child->type === 'If') {
                $parts[] = $this->generateIfExpression($child);
            } else {
                $parts[] = '[' . $this->generateExpression($child) . ']';
            }
        }

        if (count($parts) === 1) {
            return $parts[0];
        }

        return 'array_values(array_merge(' . implode(', ', $parts) . '))';
    }

    private function generateInlineValue(AstNode $node): string
    {
        if ($node->children === []) {
            return '""';
        }

        return $this->generateExpression($node->children[0]);
    }

    private function generateStringLiteral(AstNode $node): string
    {
        $value = addslashes((string) ($node->attributes['value'] ?? ''));
        return '"' . $value . '"';
    }

    private function generateNumberLiteral(AstNode $node): string
    {
        $value = (string) ($node->attributes['value'] ?? '0');
        return var_export($value, true);
    }

    private function generateIdentifier(AstNode $node): string
    {
        $name = (string) ($node->attributes['value'] ?? 'var');
        return '($' . $name . ' ?? ($data[' . var_export($name, true) . '] ?? ""))';
    }


    private function generatePropertyAccess(AstNode $node): string
    {
        /** @var array<int, string> $path */
        $path = $node->attributes['path'] ?? [];
        if ($path === []) {
            return '""';
        }

        $parts = [];
        foreach ($path as $p) {
            $parts[] = var_export($p, true);
        }

        // Fast helper: locals first (foreach $row), then $data
        return '\\Folio\\Pdf\\Template\\Runtime::get($data, [' . implode(', ', $parts) . '], get_defined_vars())';
    }


    private function generateIfExpression(AstNode $node): string
    {
        $condition = $node->attributes['condition'] ?? null;
        $condExpr = $condition instanceof AstNode
            ? $this->generateExpression($condition)
            : 'false';

        $then = $node->children[0] ?? null;
        $else = $node->children[1] ?? null;

        $thenExpr = $then ? $this->blockToArray($then) : '[]';
        $elseExpr = $else ? $this->blockToArray($else) : '[]';

        return '((' . $condExpr . ') ? (' . $thenExpr . ') : (' . $elseExpr . '))';
    }

    private function generateForeachExpression(AstNode $node): string
    {
        $collection = $node->attributes['collection'] ?? null;
        $item = (string) ($node->attributes['item'] ?? 'item');
        $body = $node->children[0] ?? null;

        $collExpr = $collection instanceof AstNode
            ? $this->generateExpression($collection)
            : '[]';

        $bodyArray = $body ? $this->blockToArray($body) : '[]';

        return '(function (array $data) {
            extract($data, EXTR_SKIP);
            $__source = ' . $collExpr . ';
            if (!is_array($__source)) {
                $__source = is_iterable($__source) ? iterator_to_array($__source) : [];
            }
            $__out = [];
            foreach ($__source as $' . $item . ') {
                foreach ((array) (' . $bodyArray . ') as $__node) {
                    $__out[] = $__node;
                }
            }
            return $__out;
        })($data)';
    }


    private function generateForeachStatement(AstNode $node, string $indent, bool $documentLevel): string
    {
        // Document-level foreach that may create pages
        $collection = $node->attributes['collection'] ?? null;
        $item = (string) ($node->attributes['item'] ?? 'item');
        $body = $node->children[0] ?? null;
        $collExpr = $collection instanceof AstNode ? $this->generateExpression($collection) : '[]';

        $php = $indent . "foreach ((is_iterable({$collExpr}) ? {$collExpr} : []) as \${$item}) {\n";
        if ($body) {
            foreach ($body->children as $child) {
                $expr = $this->generateExpression($child);
                if ($child->type === 'Element' && ($child->attributes['type'] ?? '') === 'page') {
                    $php .= $indent . "    \$pdf = \$pdf->{$expr};\n";
                } else {
                    $php .= $indent . "    \$pdf = \$pdf->page(Page::a4()->withContent({$expr}));\n";
                }
            }
        }
        $php .= $indent . "}\n";

        return $php;
    }

    private function blockToArray(AstNode $block): string
    {
        if ($block->type === 'Block') {
            return $this->generateNodeList($block->children);
        }

        return '[' . $this->generateExpression($block) . ']';
    }

    private function safeVarList(): string
    {
        // Variables commonly extracted; use $data only in use() for nested closures
        return 'data';
    }
}
