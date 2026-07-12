<?php

declare(strict_types=1);

namespace Folio\Pdf\Pdf;

use Folio\Pdf\Contracts\PdfWriter as PdfWriterInterface;

/**
 * Basic PDF writer implementation.
 */
final class PdfFileWriter implements PdfWriterInterface
{
    private string $buffer = '';
    private int $objectId = 0;
    private array $objects = [];
    private array $pages = [];
    private ?int $rootObject = null;
    private ?int $infoObject = null;

    public function __construct()
    {
        $this->initialize();
    }

    private function initialize(): void
    {
        $this->buffer = '';
        $this->objectId = 0;
        $this->objects = [];
        $this->pages = [];
        $this->rootObject = null;
        $this->infoObject = null;
    }

    public function save(string $path): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, recursive: true);
        }
        file_put_contents($path, $this->toString());
    }

    public function toString(): string
    {
        return $this->generatePdf();
    }

    public function toBytes(): string
    {
        return $this->toString();
    }

    /**
     * Add a page to the PDF.
     */
    public function addPage(float $width, float $height, string $content = ''): self
    {
        $pageId = $this->createObject();
        $this->pages[] = [
            'id' => $pageId,
            'width' => $width,
            'height' => $height,
            'content' => $content,
        ];
        return $this;
    }

    /**
     * Create a new PDF object.
     */
    private function createObject(): int
    {
        $this->objectId++;
        $this->objects[$this->objectId] = '';
        return $this->objectId;
    }

    /**
     * Generate the complete PDF document.
     */
    private function generatePdf(): string
    {
        $pdf = "%PDF-1.7\n";

        // Create info object
        $this->infoObject = $this->createObject();
        $this->objects[$this->infoObject] = $this->generateInfoObject();

        // Create root object (catalog)
        $this->rootObject = $this->createObject();

        // Create pages and kids
        $kids = [];
        foreach ($this->pages as $page) {
            $pageObjId = $page['id'];
            $contentObjId = $this->createObject();

            $content = $page['content'];
            $contentLength = strlen($content);

            $this->objects[$contentObjId] = sprintf(
                "<<\n" .
                "  /Length %d\n" .
                ">>\n" .
                "stream\n" .
                "%s" .
                "\nendstream\n",
                $contentLength,
                $content
            );

            $this->objects[$pageObjId] = sprintf(
                "<<\n" .
                "  /Type /Page\n" .
                "  /Parent %d 0 R\n" .
                "  /MediaBox [0 0 %.2f %.2f]\n" .
                "  /Contents %d 0 R\n" .
                "  /Resources <<\n" .
                "    /Font <<\n" .
                "      /F1 %d 0 R\n" .
                "    >>\n" .
                "  >>\n" .
                ">>\n",
                0, // Will be set to pages object
                $page['width'],
                $page['height'],
                $contentObjId,
                0 // Font object, will be created
            );

            $kids[] = sprintf("%d 0 R", $pageObjId);
        }

        // Create pages object
        $pagesObjectId = $this->createObject();
        $this->objects[$pagesObjectId] = sprintf(
            "<<\n" .
            "  /Type /Pages\n" .
            "  /Kids [%s]\n" .
            "  /Count %d\n" .
            ">>\n",
            implode(' ', $kids),
            count($this->pages)
        );

        // Update page parent references
        foreach ($this->pages as $page) {
            $this->objects[$page['id']] = str_replace(
                sprintf('/Parent %d 0 R', 0),
                sprintf('/Parent %d 0 R', $pagesObjectId),
                $this->objects[$page['id']]
            );
        }

        // Create font object
        $fontObjectId = $this->createObject();
        $this->objects[$fontObjectId] = sprintf(
            "<<\n" .
            "  /Type /Font\n" .
            "  /Subtype /Type1\n" .
            "  /BaseFont /Helvetica\n" .
            ">>\n"
        );

        // Update font references in pages
        foreach ($this->pages as $page) {
            $this->objects[$page['id']] = str_replace(
                sprintf('/F1 %d 0 R', 0),
                sprintf('/F1 %d 0 R', $fontObjectId),
                $this->objects[$page['id']]
            );
        }

        // Create root catalog
        $this->objects[$this->rootObject] = sprintf(
            "<<\n" .
            "  /Type /Catalog\n" .
            "  /Pages %d 0 R\n" .
            ">>\n",
            $pagesObjectId
        );

        // Write all objects
        $offsets = [];
        foreach ($this->objects as $id => $content) {
            $offsets[$id] = strlen($pdf);
            $pdf .= sprintf("%d 0 obj\n", $id);
            $pdf .= $content;
            $pdf .= "endobj\n";
        }

        // Write xref table
        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n";
        $pdf .= sprintf("0 %d\n", $this->objectId + 1);
        $pdf .= "0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        // Write trailer
        $pdf .= "trailer\n";
        $pdf .= sprintf(
            "<<\n" .
            "  /Size %d\n" .
            "  /Root %d 0 R\n" .
            "  /Info %d 0 R\n" .
            ">>\n",
            $this->objectId + 1,
            $this->rootObject,
            $this->infoObject
        );

        // Write startxref
        $pdf .= sprintf("startxref\n%d\n", $xrefOffset);
        $pdf .= "%%EOF\n";

        return $pdf;
    }

    /**
     * Generate the info object.
     */
    private function generateInfoObject(): string
    {
        $date = date('YmdHis');
        return sprintf(
            "<<\n" .
            "  /Producer (Folio PDF)\n" .
            "  /CreationDate (D:%s)\n" .
            ">>\n",
            $date
        );
    }
}
