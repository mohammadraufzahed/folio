<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

final readonly class StyleSheet
{
    /**
     * @param array<int, StyleRule> $rules
     */
    public function __construct(public array $rules = [])
    {
    }
}
