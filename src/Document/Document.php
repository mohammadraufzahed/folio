<?php

declare(strict_types=1);

namespace Folio\Pdf\Document;

use Folio\Pdf\Contracts\Node;
use Folio\Pdf\Contracts\PdfWriter;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Row;
use Folio\Pdf\Nodes\Table;
use Folio\Pdf\Nodes\TableCell;
use Folio\Pdf\Nodes\TableRow;
use Folio\Pdf\Nodes\Text;

/**
 * PDF document with flow layout, page breaks, and efficient rendering.
 */
final class Document
{
    private const float MARGIN_X = 40.0;
    private const float MARGIN_TOP = 36.0;
    private const float MARGIN_BOTTOM = 40.0;
    private const float HEADER_BAND = 86.0;
    private const float FOOTER_BAND = 48.0;
    private const float BASE_ROW_HEIGHT = 18.0;
    private const float CELL_PAD_X = 5.0;
    private const float CELL_PAD_Y = 4.0;
    private const float TABLE_AFTER_GAP = 16.0;
    private const float HEADING_AFTER_GAP = 8.0;
    private const float TEXT_GAP = 12.0;
    private const float TITLE_GAP = 14.0;

    private readonly array $pages;

    /** @var array{title?: string, subtitle?: string, badge?: string, rightTitle?: string, rightSubtitle?: string}|null */
    private readonly ?array $pageHeader;

    /** @var array{left?: string, center?: string, right?: string, showPageNumber?: bool}|null */
    private readonly ?array $pageFooter;

    /** @var list<string> */
    private array $gBuf = [];
    /** @var list<string> */
    private array $tBuf = [];
    private int $tableStyleIndex = 0;

    /**
     * @param array<int, Page> $pages
     * @param array{title?: string, subtitle?: string, badge?: string, rightTitle?: string, rightSubtitle?: string}|null $pageHeader
     * @param array{left?: string, center?: string, right?: string, showPageNumber?: bool}|null $pageFooter
     */
    public function __construct(array $pages = [], ?array $pageHeader = null, ?array $pageFooter = null)
    {
        $this->pages = array_values($pages);
        $this->pageHeader = $pageHeader;
        $this->pageFooter = $pageFooter;
    }

    public static function make(): self
    {
        return new self();
    }

    public function addPage(Page $page): self
    {
        return new self([...$this->pages, $page], $this->pageHeader, $this->pageFooter);
    }

    /**
     * @param array{title?: string, subtitle?: string, badge?: string, rightTitle?: string, rightSubtitle?: string} $header
     */
    public function withPageHeader(array $header): self
    {
        return new self($this->pages, $header, $this->pageFooter);
    }

    /**
     * @param array{left?: string, center?: string, right?: string, showPageNumber?: bool} $footer
     */
    public function withPageFooter(array $footer): self
    {
        return new self($this->pages, $this->pageHeader, $footer);
    }

    /** @return array<int, Page> */
    public function pages(): array
    {
        return $this->pages;
    }

    public function pageCount(): int
    {
        return count($this->pages);
    }

    public function generate(): PdfWriter
    {
        $writer = new \Folio\Pdf\Pdf\PdfFileWriter();

        $blocks = [];
        $pageWidth = 595.0;
        $pageHeight = 842.0;

        foreach ($this->pages as $page) {
            $pageWidth = $page->width();
            $pageHeight = $page->height();
            if ($page->content() !== null) {
                $this->collectBlocks($page->content(), $blocks);
            }
        }

        $hasChrome = $this->pageHeader !== null || $this->pageFooter !== null;
        $topMargin = $hasChrome ? (self::MARGIN_TOP + self::HEADER_BAND) : self::MARGIN_TOP;
        $bottomMargin = $hasChrome ? (self::MARGIN_BOTTOM + self::FOOTER_BAND) : self::MARGIN_BOTTOM;

        $contentWidth = $pageWidth - (self::MARGIN_X * 2);
        $renderedPages = $this->flowBlocksToPages(
            $blocks,
            $pageWidth,
            $pageHeight,
            $contentWidth,
            $topMargin,
            $bottomMargin
        );

        if ($renderedPages === []) {
            $renderedPages = [''];
        }

        $totalPages = count($renderedPages);
        foreach ($renderedPages as $i => $content) {
            $pageNo = $i + 1;
            $chrome = $hasChrome
                ? $this->renderPageChrome($pageWidth, $pageHeight, $pageNo, $totalPages)
                : '';
            $writer->addPage($pageWidth, $pageHeight, $chrome . $content);
        }

        return $writer;
    }

    /** @param array<int, Node> $blocks */
    private function collectBlocks(Node $node, array &$blocks): void
    {
        if ($node instanceof Column) {
            foreach ($node->children() as $child) {
                $this->collectBlocks($child, $blocks);
            }
            return;
        }

        $blocks[] = $node;
    }

    /**
     * Draw branded header + footer chrome on every page.
     *
     * Header options:
     *  title, subtitle, badge, rightTitle, rightSubtitle, monogram,
     *  theme (navy|teal|slate|emerald|crimson|violet),
     *  style (bar|band|minimal|split),
     *  showMonogram (true|false), accent (hex or named)
     *
     * Footer options:
     *  left, center, right, showPageNumber, pageFormat ("Page {page} of {pages}"),
     *  style (rule|band|minimal), theme
     */
    private function renderPageChrome(float $pageWidth, float $pageHeight, int $pageNo, int $totalPages): string
    {
        $g = [];
        $t = ["BT\n/F1 12 Tf\n"];
        $x = self::MARGIN_X;
        $contentWidth = $pageWidth - (self::MARGIN_X * 2);

        if ($this->pageHeader !== null && $this->chromeVisible($this->pageHeader, $pageNo, $totalPages)) {
            if (isset($this->pageHeader['layout']) && is_array($this->pageHeader['layout'])) {
                $this->drawLayoutChrome(
                    $g,
                    $t,
                    $this->pageHeader,
                    true,
                    $pageWidth,
                    $pageHeight,
                    $x,
                    $contentWidth,
                    $pageNo,
                    $totalPages
                );
            } else {
                $this->drawHeaderChrome($g, $t, $pageWidth, $pageHeight, $x, $contentWidth);
            }
        }

        if ($this->pageFooter !== null && $this->chromeVisible($this->pageFooter, $pageNo, $totalPages)) {
            if (isset($this->pageFooter['layout']) && is_array($this->pageFooter['layout'])) {
                $this->drawLayoutChrome(
                    $g,
                    $t,
                    $this->pageFooter,
                    false,
                    $pageWidth,
                    $pageHeight,
                    $x,
                    $contentWidth,
                    $pageNo,
                    $totalPages
                );
            } else {
                $this->drawFooterChrome($g, $t, $pageWidth, $pageHeight, $x, $contentWidth, $pageNo, $totalPages);
            }
        }

        $t[] = "ET\n";
        return implode('', $g) . implode('', $t);
    }

    /**
     * @param array<string, mixed> $chrome
     */
    private function chromeVisible(array $chrome, int $pageNo, int $totalPages): bool
    {
        $only = strtolower((string) ($chrome['only'] ?? 'all'));
        return match ($only) {
            'first' => $pageNo === 1,
            'notfirst', 'rest' => $pageNo > 1,
            'last' => $pageNo === $totalPages,
            default => true,
        };
    }

    /**
     * Paint custom chrome layout tree (Mode B).
     *
     * @param list<string> $g
     * @param list<string> $t
     * @param array<string, mixed> $chrome
     */
    private function drawLayoutChrome(
        array &$g,
        array &$t,
        array $chrome,
        bool $isHeader,
        float $pageWidth,
        float $pageHeight,
        float $x,
        float $contentWidth,
        int $pageNo,
        int $totalPages
    ): void {
        $themeName = (string) ($chrome['theme'] ?? 'navy');
        $theme = $this->resolveChromeTheme($themeName, $chrome);
        $height = (float) ($chrome['height'] ?? ($isHeader ? self::HEADER_BAND - 14.0 : self::FOOTER_BAND));
        $height = max(28.0, min(160.0, $height));

        if ($isHeader) {
            $bandTop = $pageHeight - 10.0;
            $bandBottom = $bandTop - $height;
            $g[] = $this->pdfFillRect(0.0, $bandBottom, $pageWidth, $height, $theme['bg']);
            $g[] = $this->pdfFillRect(0.0, $bandBottom - 3.0, $pageWidth, 3.0, $theme['accent']);
            $originY = $bandTop;
            $areaBottom = $bandBottom;
        } else {
            $bandBottom = 8.0;
            $bandTop = $bandBottom + $height;
            $g[] = $this->pdfFillRect(0.0, 0.0, $pageWidth, $bandTop + 4.0, $theme['wash']);
            $g[] = $this->pdfFillRect(0.0, $bandTop, $pageWidth, 2.0, $theme['accent']);
            $originY = $bandTop - 4.0;
            $areaBottom = $bandBottom;
        }

        $layout = $chrome['layout'] ?? [];
        if (!is_array($layout)) {
            $layout = [];
        }
        $layout = $this->flattenChromeNodes($layout);

        $ctx = [
            'pageNo' => $pageNo,
            'totalPages' => $totalPages,
            'theme' => $theme,
            'isHeader' => $isHeader,
            'defaultColor' => $isHeader ? $theme['textLight'] : $theme['mutedDark'],
            'mutedColor' => $isHeader ? $theme['mutedLight'] : $theme['mutedDark'],
        ];

        $this->paintChromeNodes(
            $g,
            $t,
            $layout,
            $x,
            $originY,
            $contentWidth,
            max(20.0, $originY - $areaBottom),
            $ctx
        );

        $t[] = "0 0 0 rg\n/F1 12 Tf\n";
    }

    /**
     * @param array<int, mixed> $nodes
     * @return array<int, array<string, mixed>>
     */
    private function flattenChromeNodes(array $nodes): array
    {
        $out = [];
        foreach ($nodes as $n) {
            if (!is_array($n)) {
                continue;
            }
            if (isset($n['type'])) {
                $out[] = $n;
            } else {
                $isList = array_keys($n) === range(0, count($n) - 1);
                if ($isList) {
                    foreach ($this->flattenChromeNodes($n) as $child) {
                        $out[] = $child;
                    }
                }
            }
        }
        return $out;
    }

    /**
     * @param list<string> $g
     * @param list<string> $t
     * @param array<int, array<string, mixed>> $nodes
     * @param array<string, mixed> $ctx
     */
    private function paintChromeNodes(
        array &$g,
        array &$t,
        array $nodes,
        float $x,
        float $topY,
        float $width,
        float $height,
        array $ctx
    ): void {
        if ($nodes === []) {
            return;
        }

        if (count($nodes) === 1) {
            $this->paintChromeNode($g, $t, $nodes[0], $x, $topY, $width, $height, $ctx);
            return;
        }

        $y = $topY;
        $remaining = $height;
        foreach ($nodes as $node) {
            $h = min($remaining, $this->estimateChromeHeight($node, $width, $ctx));
            $this->paintChromeNode($g, $t, $node, $x, $y, $width, $h, $ctx);
            $y -= $h;
            $remaining -= $h;
            if ($remaining <= 0) {
                break;
            }
        }
    }

    /**
     * @param list<string> $g
     * @param list<string> $t
     * @param array<string, mixed> $node
     * @param array<string, mixed> $ctx
     */
    private function paintChromeNode(
        array &$g,
        array &$t,
        array $node,
        float $x,
        float $topY,
        float $width,
        float $height,
        array $ctx
    ): void {
        $type = (string) ($node['type'] ?? 'text');
        $attrs = is_array($node['attrs'] ?? null) ? $node['attrs'] : [];
        $value = (string) ($node['value'] ?? '');
        $children = $this->flattenChromeNodes(is_array($node['children'] ?? null) ? $node['children'] : []);
        /** @var array{bg: array{0:float,1:float,2:float}, accent: array{0:float,1:float,2:float}, accentSoft: array{0:float,1:float,2:float}, textLight: array{0:float,1:float,2:float}, textDark: array{0:float,1:float,2:float}, mutedLight: array{0:float,1:float,2:float}, mutedDark: array{0:float,1:float,2:float}} $theme */
        $theme = $ctx['theme'];

        $pad = (float) ($attrs['pad'] ?? 0);
        $padX = (float) ($attrs['padX'] ?? $pad);
        $padY = (float) ($attrs['padY'] ?? $pad);
        $gap = (float) ($attrs['gap'] ?? 8);
        $innerX = $x + $padX;
        $innerW = max(10.0, $width - $padX * 2);
        $innerTop = $topY - $padY;
        $innerH = max(8.0, $height - $padY * 2);

        if ($type === 'box' || isset($attrs['bg'])) {
            $bg = $this->resolveColorAttr((string) ($attrs['bg'] ?? ''), $theme, $theme['bg']);
            $g[] = $this->pdfFillRect($x, $topY - $height, $width, $height, $bg);
        }

        if ($type === 'row') {
            $align = (string) ($attrs['align'] ?? 'center');
            $n = max(1, count($children));
            $grows = [];
            $fixed = 0.0;
            foreach ($children as $i => $child) {
                $ca = is_array($child['attrs'] ?? null) ? $child['attrs'] : [];
                $grow = (float) ($ca['grow'] ?? 0);
                if ($grow > 0) {
                    $grows[$i] = $grow;
                } else {
                    $w = (float) ($ca['width'] ?? 0);
                    if ($w <= 0) {
                        $w = $innerW / $n;
                    }
                    $fixed += $w;
                    $grows[$i] = 0.0;
                }
            }
            $growTotal = array_sum($grows);
            $free = max(0.0, $innerW - $fixed - $gap * max(0, $n - 1));
            $cursor = $innerX;
            foreach ($children as $i => $child) {
                $ca = is_array($child['attrs'] ?? null) ? $child['attrs'] : [];
                if ($grows[$i] > 0 && $growTotal > 0) {
                    $cw = $free * ($grows[$i] / $growTotal);
                } else {
                    $cw = (float) ($ca['width'] ?? ($innerW / $n));
                }
                $cw = max(12.0, $cw);
                $this->paintChromeNode($g, $t, $child, $cursor, $innerTop, $cw, $innerH, $ctx);
                $cursor += $cw + $gap;
            }
            return;
        }

        if ($type === 'column') {
            $y = $innerTop;
            $remaining = $innerH;
            foreach ($children as $child) {
                $h = min($remaining, $this->estimateChromeHeight($child, $innerW, $ctx));
                $this->paintChromeNode($g, $t, $child, $innerX, $y, $innerW, $h, $ctx);
                $y -= $h + $gap * 0.35;
                $remaining -= $h + $gap * 0.35;
                if ($remaining <= 0) {
                    break;
                }
            }
            return;
        }

        if ($type === 'spacer') {
            return;
        }

        if ($type === 'rule') {
            $color = $this->resolveColorAttr((string) ($attrs['color'] ?? 'accent'), $theme, $theme['accent']);
            $y = $topY - $height / 2.0;
            $g[] = sprintf(
                "%.3f %.3f %.3f RG\n1 w\n%.2f %.2f m\n%.2f %.2f l\nS\n0 0 0 RG\n",
                $color[0],
                $color[1],
                $color[2],
                $x,
                $y,
                $x + $width,
                $y
            );
            return;
        }

        if ($type === 'monogram') {
            $label = strtoupper(substr($value !== '' ? $value : 'AR', 0, 3));
            $tile = min($height - 4.0, 30.0);
            $tile = max(20.0, $tile);
            $ty = $topY - $tile - max(0.0, ($height - $tile) / 2.0);
            $g[] = $this->pdfFillRect($x, $ty, $tile, $tile, $theme['accent']);
            $g[] = $this->pdfFillRect($x + 1.5, $ty + 1.5, $tile - 3.0, $tile - 3.0, $theme['accentSoft']);
            $t[] = $this->pdfSetRgb($theme['textLight']);
            $t[] = sprintf(
                "1 0 0 1 %.2f %.2f Tm\n/F1 11 Tf\n(%s) Tj\n",
                $x + 6.0,
                $ty + 9.0,
                $this->escapePdfString($label)
            );
            return;
        }

        if ($type === 'badge') {
            $label = strtoupper($value !== '' ? $value : 'BADGE');
            $bw = max(48.0, strlen($label) * 5.2 + 16.0);
            $bh = 13.0;
            $by = $topY - $bh - max(0.0, ($height - $bh) / 2.0);
            $align = (string) ($attrs['align'] ?? 'end');
            $bx = match ($align) {
                'start', 'left' => $x,
                'center' => $x + ($width - $bw) / 2.0,
                default => $x + max(0.0, $width - $bw),
            };
            $g[] = $this->pdfFillRect($bx, $by, $bw, $bh, $theme['accent']);
            $t[] = $this->pdfSetRgb($theme['textLight']);
            $t[] = sprintf(
                "1 0 0 1 %.2f %.2f Tm\n/F1 7 Tf\n(%s) Tj\n",
                $bx + 8.0,
                $by + 3.2,
                $this->escapePdfString($label)
            );
            return;
        }

        if ($type === 'pagenum') {
            $format = (string) ($attrs['format'] ?? 'Page {page} of {pages}');
            $value = str_replace(
                ['{page}', '{pages}', '{PAGE}', '{PAGES}'],
                [(string) $ctx['pageNo'], (string) $ctx['totalPages'], (string) $ctx['pageNo'], (string) $ctx['totalPages']],
                $format
            );
            $type = 'text';
        }

        $size = (int) ($attrs['size'] ?? ($type === 'heading' ? 14 : 10));
        $size = max(6, min(28, $size));
        $weight = (string) ($attrs['weight'] ?? ($type === 'heading' ? 'bold' : 'normal'));
        $colorKey = (string) ($attrs['color'] ?? '');
        $color = $this->resolveColorAttr(
            $colorKey,
            $theme,
            $colorKey === '' ? $ctx['defaultColor'] : $theme['textLight']
        );
        if ($colorKey === 'muted') {
            $color = $ctx['mutedColor'];
        }

        $align = (string) ($attrs['align'] ?? 'start');
        $label = $this->truncateText($value, $width, $size);
        $textW = strlen($label) * $size * 0.48;
        $tx = match ($align) {
            'center' => $x + max(0.0, ($width - $textW) / 2.0),
            'end', 'right' => $x + max(0.0, $width - $textW),
            default => $x,
        };
        $ty = $topY - max($size + 2.0, $height * 0.55);

        $t[] = $this->pdfSetRgb($color);
        $t[] = sprintf(
            "1 0 0 1 %.2f %.2f Tm\n/F1 %d Tf\n(%s) Tj\n",
            $tx,
            $ty,
            $size,
            $this->escapePdfString($label)
        );

        if ($children !== []) {
            $this->paintChromeNodes($g, $t, $children, $x, $topY - $size - 4.0, $width, max(8.0, $height - $size - 4.0), $ctx);
        }
    }

    /**
     * @param array<string, mixed> $node
     * @param array<string, mixed> $ctx
     */
    private function estimateChromeHeight(array $node, float $width, array $ctx): float
    {
        $type = (string) ($node['type'] ?? 'text');
        $attrs = is_array($node['attrs'] ?? null) ? $node['attrs'] : [];
        return match ($type) {
            'row', 'box' => (float) ($attrs['height'] ?? 36.0),
            'column' => (float) ($attrs['height'] ?? 48.0),
            'monogram' => 32.0,
            'badge' => 16.0,
            'rule' => 8.0,
            'spacer' => (float) ($attrs['height'] ?? 8.0),
            'heading' => (float) ($attrs['size'] ?? 14) + 8.0,
            default => (float) ($attrs['size'] ?? 10) + 6.0,
        };
    }

    /**
     * @param array{bg: array{0:float,1:float,2:float}, accent: array{0:float,1:float,2:float}, accentSoft: array{0:float,1:float,2:float}, textLight: array{0:float,1:float,2:float}, textDark: array{0:float,1:float,2:float}, mutedLight: array{0:float,1:float,2:float}, mutedDark: array{0:float,1:float,2:float}} $theme
     * @return array{0:float,1:float,2:float}
     */
    private function resolveColorAttr(string $value, array $theme, array $fallback): array
    {
        $value = trim($value);
        if ($value === '') {
            return $fallback;
        }
        $named = match (strtolower($value)) {
            'text', 'textlight' => $theme['textLight'],
            'textdark' => $theme['textDark'],
            'muted', 'mutedlight' => $theme['mutedLight'],
            'muteddark' => $theme['mutedDark'],
            'accent' => $theme['accent'],
            'accentsoft' => $theme['accentSoft'],
            'bg' => $theme['bg'],
            'white' => [1.0, 1.0, 1.0],
            'black' => [0.0, 0.0, 0.0],
            default => null,
        };
        if ($named !== null) {
            return $named;
        }
        $hex = $this->parseHexColor($value);
        return $hex ?? $fallback;
    }

    /** @return array{0:float,1:float,2:float}|null */
    private function parseHexColor(string $value): ?array
    {
        $value = ltrim(trim($value), '#');
        if (preg_match('/^[0-9a-fA-F]{3}$/', $value)) {
            $value = $value[0] . $value[0] . $value[1] . $value[1] . $value[2] . $value[2];
        }
        if (!preg_match('/^[0-9a-fA-F]{6}$/', $value)) {
            return null;
        }
        return [
            hexdec(substr($value, 0, 2)) / 255.0,
            hexdec(substr($value, 2, 2)) / 255.0,
            hexdec(substr($value, 4, 2)) / 255.0,
        ];
    }


    /**
     * @param list<string> $g
     * @param list<string> $t
     */
    private function drawHeaderChrome(array &$g, array &$t, float $pageWidth, float $pageHeight, float $x, float $contentWidth): void
    {
        $h = $this->pageHeader ?? [];
        $theme = $this->resolveChromeTheme((string) ($h['theme'] ?? 'navy'), $h);
        $style = strtolower((string) ($h['style'] ?? 'bar'));
        $title = (string) ($h['title'] ?? '');
        $subtitle = (string) ($h['subtitle'] ?? '');
        $badge = (string) ($h['badge'] ?? '');
        $rightTitle = (string) ($h['rightTitle'] ?? '');
        $rightSubtitle = (string) ($h['rightSubtitle'] ?? '');
        $monogram = (string) ($h['monogram'] ?? $this->makeMonogram($title));
        $showMonogram = $this->toBool($h['showMonogram'] ?? true);

        $bandTop = $pageHeight - 14.0;
        $bandHeight = match ($style) {
            'minimal' => 42.0,
            'split' => 58.0,
            'band' => 62.0,
            default => 56.0,
        };
        $bandBottom = $bandTop - $bandHeight;

        if ($style === 'minimal') {
            $g[] = $this->pdfFillRect(0.0, $bandBottom, $pageWidth, $bandHeight, $theme['wash']);
            $g[] = $this->pdfFillRect(0.0, $bandBottom - 2.5, $pageWidth, 2.5, $theme['accent']);
        } elseif ($style === 'band') {
            $g[] = $this->pdfFillRect(0.0, $bandBottom, $pageWidth, $bandHeight, $theme['bg']);
            $g[] = $this->pdfFillRect(0.0, $bandTop - 4.0, $pageWidth, 4.0, $theme['accentSoft']);
            $g[] = $this->pdfFillRect(0.0, $bandBottom - 3.0, $pageWidth, 3.0, $theme['accent']);
        } elseif ($style === 'split') {
            $split = $pageWidth * 0.62;
            $g[] = $this->pdfFillRect(0.0, $bandBottom, $split, $bandHeight, $theme['bg']);
            $g[] = $this->pdfFillRect($split, $bandBottom, $pageWidth - $split, $bandHeight, $theme['bgAlt']);
            $g[] = $this->pdfFillRect(0.0, $bandBottom - 3.0, $pageWidth, 3.0, $theme['accent']);
        } else {
            $g[] = $this->pdfFillRect(0.0, $bandBottom, $pageWidth, $bandHeight, $theme['bg']);
            $g[] = $this->pdfFillRect(0.0, $bandBottom - 3.0, $pageWidth, 3.0, $theme['accent']);
            $g[] = $this->pdfFillRect(0.0, $bandBottom, 5.0, $bandHeight, $theme['accent']);
        }

        $textColor = $style === 'minimal' ? $theme['textDark'] : $theme['textLight'];
        $mutedColor = $style === 'minimal' ? $theme['mutedDark'] : $theme['mutedLight'];

        $cursorX = $x;
        if ($showMonogram && $monogram !== '') {
            $tile = 28.0;
            $tileY = $bandTop - 40.0;
            $g[] = $this->pdfFillRect($cursorX, $tileY, $tile, $tile, $theme['accent']);
            $g[] = $this->pdfFillRect($cursorX + 1.5, $tileY + 1.5, $tile - 3.0, $tile - 3.0, $theme['accentSoft']);
            $t[] = $this->pdfSetRgb($theme['textLight']);
            $t[] = sprintf(
                "1 0 0 1 %.2f %.2f Tm\n/F1 11 Tf\n(%s) Tj\n",
                $cursorX + 6.0,
                $tileY + 9.0,
                $this->escapePdfString(strtoupper(substr($monogram, 0, 3)))
            );
            $cursorX += $tile + 10.0;
        }

        $titleY = $bandTop - 24.0;
        $subY = $bandTop - 40.0;
        $t[] = $this->pdfSetRgb($textColor);
        if ($title !== '') {
            $t[] = sprintf(
                "1 0 0 1 %.2f %.2f Tm\n/F1 15 Tf\n(%s) Tj\n",
                $cursorX,
                $titleY,
                $this->escapePdfString($title)
            );
        }
        if ($subtitle !== '') {
            $t[] = $this->pdfSetRgb($mutedColor);
            $t[] = sprintf(
                "1 0 0 1 %.2f %.2f Tm\n/F1 9 Tf\n(%s) Tj\n",
                $cursorX,
                $subY,
                $this->escapePdfString($subtitle)
            );
        }

        $rightColW = 150.0;
        $rightX = $pageWidth - self::MARGIN_X - $rightColW;
        $metaBottomReserve = $badge !== '' ? 18.0 : 0.0;

        if ($rightTitle !== '') {
            $t[] = $this->pdfSetRgb($textColor);
            $t[] = sprintf(
                "1 0 0 1 %.2f %.2f Tm\n/F1 10 Tf\n(%s) Tj\n",
                $rightX,
                $titleY,
                $this->escapePdfString($this->truncateText($rightTitle, $rightColW, 10))
            );
        }
        if ($rightSubtitle !== '') {
            $t[] = $this->pdfSetRgb($mutedColor);
            $t[] = sprintf(
                "1 0 0 1 %.2f %.2f Tm\n/F1 8 Tf\n(%s) Tj\n",
                $rightX,
                $subY,
                $this->escapePdfString($this->truncateText($rightSubtitle, $rightColW, 8))
            );
        }

        if ($badge !== '') {
            $badgeLabel = strtoupper($badge);
            $badgeW = max(52.0, strlen($badgeLabel) * 5.2 + 18.0);
            $badgeH = 13.0;
            $badgeX = $pageWidth - self::MARGIN_X - $badgeW;
            $badgeY = $bandBottom + 8.0;
            $g[] = $this->pdfFillRect($badgeX, $badgeY, $badgeW, $badgeH, $theme['accent']);
            $g[] = $this->pdfFillRect($badgeX, $badgeY, 3.0, $badgeH, $theme['accentSoft']);
            $t[] = $this->pdfSetRgb($theme['textLight']);
            $t[] = sprintf(
                "1 0 0 1 %.2f %.2f Tm\n/F1 7 Tf\n(%s) Tj\n",
                $badgeX + 8.0,
                $badgeY + 3.2,
                $this->escapePdfString($badgeLabel)
            );
        }

        $t[] = "0 0 0 rg\n/F1 12 Tf\n";
    }

    /**
     * @param list<string> $g
     * @param list<string> $t
     */
    private function drawFooterChrome(
        array &$g,
        array &$t,
        float $pageWidth,
        float $pageHeight,
        float $x,
        float $contentWidth,
        int $pageNo,
        int $totalPages
    ): void {
        $f = $this->pageFooter ?? [];
        $themeName = (string) ($f['theme'] ?? ($this->pageHeader['theme'] ?? 'navy'));
        $theme = $this->resolveChromeTheme($themeName, $f);
        $style = strtolower((string) ($f['style'] ?? 'rule'));
        $left = (string) ($f['left'] ?? '');
        $center = (string) ($f['center'] ?? '');
        $right = (string) ($f['right'] ?? '');
        $showPage = $this->toBool($f['showPageNumber'] ?? true);
        $pageFormat = (string) ($f['pageFormat'] ?? 'Page {page} of {pages}');

        $footerTop = 46.0;
        $footerY = 24.0;

        if ($style === 'band') {
            $g[] = $this->pdfFillRect(0.0, 0.0, $pageWidth, $footerTop + 6.0, $theme['wash']);
            $g[] = $this->pdfFillRect(0.0, $footerTop + 4.0, $pageWidth, 2.5, $theme['accent']);
        } elseif ($style === 'minimal') {
            $g[] = $this->pdfFillRect($x, $footerTop, 28.0, 1.5, $theme['accent']);
            $g[] = $this->pdfFillRect($x + $contentWidth - 28.0, $footerTop, 28.0, 1.5, $theme['accent']);
        } else {
            $g[] = sprintf(
                "%.3f %.3f %.3f RG\n0.7 w\n%.2f %.2f m\n%.2f %.2f l\nS\n0 0 0 RG\n",
                $theme['rule'][0],
                $theme['rule'][1],
                $theme['rule'][2],
                $x,
                $footerTop,
                $x + $contentWidth,
                $footerTop
            );
            $mid = $x + $contentWidth / 2.0;
            $g[] = $this->pdfFillRect($mid - 18.0, $footerTop - 0.5, 36.0, 2.0, $theme['accent']);
        }

        $t[] = $this->pdfSetRgb($theme['mutedDark']);

        if ($left !== '') {
            $t[] = sprintf(
                "1 0 0 1 %.2f %.2f Tm\n/F1 8 Tf\n(%s) Tj\n",
                $x,
                $footerY,
                $this->escapePdfString($this->truncateText($left, $contentWidth * 0.32, 8))
            );
        }

        if ($center !== '') {
            $approx = strlen($center) * 4.0;
            $t[] = sprintf(
                "1 0 0 1 %.2f %.2f Tm\n/F1 8 Tf\n(%s) Tj\n",
                $x + ($contentWidth - $approx) / 2.0,
                $footerY,
                $this->escapePdfString($center)
            );
        }

        $pageLabel = '';
        if ($showPage) {
            $pageLabel = str_replace(
                ['{page}', '{pages}', '{PAGE}', '{PAGES}'],
                [(string) $pageNo, (string) $totalPages, (string) $pageNo, (string) $totalPages],
                $pageFormat
            );
        }
        if ($right !== '' && $pageLabel !== '') {
            $pageLabel = $right . '  ·  ' . $pageLabel;
        } elseif ($right !== '') {
            $pageLabel = $right;
        }

        if ($pageLabel !== '') {
            $approxW = strlen($pageLabel) * 4.1;
            $t[] = sprintf(
                "1 0 0 1 %.2f %.2f Tm\n/F1 8 Tf\n(%s) Tj\n",
                $x + $contentWidth - $approxW,
                $footerY,
                $this->escapePdfString($pageLabel)
            );
        }

        $t[] = "0 0 0 rg\n/F1 12 Tf\n";
    }

    /**
     * @return array{
     *   bg: array{0:float,1:float,2:float},
     *   bgAlt: array{0:float,1:float,2:float},
     *   accent: array{0:float,1:float,2:float},
     *   accentSoft: array{0:float,1:float,2:float},
     *   wash: array{0:float,1:float,2:float},
     *   textLight: array{0:float,1:float,2:float},
     *   textDark: array{0:float,1:float,2:float},
     *   mutedLight: array{0:float,1:float,2:float},
     *   mutedDark: array{0:float,1:float,2:float},
     *   rule: array{0:float,1:float,2:float}
     * }
     */
    /**
     * @param array<string, mixed> $overrides
     */
    private function resolveChromeTheme(string $name, array $overrides = []): array
    {
        $themes = [
            'navy' => [
                'bg' => [0.10, 0.18, 0.32],
                'bgAlt' => [0.14, 0.24, 0.40],
                'accent' => [0.18, 0.48, 0.86],
                'accentSoft' => [0.28, 0.58, 0.92],
                'wash' => [0.94, 0.96, 0.99],
            ],
            'teal' => [
                'bg' => [0.07, 0.25, 0.28],
                'bgAlt' => [0.10, 0.32, 0.36],
                'accent' => [0.10, 0.62, 0.58],
                'accentSoft' => [0.22, 0.72, 0.68],
                'wash' => [0.93, 0.98, 0.97],
            ],
            'slate' => [
                'bg' => [0.18, 0.22, 0.28],
                'bgAlt' => [0.24, 0.28, 0.34],
                'accent' => [0.42, 0.50, 0.62],
                'accentSoft' => [0.55, 0.62, 0.72],
                'wash' => [0.96, 0.96, 0.97],
            ],
            'emerald' => [
                'bg' => [0.08, 0.24, 0.18],
                'bgAlt' => [0.12, 0.32, 0.24],
                'accent' => [0.16, 0.65, 0.42],
                'accentSoft' => [0.28, 0.75, 0.52],
                'wash' => [0.94, 0.98, 0.95],
            ],
            'crimson' => [
                'bg' => [0.30, 0.10, 0.14],
                'bgAlt' => [0.38, 0.14, 0.18],
                'accent' => [0.78, 0.22, 0.30],
                'accentSoft' => [0.88, 0.36, 0.42],
                'wash' => [0.99, 0.95, 0.95],
            ],
            'violet' => [
                'bg' => [0.18, 0.12, 0.32],
                'bgAlt' => [0.24, 0.16, 0.40],
                'accent' => [0.52, 0.34, 0.86],
                'accentSoft' => [0.64, 0.48, 0.92],
                'wash' => [0.96, 0.94, 0.99],
            ],
        ];

        $base = $themes[$name] ?? ($name === 'custom' ? $themes['navy'] : $themes['navy']);
        $base['textLight'] = [1.0, 1.0, 1.0];
        $base['textDark'] = [0.12, 0.14, 0.18];
        $base['mutedLight'] = [0.78, 0.84, 0.92];
        $base['mutedDark'] = [0.38, 0.42, 0.48];
        $base['rule'] = [0.72, 0.74, 0.78];

        foreach (['bg', 'bgAlt', 'accent', 'accentSoft', 'wash'] as $key) {
            if (!isset($overrides[$key])) {
                continue;
            }
            $parsed = $this->parseHexColor((string) $overrides[$key]);
            if ($parsed !== null) {
                $base[$key] = $parsed;
            }
        }

        return $base;
    }

    /** @param array{0:float,1:float,2:float} $rgb */
    private function pdfFillRect(float $x, float $y, float $w, float $h, array $rgb): string
    {
        return sprintf(
            "%.3f %.3f %.3f rg\n%.2f %.2f %.2f %.2f re f\n0 0 0 rg\n",
            $rgb[0],
            $rgb[1],
            $rgb[2],
            $x,
            $y,
            $w,
            $h
        );
    }

    /** @param array{0:float,1:float,2:float} $rgb */
    private function pdfSetRgb(array $rgb): string
    {
        return sprintf("%.3f %.3f %.3f rg\n", $rgb[0], $rgb[1], $rgb[2]);
    }

    private function makeMonogram(string $title): string
    {
        $title = trim($title);
        if ($title === '') {
            return 'AR';
        }
        $parts = preg_split('/\s+/', $title) ?: [];
        $letters = '';
        foreach ($parts as $p) {
            if ($p === '') {
                continue;
            }
            $letters .= strtoupper($p[0]);
            if (strlen($letters) >= 2) {
                break;
            }
        }
        if (strlen($letters) < 2) {
            $letters = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $title) ?: 'AR', 0, 2));
        }
        return $letters;
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return (bool) $value;
        }
        $v = strtolower(trim((string) $value));
        return !in_array($v, ['', '0', 'false', 'no', 'off'], true);
    }

    /**
     * @param array<int, Node> $blocks
     * @return array<int, string>
     */
    private function flowBlocksToPages(
        array $blocks,
        float $pageWidth,
        float $pageHeight,
        float $contentWidth,
        float $topMargin = self::MARGIN_TOP,
        float $bottomMargin = self::MARGIN_BOTTOM
    ): array {
        $pages = [];
        $this->gBuf = [];
        $this->tBuf = ["BT\n/F1 12 Tf\n"];
        $y = $pageHeight - $topMargin;
        $bottom = $bottomMargin;
        $x = self::MARGIN_X;
        $this->tableStyleIndex = 0;
        $prevWasTable = false;

        $flush = function () use (&$pages): void {
            $this->tBuf[] = "ET\n";
            $pages[] = implode('', $this->gBuf) . implode('', $this->tBuf);
            $this->gBuf = [];
            $this->tBuf = ["BT\n/F1 12 Tf\n"];
        };

        $newPage = function () use (&$y, $pageHeight, $topMargin, $flush): void {
            $flush();
            $y = $pageHeight - $topMargin;
        };

        foreach ($blocks as $block) {
            if ($block instanceof Text) {
                if ($y - self::TEXT_GAP < $bottom) {
                    $newPage();
                }
                $this->tBuf[] = sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $x, $y, $this->escapePdfString($block->text()));
                $y -= self::TEXT_GAP;
                $prevWasTable = false;
                continue;
            }

            if ($block instanceof Heading) {
                if ($prevWasTable) {
                    $y -= 6.0;
                }

                if ($y - self::TITLE_GAP - 24 < $bottom) {
                    $newPage();
                }

                $this->tBuf[] = sprintf(
                    "1 0 0 1 %.2f %.2f Tm\n/F1 13 Tf\n(%s) Tj\n/F1 12 Tf\n",
                    $x,
                    $y,
                    $this->escapePdfString($block->text())
                );
                $y -= self::HEADING_AFTER_GAP;
                $prevWasTable = false;
                continue;
            }

            if ($block instanceof Table) {
                $style = $this->tableStyleIndex % 3;
                $this->tableStyleIndex++;

                $this->flowTable(
                    $block,
                    $y,
                    $x,
                    $contentWidth,
                    $bottom,
                    $pageHeight,
                    $topMargin,
                    $newPage,
                    $style
                );
                $y -= self::TABLE_AFTER_GAP;
                $prevWasTable = true;
                continue;
            }

            if ($block instanceof Row) {
                foreach ($block->children() as $child) {
                    if ($child instanceof Text) {
                        if ($y - self::TEXT_GAP < $bottom) {
                            $newPage();
                        }
                        $this->tBuf[] = sprintf("1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n", $x, $y, $this->escapePdfString($child->text()));
                        $y -= self::TEXT_GAP;
                    }
                }
                $prevWasTable = false;
            }
        }

        if ($this->gBuf !== [] || count($this->tBuf) > 1) {
            $flush();
        }

        return $pages;
    }

    /**
     * @param callable(): void $newPage
     */
    private function flowTable(
        Table $table,
        float &$y,
        float $x,
        float $availableWidth,
        float $bottom,
        float $pageHeight,
        float $topMargin,
        callable $newPage,
        int $style
    ): void {
        $rows = $table->rows();
        if ($rows === []) {
            return;
        }

        $columnCount = $this->resolveColumnCount($table);
        if ($columnCount === 0) {
            return;
        }

        $columnWidths = $this->resolveColumnWidths($table, $columnCount, $availableWidth);
        $rowHeights = $this->measureRowHeights($table, $columnWidths);

        $headerIndexes = [];
        $footerIndexes = [];
        foreach ($rows as $i => $row) {
            if ($row->isHeader()) {
                $headerIndexes[] = $i;
            } elseif ($row->isFooter()) {
                $footerIndexes[] = $i;
            }
        }

        $headerHeight = 0.0;
        foreach ($headerIndexes as $hi) {
            $headerHeight += $rowHeights[$hi];
        }

        $styles = [
            [[0.82, 0.86, 0.92], [0.95, 0.97, 0.99]],
            [[0.84, 0.90, 0.84], [0.96, 0.98, 0.96]],
            [[0.92, 0.88, 0.82], [0.99, 0.97, 0.94]],
        ];
        [$headerRgb, $zebraRgb] = $styles[$style] ?? $styles[0];

        $variantColors = [
            'warning' => [1.0, 0.93, 0.70],
            'success' => [0.85, 0.93, 0.85],
            'danger' => [0.98, 0.85, 0.85],
            'info' => [0.85, 0.90, 0.97],
            'primary' => [0.82, 0.86, 0.92],
        ];
        $footerRgb = [0.88, 0.88, 0.90];

        $drawRow = function (int $rowIndex, int $bodyIndex) use ($table, $rows, $rowHeights, $columnWidths, &$y, $x, $headerRgb, $zebraRgb, $variantColors, $footerRgb): void {
            $row = $rows[$rowIndex];
            $rowHeight = $rowHeights[$rowIndex];
            $rowTop = $y;
            $rowBottom = $y - $rowHeight;
            $cellX = $x;
            $colCursor = 0;
            $isHeaderRow = $row->isHeader();
            $isFooterRow = $row->isFooter();
            $zebra = !$isHeaderRow && !$isFooterRow && ($bodyIndex % 2 === 1);

            $this->gBuf[] = "0.65 w\n0 0 0 RG\n";

            foreach ($row->cells() as $cell) {
                $colSpan = max(1, $cell->colSpan());
                $cellWidth = $this->sumColumnWidths($columnWidths, $colCursor, $colSpan);
                $isHeader = $cell->isHeader() || $isHeaderRow;
                $variant = $cell->variant();

                $fillRgb = null;
                if ($isHeader) {
                    $fillRgb = $headerRgb;
                } elseif ($isFooterRow) {
                    $fillRgb = $footerRgb;
                } elseif ($variant !== null && isset($variantColors[$variant])) {
                    $fillRgb = $variantColors[$variant];
                } elseif ($zebra) {
                    $fillRgb = $zebraRgb;
                }

                if ($fillRgb !== null) {
                    $this->gBuf[] = sprintf(
                        "%.3f %.3f %.3f rg\n%.2f %.2f %.2f %.2f re f\n0 0 0 rg\n",
                        $fillRgb[0],
                        $fillRgb[1],
                        $fillRgb[2],
                        $cellX,
                        $rowBottom,
                        $cellWidth,
                        $rowHeight
                    );
                }

                if ($table->showBorders()) {
                    $this->gBuf[] = sprintf(
                        "%.2f %.2f %.2f %.2f re S\n",
                        $cellX,
                        $rowBottom,
                        $cellWidth,
                        $rowHeight
                    );
                }

                $this->renderCellContent(
                    $cell,
                    $isHeader,
                    $cellX + self::CELL_PAD_X,
                    $rowTop - self::CELL_PAD_Y,
                    max(14.0, $cellWidth - self::CELL_PAD_X * 2)
                );

                $cellX += $cellWidth;
                $colCursor += $colSpan;
            }

            $y = $rowBottom;
        };

        $drawHeaders = function () use ($headerIndexes, $drawRow): void {
            foreach ($headerIndexes as $hi) {
                $drawRow($hi, 0);
            }
        };

        $drawFooters = function () use ($footerIndexes, $drawRow): void {
            foreach ($footerIndexes as $fi) {
                $drawRow($fi, -1);
            }
        };

        $firstBody = count($headerIndexes);
        $bodyEnd = count($rows) - count($footerIndexes);
        $minNeeded = $headerHeight + ($rowHeights[$firstBody] ?? self::BASE_ROW_HEIGHT);
        if ($y - $minNeeded < $bottom) {
            $newPage();
            $y = $pageHeight - $topMargin;
        }

        $drawHeaders();

        $bodyIndex = 0;
        for ($i = $firstBody; $i < $bodyEnd; $i++) {
            $rowHeight = $rowHeights[$i];
            if ($y - $rowHeight < $bottom) {
                $newPage();
                $y = $pageHeight - $topMargin;
                $drawHeaders();
            }
            $drawRow($i, $bodyIndex);
            $bodyIndex++;
        }

        $drawFooters();
    }

    private function renderCellContent(
        TableCell $cell,
        bool $isHeader,
        float $x,
        float $topY,
        float $maxWidth
    ): void {
        $content = $cell->content();

        if ($content instanceof Text) {
            $fontSize = $isHeader ? 10 : 9;
            $label = $this->truncateText($content->text(), $maxWidth, $fontSize);
            $this->tBuf[] = sprintf(
                "1 0 0 1 %.2f %.2f Tm\n/F1 %d Tf\n(%s) Tj\n/F1 12 Tf\n",
                $x,
                $topY - 9.0,
                $fontSize,
                $this->escapePdfString($label)
            );
            return;
        }

        if ($content instanceof Table) {
            $nestedY = $topY;
            $this->renderNestedTable($content, $nestedY, $x, $maxWidth);
            return;
        }

        if ($content instanceof Heading) {
            $this->tBuf[] = sprintf(
                "1 0 0 1 %.2f %.2f Tm\n/F1 10 Tf\n(%s) Tj\n/F1 12 Tf\n",
                $x,
                $topY - 9.0,
                $this->escapePdfString($content->text())
            );
        }
    }

    private function renderNestedTable(Table $table, float &$y, float $x, float $availableWidth): void
    {
        $rows = $table->rows();
        if ($rows === []) {
            return;
        }

        $columnCount = $this->resolveColumnCount($table);
        $columnWidths = $this->resolveColumnWidths($table, $columnCount, $availableWidth);
        $rowHeights = $this->measureRowHeights($table, $columnWidths);
        $this->gBuf[] = "0.55 w\n0 0 0 RG\n";

        foreach ($rows as $rowIndex => $row) {
            $rowHeight = $rowHeights[$rowIndex];
            $rowTop = $y;
            $rowBottom = $y - $rowHeight;
            $cellX = $x;
            $colCursor = 0;
            $isHeaderRow = $row->isHeader();

            foreach ($row->cells() as $cell) {
                $colSpan = max(1, $cell->colSpan());
                $cellWidth = $this->sumColumnWidths($columnWidths, $colCursor, $colSpan);
                $isHeader = $cell->isHeader() || $isHeaderRow;

                if ($isHeader) {
                    $this->gBuf[] = sprintf("0.90 0.90 0.92 rg\n%.2f %.2f %.2f %.2f re f\n0 0 0 rg\n", $cellX, $rowBottom, $cellWidth, $rowHeight);
                }
                if ($table->showBorders()) {
                    $this->gBuf[] = sprintf("%.2f %.2f %.2f %.2f re S\n", $cellX, $rowBottom, $cellWidth, $rowHeight);
                }

                $this->renderCellContent(
                    $cell,
                    $isHeader,
                    $cellX + self::CELL_PAD_X,
                    $rowTop - self::CELL_PAD_Y,
                    max(12.0, $cellWidth - self::CELL_PAD_X * 2)
                );

                $cellX += $cellWidth;
                $colCursor += $colSpan;
            }
            $y = $rowBottom;
        }
    }

    /** @param array<int, float> $columnWidths @return array<int, float> */
    private function measureRowHeights(Table $table, array $columnWidths): array
    {
        $heights = [];
        foreach ($table->rows() as $row) {
            $maxHeight = self::BASE_ROW_HEIGHT;
            $colCursor = 0;
            foreach ($row->cells() as $cell) {
                $colSpan = max(1, $cell->colSpan());
                $cellWidth = $this->sumColumnWidths($columnWidths, $colCursor, $colSpan);
                $innerWidth = max(12.0, $cellWidth - self::CELL_PAD_X * 2);
                $contentHeight = $this->measureCellContentHeight($cell, $innerWidth);
                $maxHeight = max($maxHeight, $contentHeight + self::CELL_PAD_Y * 2);
                $colCursor += $colSpan;
            }
            $heights[] = $maxHeight;
        }
        return $heights;
    }

    private function measureCellContentHeight(TableCell $cell, float $innerWidth): float
    {
        $content = $cell->content();
        if ($content instanceof Text || $content instanceof Heading) {
            return 11.0;
        }
        if ($content instanceof Table) {
            $cc = $this->resolveColumnCount($content);
            if ($cc === 0) {
                return self::BASE_ROW_HEIGHT;
            }
            $cw = $this->resolveColumnWidths($content, $cc, $innerWidth);
            return array_sum($this->measureRowHeights($content, $cw));
        }
        return 11.0;
    }

    private function resolveColumnCount(Table $table): int
    {
        $max = 0;
        foreach ($table->rows() as $row) {
            $count = 0;
            foreach ($row->cells() as $cell) {
                $count += max(1, $cell->colSpan());
            }
            $max = max($max, $count);
        }
        return $max;
    }

    /** @return array<int, float> */
    private function resolveColumnWidths(Table $table, int $columnCount, float $availableWidth): array
    {
        $configured = $table->columnWidths();
        if ($configured !== [] && count($configured) === $columnCount) {
            $sum = array_sum($configured);
            if ($sum > 0) {
                $scale = $availableWidth / $sum;
                return array_map(static fn(float $w): float => $w * $scale, array_map('floatval', $configured));
            }
        }
        return array_fill(0, $columnCount, $availableWidth / max(1, $columnCount));
    }

    /** @param array<int, float> $columnWidths */
    private function sumColumnWidths(array $columnWidths, int $start, int $span): float
    {
        $total = 0.0;
        for ($i = $start; $i < $start + $span; $i++) {
            $total += $columnWidths[$i] ?? 0.0;
        }
        return max(16.0, $total);
    }

    private function truncateText(string $text, float $maxWidth, int $fontSize): string
    {
        $maxChars = max(1, (int) floor($maxWidth / ($fontSize * 0.48)));
        if (strlen($text) <= $maxChars) {
            return $text;
        }
        return substr($text, 0, max(1, $maxChars - 1)) . '...';
    }

    private function escapePdfString(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
