<?php

declare(strict_types=1);

namespace Folio\Pdf\Ports;

final readonly class UnicodeRangeSet
{
    /**
     * @param array<int, array{start: int, end: int}> $ranges
     */
    public function __construct(public array $ranges = [])
    {
    }

    public function contains(int $codepoint): bool
    {
        foreach ($this->ranges as $range) {
            if ($codepoint >= $range['start'] && $codepoint <= $range['end']) {
                return true;
            }
        }

        return false;
    }
}
