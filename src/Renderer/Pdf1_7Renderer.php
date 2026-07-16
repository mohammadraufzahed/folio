<?php

declare(strict_types=1);

namespace Folio\Pdf\Renderer;

use Folio\Pdf\Document\Document;
use Folio\Pdf\Font\Core14FontMetrics;
use Folio\Pdf\Font\Font;
use Folio\Pdf\Layout\LayoutBox;
use Folio\Pdf\Layout\LayoutResult;
use Folio\Pdf\Layout\TextWrapper;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Text;
use Folio\Pdf\Nodes\TextRun;
use Folio\Pdf\Ports\FontMetricsPort;
use Folio\Pdf\Ports\RendererPort;
use Folio\Pdf\Styling\Color;

final class Pdf1_7Renderer implements RendererPort
{
    private readonly FontMetricsPort $fontMetrics;
    private readonly bool $compress;

    private int $nextId = 0;
    /** @var array<int, string> */
    private array $objects = [];
    /** @var array<int, array{width: float, height: float, contentId: int}> */
    private array $pages = [];
    /** @var array<string, int> */
    private array $fontObjects = [];

    public function __construct(?FontMetricsPort $fontMetrics = null, bool $compress = true)
    {
        $this->fontMetrics = $fontMetrics ?? Core14FontMetrics::default();
        $this->compress = $compress;
    }

    public function render(Document $document, LayoutResult $layout): string
    {
        $this->reset();

        foreach ($layout->layoutBoxes() as $pageBox) {
            $this->addPage($pageBox);
        }

        if ($this->pages === []) {
            $this->addEmptyPage(595.0, 842.0);
        }

        return $this->build();
    }

    private function reset(): void
    {
        $this->nextId = 0;
        $this->objects = [];
        $this->pages = [];
        $this->fontObjects = [];
    }

    private function addEmptyPage(float $width, float $height): void
    {
        $contentId = $this->createObject();
        $this->objects[$contentId] = $this->streamObject('');
        $this->pages[] = ['width' => $width, 'height' => $height, 'contentId' => $contentId];
    }

    private function addPage(LayoutBox $pageBox): void
    {
        $width = $pageBox->width();
        $height = $pageBox->height();

        $content = '';
        foreach ($pageBox->children() as $child) {
            $content .= $this->renderBox($child, 0.0, 0.0, $height, $width);
        }

        $contentId = $this->createObject();
        $this->objects[$contentId] = $this->streamObject($content);
        $this->pages[] = ['width' => $width, 'height' => $height, 'contentId' => $contentId];
    }

    private function renderBox(LayoutBox $box, float $dx, float $dy, float $pageHeight, float $availableWidth): string
    {
        $dx += $box->x();
        $dy += $box->y();

        $stream = '';

        $style = $box->computedStyle();

        if ($style !== null && $style->box->background instanceof Color) {
            $stream .= $this->paintRect($dx, $dy, $box->width(), $box->height(), $pageHeight, $style->box->background);
        }

        $source = $box->source();

        if ($source instanceof Text || $source instanceof TextRun || $source instanceof Heading) {
            $text = match (true) {
                $source instanceof Heading => $source->text(),
                default => $source->text(),
            };

            $stream .= $this->paintText($text, $box, $dx, $dy, $pageHeight, $availableWidth);

            return $stream;
        }

        foreach ($box->children() as $child) {
            $stream .= $this->renderBox($child, $dx, $dy, $pageHeight, $availableWidth);
        }

        return $stream;
    }

    private function paintRect(float $x, float $y, float $width, float $height, float $pageHeight, Color $color): string
    {
        $pdfY = $pageHeight - $y - $height;

        return sprintf(
            "%.3f %.3f %.3f rg\n%.2f %.2f %.2f %.2f re\nf\n0 0 0 rg\n",
            $color->red(),
            $color->green(),
            $color->blue(),
            $x,
            $pdfY,
            $width,
            $height,
        );
    }

    private function paintText(string $text, LayoutBox $box, float $dx, float $dy, float $pageHeight, float $availableWidth): string
    {
        if ($text === '') {
            return '';
        }

        $style = $box->computedStyle();
        $fontSize = $style?->text->fontSize ?? 12.0;
        $lineHeightMultiplier = $style?->text->lineHeight ?? 1.2;
        $fontName = $style?->text->font ?? 'Helvetica';
        $color = $style?->text->color ?? Color::black();

        $font = Font::make($fontName, size: $fontSize);
        $wrapper = new TextWrapper($this->fontMetrics);
        $wrapped = $wrapper->wrap($text, $font, $fontSize, min($box->width(), $availableWidth), $lineHeightMultiplier);

        $pdfFontName = $this->mapFontName($fontName);
        $this->ensureFont($pdfFontName);

        $stream = sprintf("BT\n/%s %.2f Tf\n", $pdfFontName, $fontSize);
        $stream .= sprintf("%.3f %.3f %.3f rg\n", $color->red(), $color->green(), $color->blue());

        $lineHeight = $this->fontMetrics->lineHeight($font, $fontSize) * max(0.1, $lineHeightMultiplier);

        foreach ($wrapped->lines as $index => $line) {
            $lineWidth = $this->fontMetrics->measure($line, $font, $fontSize)->width;
            $offsetX = match ($style?->text->alignment ?? null) {
                \Folio\Pdf\Styling\Alignment::Right => max(0.0, $box->width() - $lineWidth),
                \Folio\Pdf\Styling\Alignment::Center => max(0.0, ($box->width() - $lineWidth) / 2.0),
                default => 0.0,
            };

            $baselineY = $pageHeight - $dy - $fontSize - ($index * $lineHeight);
            $stream .= sprintf(
                "1 0 0 1 %.2f %.2f Tm\n(%s) Tj\n",
                $dx + $offsetX,
                $baselineY,
                $this->escapePdfString($line),
            );
        }

        $stream .= "ET\n";

        return $stream;
    }

    private function ensureFont(string $pdfName): int
    {
        if (isset($this->fontObjects[$pdfName])) {
            return $this->fontObjects[$pdfName];
        }

        $id = $this->createObject();
        $baseFont = match ($pdfName) {
            'F1' => 'Helvetica',
            'F2' => 'Times-Roman',
            'F3' => 'Courier',
            default => 'Helvetica',
        };

        $this->objects[$id] = sprintf(
            "<<\n" .
            "  /Type /Font\n" .
            "  /Subtype /Type1\n" .
            "  /BaseFont /%s\n" .
            ">>\n",
            $baseFont,
        );
        $this->fontObjects[$pdfName] = $id;

        return $id;
    }

    private function mapFontName(string $name): string
    {
        $lower = strtolower($name);

        if (str_contains($lower, 'times')) {
            return 'F2';
        }

        if (str_contains($lower, 'courier')) {
            return 'F3';
        }

        return 'F1';
    }

    private function escapePdfString(string $text): string
    {
        return str_replace(
            ['\\', '(', ')', "\r", "\n", "\t"],
            ['\\\\', '\\(', '\\)', '\\r', '\\n', '\\t'],
            $text,
        );
    }

    private function streamObject(string $content): string
    {
        $data = $content;

        if ($this->compress && function_exists('gzcompress')) {
            $compressed = gzcompress($content);

            if ($compressed !== false) {
                $data = $compressed;

                return sprintf(
                    "<<\n" .
                    "  /Length %d\n" .
                    "  /Filter /FlateDecode\n" .
                    ">>\n" .
                    "stream\n" .
                    '%s' .
                    "\nendstream\n",
                    strlen($data),
                    $data,
                );
            }
        }

        return sprintf(
            "<<\n" .
            "  /Length %d\n" .
            ">>\n" .
            "stream\n" .
            '%s' .
            "\nendstream\n",
            strlen($data),
            $data,
        );
    }

    private function createObject(): int
    {
        $this->nextId++;
        $this->objects[$this->nextId] = '';

        return $this->nextId;
    }

    private function build(): string
    {
        $pdf = "%PDF-1.7\n";

        $infoId = $this->createObject();
        $this->objects[$infoId] = $this->infoObject();

        $catalogId = $this->createObject();
        $pagesId = $this->createObject();

        $pageObjectIds = [];
        foreach ($this->pages as $page) {
            $pageObjectIds[] = $this->createPageObject($page, $pagesId);
        }

        $this->objects[$pagesId] = sprintf(
            "<<\n" .
            "  /Type /Pages\n" .
            "  /Kids [%s]\n" .
            "  /Count %d\n" .
            ">>\n",
            implode(' ', array_map(fn (int $id) => sprintf('%d 0 R', $id), $pageObjectIds)),
            count($pageObjectIds),
        );

        $this->objects[$catalogId] = sprintf(
            "<<\n" .
            "  /Type /Catalog\n" .
            "  /Pages %d 0 R\n" .
            ">>\n",
            $pagesId,
        );

        $offsets = [];
        foreach ($this->objects as $id => $content) {
            $offsets[$id] = strlen($pdf);
            $pdf .= sprintf("%d 0 obj\n", $id);
            $pdf .= $content;
            $pdf .= "endobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n";
        $pdf .= sprintf("0 %d\n", $this->nextId + 1);
        $pdf .= "0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n";
        $pdf .= sprintf(
            "<<\n" .
            "  /Size %d\n" .
            "  /Root %d 0 R\n" .
            "  /Info %d 0 R\n" .
            ">>\n",
            $this->nextId + 1,
            $catalogId,
            $infoId,
        );

        $pdf .= sprintf("startxref\n%d\n", $xrefOffset);
        $pdf .= "%%EOF\n";

        return $pdf;
    }

    /**
     * @param array{width: float, height: float, contentId: int} $page
     */
    private function createPageObject(array $page, int $pagesId): int
    {
        $id = $this->createObject();

        $resources = "    /Font <<\n";
        foreach ($this->fontObjects as $name => $fontId) {
            $resources .= sprintf("      /%s %d 0 R\n", $name, $fontId);
        }
        $resources .= '    >>';

        $this->objects[$id] = sprintf(
            "<<\n" .
            "  /Type /Page\n" .
            "  /Parent %d 0 R\n" .
            "  /MediaBox [0 0 %.2f %.2f]\n" .
            "  /Contents %d 0 R\n" .
            "  /Resources <<\n" .
            "%s\n" .
            "  >>\n" .
            ">>\n",
            $pagesId,
            $page['width'],
            $page['height'],
            $page['contentId'],
            $resources,
        );

        return $id;
    }

    private function infoObject(): string
    {
        $date = date('YmdHis');

        return sprintf(
            "<<\n" .
            "  /Producer (Folio PDF 1.7 Renderer)\n" .
            "  /CreationDate (D:%s)\n" .
            ">>\n",
            $date,
        );
    }
}
