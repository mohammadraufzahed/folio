<?php

declare(strict_types=1);

namespace Folio\Pdf\Nodes;

use Folio\Pdf\Contracts\Node;
use Folio\Pdf\Styling\Style;
use Folio\Pdf\Support\Immutable;

final class TextRun implements Node
{
    use Immutable;

    /** @var array<int, InlineSpan> */
    private readonly array $spans;
    private readonly ?Style $style;

    /**
     * @param array<int, InlineSpan> $spans
     */
    public function __construct(array $spans, ?Style $style = null)
    {
        $this->spans = array_values($spans);
        $this->style = $style;
    }

    public static function make(array $spans, ?Style $style = null): self
    {
        return new self($spans, $style);
    }

    public static function fromText(string $text, ?Style $style = null): self
    {
        return new self([new InlineSpan($text, $style)], $style);
    }

    /**
     * @return array<int, InlineSpan>
     */
    public function spans(): array
    {
        return $this->spans;
    }

    public function text(): string
    {
        $text = '';

        foreach ($this->spans as $span) {
            $text .= $span->text();
        }

        return $text;
    }

    public function style(): ?Style
    {
        return $this->style;
    }

    /**
     * @return array<int, Node>
     */
    public function children(): array
    {
        return [];
    }

    public function hasChildren(): bool
    {
        return false;
    }

    public function type(): string
    {
        return 'text_run';
    }

    /**
     * @param array<int, InlineSpan> $spans
     */
    public function withSpans(array $spans): self
    {
        return new self($spans, $this->style);
    }

    public function withStyle(?Style $style): self
    {
        return new self($this->spans, $style);
    }
}
