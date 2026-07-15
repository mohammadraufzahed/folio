<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

use Folio\Pdf\Contracts\TemplateCompiler;
use Folio\Pdf\Document\Pdf;

final class PhpTemplateCompiler implements TemplateCompiler
{
    private readonly string $cacheDir;

    private bool $strict = false;

    private array $runtimeCache = [];

    private ?string $baseDir = null;

    private array $partialDirs = [];

    public function __construct(string $cacheDir = '/tmp/folio-pdf-cache')
    {
        $this->cacheDir = $cacheDir;

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, recursive: true);
        }
    }

    public function setStrict(bool $strict = true): void
    {
        $this->strict = $strict;
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
        if ($this->baseDir === null) {
            $this->baseDir = dirname($absolute);
        }
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

    public function clearRuntimeCache(): void
    {
        $this->runtimeCache = [];
    }

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
        if ($this->baseDir === null) {
            $this->baseDir = dirname($absolute);
        }
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
        $php .= "use Folio\\Pdf\\Nodes\\Text;\n";
        $php .= "use Folio\\Pdf\\Template\\Scope;\n";
        $php .= "use Folio\\Pdf\\Template\\AttributeMapper;\n\n";

        $defaults = [];
        foreach ($node->children as $child) {
            if ($child->type === 'VarDecl') {
                $name = (string) ($child->attributes['name'] ?? '');
                $defaultNode = $child->attributes['default'] ?? null;
                if ($name !== '' && $defaultNode instanceof AstNode) {
                    $defaults[$name] = $defaultNode->attributes['value'] ?? '';
                }
            }
        }

        $php .= "return static function (array \$data = []): Pdf {\n";
        if ($defaults !== []) {
            $defaultsExpr = var_export($defaults, true);
            $php .= "    \$data = array_merge({$defaultsExpr}, \$data);\n";
        }
        $strictArg = $this->strict ? 'true' : 'false';
        $php .= "    \$scope = new Scope(\$data, [], null, {$strictArg});\n\n";
        $php .= "    \$pdf = Pdf::make();\n\n";

        foreach ($node->children as $i => $child) {
            if ($child->type === 'VarDecl') {
                continue;
            }

            if ($child->type === 'PageChrome') {
                $php .= $this->generatePageChromeStatement($child, '    ');
                continue;
            }

            if ($child->type === 'Element' && ($child->attributes['type'] ?? '') === 'page') {
                $php .= "    \$pdf = \$pdf->{$this->generateExpression($child)};\n";
            } elseif ($child->type === 'Foreach') {
                $php .= $this->generateForeachStatement($child, '    ', true);
            } elseif ($child->type === 'Directive') {
                continue;
            } else {
                $content = $this->generateAsContent($child);
                $php .= "    \$pdf = \$pdf->page(Page::a4()->withContent({$content}));\n";
            }
        }

        $php .= "\n    return \$pdf;\n";
        $php .= "};\n";

        return $php;
    }

    private function generateAsContent(AstNode $node): string
    {
        if ($node->type === 'Block') {
            return 'Column::make(null, ' . $this->generateNodeList($node->children) . ')';
        }

        if ($node->type === 'If') {
            return 'Column::make(null, ' . $this->generateIfExpression($node) . ')';
        }

        return $this->generateExpression($node);
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
            'BinaryOp' => $this->generateBinaryOp($node),
            'UnaryOp' => $this->generateUnaryOp($node),
            'If' => $this->generateIfExpression($node),
            'Foreach' => $this->generateForeachExpression($node),
            'VarDecl' => $this->generateVarDecl($node),
            'Partial' => $this->generatePartial($node),
            'PageChrome' => 'null',
            'Directive' => 'null',
            default => 'null',
        };
    }

    private function generateBinaryOp(AstNode $node): string
    {
        $op = (string) ($node->attributes['op'] ?? '==');
        $left = $this->generateExpression($node->children[0]);
        $right = $this->generateExpression($node->children[1]);

        $phpOp = match ($op) {
            '==', '!=', '<', '<=', '>', '>=' => $op,
            'and' => '&&',
            'or' => '||',
            default => '==',
        };

        return "(({$left}) {$phpOp} ({$right}))";
    }

    private function generateUnaryOp(AstNode $node): string
    {
        $op = (string) ($node->attributes['op'] ?? 'not');
        $operand = $this->generateExpression($node->children[0]);

        $phpOp = match ($op) {
            'not' => '!',
            default => '!',
        };

        return "({$phpOp}({$operand}))";
    }

    private function generatePageChromeStatement(AstNode $node, string $indent): string
    {
        $kind = (string) ($node->attributes['kind'] ?? 'pageheader');
        $attrs = $node->attributes['attributes'] ?? [];
        if (!is_array($attrs)) {
            $attrs = [];
        }

        $method = $kind === 'pagefooter' ? 'pageFooter' : 'pageHeader';
        $parts = [];
        foreach ($attrs as $key => $value) {
            $key = (string) $key;
            if ($value instanceof AstNode) {
                $parts[] = var_export($key, true) . ' => (string)(' . $this->generateExpression($value) . ')';
            } elseif (is_string($value) || is_numeric($value)) {
                if ($value === 'true' || $value === 'false') {
                    $parts[] = var_export($key, true) . ' => ' . ($value === 'true' ? 'true' : 'false');
                } else {
                    $parts[] = var_export($key, true) . ' => ' . var_export((string) $value, true);
                }
            }
        }

        if ($node->children !== []) {
            $layoutExpr = $this->generateChromeNodeList($node->children);
            $parts[] = "'layout' => " . $layoutExpr;
            $method = $kind === 'pagefooter' ? 'pageFooter' : 'pageHeader';
        }

        $arrayExpr = '[' . implode(', ', $parts) . ']';
        return $indent . "\$pdf = \$pdf->{$method}({$arrayExpr});\n";
    }

    /**
     * @param array<int, AstNode> $children
     */
    private function generateChromeNodeList(array $children): string
    {
        $parts = [];
        foreach ($children as $child) {
            $parts[] = $this->generateChromeNode($child);
        }
        return '[' . implode(', ', $parts) . ']';
    }

    private function generateChromeNode(AstNode $node): string
    {
        return match ($node->type) {
            'Element' => $this->generateChromeElement($node),
            'Block' => $this->generateChromeNodeList($node->children),
            'StringLiteral' => "['type' => 'text', 'value' => " . $this->generateStringLiteral($node) . ", 'attrs' => [], 'children' => []]",
            'NumberLiteral' => "['type' => 'text', 'value' => (string)(" . $this->generateNumberLiteral($node) . "), 'attrs' => [], 'children' => []]",
            'Identifier' => "['type' => 'text', 'value' => (string)(" . $this->generateIdentifier($node) . "), 'attrs' => [], 'children' => []]",
            'PropertyAccess' => "['type' => 'text', 'value' => (string)(" . $this->generatePropertyAccess($node) . "), 'attrs' => [], 'children' => []]",
            'BinaryOp' => "['type' => 'text', 'value' => (string)(" . $this->generateBinaryOp($node) . "), 'attrs' => [], 'children' => []]",
            'UnaryOp' => "['type' => 'text', 'value' => (string)(" . $this->generateUnaryOp($node) . "), 'attrs' => [], 'children' => []]",
            'If' => $this->generateChromeIf($node),
            'Foreach' => $this->generateChromeForeach($node),
            default => "['type' => 'spacer', 'value' => '', 'attrs' => [], 'children' => []]",
        };
    }

    private function generateChromeElement(AstNode $node): string
    {
        $type = (string) ($node->attributes['type'] ?? 'text');
        $attrs = $node->attributes['attributes'] ?? [];
        if (!is_array($attrs)) {
            $attrs = [];
        }

        $attrParts = [];
        foreach ($attrs as $key => $value) {
            $key = (string) $key;
            if ($value instanceof AstNode) {
                $attrParts[] = var_export($key, true) . ' => (string)(' . $this->generateExpression($value) . ')';
            } elseif (is_string($value) || is_numeric($value)) {
                if ($value === 'true' || $value === 'false') {
                    $attrParts[] = var_export($key, true) . ' => ' . ($value === 'true' ? 'true' : 'false');
                } else {
                    $attrParts[] = var_export($key, true) . ' => ' . var_export((string) $value, true);
                }
            }
        }

        $valueExpr = "''";
        $childNodes = $node->children;
        if ($childNodes !== [] && in_array($childNodes[0]->type, ['StringLiteral', 'NumberLiteral', 'Identifier', 'PropertyAccess', 'BinaryOp', 'UnaryOp'], true)) {
            $valueExpr = '(string)(' . $this->generateExpression($childNodes[0]) . ')';
            $childNodes = array_slice($childNodes, 1);
        }

        $childrenExpr = $this->generateChromeNodeList($childNodes);

        return sprintf(
            "['type' => %s, 'value' => %s, 'attrs' => [%s], 'children' => %s]",
            var_export($type, true),
            $valueExpr,
            implode(', ', $attrParts),
            $childrenExpr
        );
    }

    private function generateChromeIf(AstNode $node): string
    {
        $condition = $node->attributes['condition'] ?? null;
        $condExpr = $condition instanceof AstNode ? $this->generateExpression($condition) : 'false';
        $then = $node->children[0] ?? null;
        $else = $node->children[1] ?? null;
        $thenExpr = $then ? $this->generateChromeNodeList($then->type === 'Block' ? $then->children : [$then]) : '[]';

        if ($else !== null && $else->type === 'If') {
            $elseExpr = $this->generateChromeIf($else);
        } elseif ($else !== null && $else->type === 'Block') {
            $elseExpr = $this->generateChromeNodeList($else->children);
        } else {
            $elseExpr = '[]';
        }

        return "(({$condExpr}) ? {$thenExpr} : {$elseExpr})";
    }

    private function generateChromeForeach(AstNode $node): string
    {
        $collection = $node->attributes['collection'] ?? null;
        $item = (string) ($node->attributes['item'] ?? 'item');
        $index = $node->attributes['index'] ?? null;
        $hasEmpty = (bool) ($node->attributes['hasEmpty'] ?? false);
        $collExpr = $collection instanceof AstNode ? $this->generateExpression($collection) : '[]';
        $body = $node->children[0] ?? null;
        $emptyBody = $hasEmpty ? ($node->children[1] ?? null) : null;
        $bodyExpr = $body ? $this->generateChromeNodeList($body->type === 'Block' ? $body->children : [$body]) : '[]';
        $emptyExpr = $emptyBody ? $this->generateChromeNodeList($emptyBody->type === 'Block' ? $emptyBody->children : [$emptyBody]) : '[]';

        $foreachHead = $index !== null
            ? "foreach ((array)({$collExpr}) as \$__key => \$__val)"
            : "foreach ((array)({$collExpr}) as \$__val)";

        $localsInit = $index !== null
            ? "'" . $index . "' => \$__key, '" . $item . "' => \$__val"
            : "'" . $item . "' => \$__val";

        $emptyCheck = $hasEmpty
            ? " if (\$__out === []) { \$__out = array_merge(\$__out, {$emptyExpr}); }"
            : '';

        return "(function(\Folio\Pdf\Template\Scope \$scope) { \$__out = []; \$__saved = \$scope; {$foreachHead} { \$scope = \$__saved->child([{$localsInit}]); \$__out = array_merge(\$__out, {$bodyExpr}); } \$scope = \$__saved;{$emptyCheck} return \$__out; })(\$scope)";
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
            'tr' => $this->generateTableRow($node, false),
            'header' => $this->generateTableRow($node, true),
            'footer' => $this->generateTableFooter($node),
            'th' => $this->generateTableCell($node, true),
            'td' => $this->generateTableCell($node, false),
            default => 'null',
        };
    }

    private function generatePage(AstNode $node): string
    {
        $content = $this->generateChildrenAsNode($node);
        $pageExpr = $this->generatePageExpr($node);
        return 'page(' . $pageExpr . '->withContent(' . $content . '))';
    }

    private function generatePageExpr(AstNode $node): string
    {
        $attrs = $node->attributes['attributes'] ?? [];
        $size = $attrs['size'] ?? null;
        $orientation = $attrs['orientation'] ?? 'portrait';

        $sizeStr = is_string($size) ? strtolower($size) : null;

        $factory = match ($sizeStr) {
            'a4', null, '' => 'Page::a4()',
            'letter' => 'Page::letter()',
            'a3' => 'Page::a3()',
            default => null,
        };

        if ($factory === null && $sizeStr !== null && preg_match('/^([\d.]+)\s*x\s*([\d.]+)$/', $sizeStr, $m)) {
            $w = (float) $m[1];
            $h = (float) $m[2];
            $factory = "Page::make({$w}, {$h})";
        }

        if ($factory === null) {
            $factory = 'Page::a4()';
        }

        if (is_string($orientation) && strtolower($orientation) === 'landscape') {
            $factory = "({$factory})->withSize(({$factory})->height(), ({$factory})->width())";
            if ($sizeStr === 'a4' || $sizeStr === null || $sizeStr === '') {
                $factory = 'Page::make(842.0, 595.0)';
            } elseif ($sizeStr === 'letter') {
                $factory = 'Page::make(792.0, 612.0)';
            } elseif ($sizeStr === 'a3') {
                $factory = 'Page::make(1191.0, 842.0)';
            }
        }

        return $factory;
    }

    private function generateColumn(AstNode $node): string
    {
        $styleExpr = $this->generateStyleExpr($node);
        return 'Column::make(' . $styleExpr . ', ' . $this->generateChildrenArray($node) . ')';
    }

    private function generateRow(AstNode $node): string
    {
        $styleExpr = $this->generateStyleExpr($node);
        return 'Row::make(' . $styleExpr . ', ' . $this->generateChildrenArray($node) . ')';
    }

    private function generateText(AstNode $node): string
    {
        $styleExpr = $this->generateStyleExpr($node);
        return 'Text::make((string)(' . $this->generateInlineValue($node) . '), ' . $styleExpr . ')';
    }

    private function generateHeading(AstNode $node): string
    {
        $styleExpr = $this->generateStyleExpr($node);
        return 'Heading::make((string)(' . $this->generateInlineValue($node) . '), 1, ' . $styleExpr . ')';
    }

    private function generateTable(AstNode $node): string
    {
        return 'Table::simple(' . $this->generateChildrenArray($node) . ')';
    }

    private function generateStyleExpr(AstNode $node): string
    {
        $attrs = $node->attributes['attributes'] ?? [];
        if (!is_array($attrs) || $attrs === []) {
            return 'null';
        }

        $styleKeys = [
            'color',
            'background',
            'fontSize',
            'fontWeight',
            'font',
            'padding',
            'margin',
            'align',
            'lineHeight',
            'letterSpacing',
            'opacity',
            'width',
            'height',
            'radius',
        ];

        $styleAttrs = [];
        foreach ($styleKeys as $key) {
            if (!array_key_exists($key, $attrs)) {
                continue;
            }
            $value = $attrs[$key];
            if ($value instanceof AstNode) {
                $styleAttrs[$key] = $this->generateExpression($value);
            } elseif (is_string($value) || is_numeric($value)) {
                if ($value === 'true' || $value === 'false') {
                    $styleAttrs[$key] = $value === 'true' ? 'true' : 'false';
                } else {
                    $styleAttrs[$key] = var_export((string) $value, true);
                }
            }
        }

        if ($styleAttrs === []) {
            return 'null';
        }

        $parts = [];
        foreach ($styleAttrs as $key => $expr) {
            $parts[] = var_export($key, true) . ' => ' . $expr;
        }

        return '\\Folio\\Pdf\\Template\\AttributeMapper::toStyle([' . implode(', ', $parts) . '])';
    }

    private function generateTableRow(AstNode $node, bool $isHeader): string
    {
        $method = $isHeader ? 'header' : 'make';
        return 'TableRow::' . $method . '(' . $this->generateChildrenArray($node) . ')';
    }

    private function generateTableFooter(AstNode $node): string
    {
        return 'TableRow::footer(' . $this->generateChildrenArray($node) . ')';
    }

    private function generateVarDecl(AstNode $node): string
    {

        return 'null';
    }

    private function generatePartial(AstNode $node): string
    {
        $path = (string) ($node->attributes['path'] ?? '');
        $escaped = var_export($path, true);

        $resolved = $this->resolvePartialPath($path);
        if ($resolved !== null) {
            $partialContent = file_get_contents($resolved);
            if ($partialContent !== false) {
                $lexer = new Lexer($partialContent);
                $parser = new Parser($lexer->tokenize());
                $partialAst = $parser->parse();

                $parts = [];
                foreach ($partialAst->children as $child) {
                    if ($child->type === 'VarDecl') {
                        continue;
                    }
                    $parts[] = $this->generateExpression($child);
                }

                if ($parts === []) {
                    return 'null';
                }

                if (count($parts) === 1) {
                    return $parts[0];
                }

                return 'Column::make(null, [' . implode(', ', $parts) . '])';
            }
        }

        return 'Text::make("[partial not found: ' . $path . ']")';
    }

    private function generateTableCell(AstNode $node, bool $isHeader): string
    {
        $attrs = $node->attributes['attributes'] ?? [];
        $rowSpan = (int) ($attrs['rowspan'] ?? $attrs['rowSpan'] ?? 1);
        $colSpan = (int) ($attrs['colspan'] ?? $attrs['colSpan'] ?? 1);
        $variant = $attrs['variant'] ?? $attrs['color'] ?? null;
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

        $variantExpr = $variant !== null ? ', ' . var_export((string) $variant, true) : '';
        return 'TableCell::make(' . $content . $variantExpr . ')';
    }

    private function generateCellContent(AstNode $node): string
    {
        if ($node->children === []) {
            return 'Text::make("")';
        }

        if (count($node->children) === 1) {
            $child = $node->children[0];
            if (in_array($child->type, ['StringLiteral', 'NumberLiteral', 'Identifier', 'PropertyAccess', 'BinaryOp', 'UnaryOp'], true)) {
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

    private function generateChildrenArray(AstNode $node): string
    {
        return $this->generateNodeList($node->children);
    }

    /**
     * @param array<int, AstNode> $children
     */
    private function generateNodeList(array $children): string
    {
        $children = array_values(array_filter(
            $children,
            static fn (AstNode $c): bool => $c->type !== 'VarDecl'
        ));

        if ($children === []) {
            return '[]';
        }

        $needsMerge = false;
        foreach ($children as $child) {
            if ($child->type === 'Foreach' || $child->type === 'If') {
                $needsMerge = true;
                break;
            }
        }

        if (!$needsMerge) {
            $parts = array_map(fn (AstNode $c) => $this->generateExpression($c), $children);
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
        return '$scope->getVar(' . var_export($name, true) . ')';
    }

    private function generatePropertyAccess(AstNode $node): string
    {

        $path = $node->attributes['path'] ?? [];
        if ($path === []) {
            return '""';
        }

        $parts = [];
        foreach ($path as $p) {
            $parts[] = var_export($p, true);
        }

        return '$scope->getPath([' . implode(', ', $parts) . '])';
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

        if ($else !== null && $else->type === 'If') {
            $elseExpr = $this->generateIfExpression($else);
        } elseif ($else !== null && $else->type === 'Block') {
            $elseExpr = $this->blockToArray($else);
        } else {
            $elseExpr = '[]';
        }

        return '((' . $condExpr . ') ? (' . $thenExpr . ') : (' . $elseExpr . '))';
    }

    private function generateForeachExpression(AstNode $node): string
    {
        $collection = $node->attributes['collection'] ?? null;
        $item = (string) ($node->attributes['item'] ?? 'item');
        $index = $node->attributes['index'] ?? null;
        $hasEmpty = (bool) ($node->attributes['hasEmpty'] ?? false);
        $body = $node->children[0] ?? null;
        $emptyBody = $hasEmpty ? ($node->children[1] ?? null) : null;

        $collExpr = $collection instanceof AstNode
            ? $this->generateExpression($collection)
            : '[]';

        $bodyArray = $body ? $this->blockToArray($body) : '[]';
        $emptyArray = $emptyBody ? $this->blockToArray($emptyBody) : '[]';

        $foreachHead = $index !== null
            ? 'foreach ($__source as $__key => $__val)'
            : 'foreach ($__source as $__val)';

        $localsInit = $index !== null
            ? "'" . $index . "' => \$__key, '" . $item . "' => \$__val"
            : "'" . $item . "' => \$__val";

        $emptyCheck = $hasEmpty
            ? "    if (\$__out === []) {\n        foreach ((array) ({$emptyArray}) as \$__node) { \$__out[] = \$__node; }\n    }\n"
            : '';

        return '(function (\Folio\Pdf\Template\Scope $scope) {
            $__source = ' . $collExpr . ';
            if (!is_array($__source)) {
                $__source = is_iterable($__source) ? iterator_to_array($__source) : [];
            }
            $__out = [];
            $__savedScope = $scope;
            ' . $foreachHead . ' {
                $scope = $__savedScope->child([' . $localsInit . ']);
                foreach ((array) (' . $bodyArray . ') as $__node) {
                    $__out[] = $__node;
                }
            }
            $scope = $__savedScope;
' . $emptyCheck . '            return $__out;
        })($scope)';
    }

    private function generateForeachStatement(AstNode $node, string $indent, bool $documentLevel): string
    {

        $collection = $node->attributes['collection'] ?? null;
        $item = (string) ($node->attributes['item'] ?? 'item');
        $index = $node->attributes['index'] ?? null;
        $body = $node->children[0] ?? null;
        $collExpr = $collection instanceof AstNode ? $this->generateExpression($collection) : '[]';

        $foreachHead = $index !== null
            ? "foreach ((is_iterable({$collExpr}) ? {$collExpr} : []) as \$__key => \$__val)"
            : "foreach ((is_iterable({$collExpr}) ? {$collExpr} : []) as \$__val)";

        $localsInit = $index !== null
            ? "'" . $index . "' => \$__key, '" . $item . "' => \$__val"
            : "'" . $item . "' => \$__val";

        $php = $indent . "\$__savedScope = \$scope;\n";
        $php .= $indent . $foreachHead . " {\n";
        $php .= $indent . '    $scope = $__savedScope->child([' . $localsInit . "]);\n";
        if ($body) {
            foreach ($body->children as $child) {
                if ($child->type === 'Element' && ($child->attributes['type'] ?? '') === 'page') {
                    $php .= $indent . "    \$pdf = \$pdf->{$this->generateExpression($child)};\n";
                } elseif ($child->type === 'Directive') {
                    continue;
                } else {
                    $content = $this->generateAsContent($child);
                    $php .= $indent . "    \$pdf = \$pdf->page(Page::a4()->withContent({$content}));\n";
                }
            }
        }
        $php .= $indent . "}\n";
        $php .= $indent . "\$scope = \$__savedScope;\n";

        return $php;
    }

    private function blockToArray(AstNode $block): string
    {
        if ($block->type === 'Block') {
            return $this->generateNodeList($block->children);
        }

        return '[' . $this->generateExpression($block) . ']';
    }

    public function setBaseDir(string $dir): void
    {
        $this->baseDir = $dir;
    }

    public function addPartialDir(string $dir): void
    {
        $this->partialDirs[] = $dir;
    }

    private function resolvePartialPath(string $path): ?string
    {
        if ($this->baseDir !== null) {
            $candidate = $this->baseDir . '/' . $path;
            if (!str_ends_with($candidate, '.folio')) {
                $candidate .= '.folio';
            }
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        foreach ($this->partialDirs as $dir) {
            $candidate = $dir . '/' . $path;
            if (!str_ends_with($candidate, '.folio')) {
                $candidate .= '.folio';
            }
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        if (is_file($path)) {
            return $path;
        }

        $withExt = $path;
        if (!str_ends_with($withExt, '.folio')) {
            $withExt .= '.folio';
        }
        if (is_file($withExt)) {
            return $withExt;
        }

        return null;
    }

    /**
     * @return array<int, array{name: string, keyword: string, default: mixed}>
     */
    public function extractDeclarations(string $template): array
    {
        $lexer = new Lexer($template);
        $tokens = $lexer->tokenize();
        $parser = new Parser($tokens);
        $ast = $parser->parse();

        $decls = [];
        $this->collectDecls($ast, $decls);

        return $decls;
    }

    private function collectDecls(AstNode $node, array &$decls): void
    {
        if ($node->type === 'VarDecl') {
            $name = (string) ($node->attributes['name'] ?? '');
            $keyword = (string) ($node->attributes['keyword'] ?? 'var');
            $defaultNode = $node->attributes['default'] ?? null;
            $default = '';
            if ($defaultNode instanceof AstNode) {
                $default = $defaultNode->attributes['value'] ?? '';
            }
            $decls[] = ['name' => $name, 'keyword' => $keyword, 'default' => $default];
        }

        foreach ($node->children as $child) {
            $this->collectDecls($child, $decls);
        }
    }
}
