<?php

declare(strict_types=1);

namespace Folio\Pdf\Template\Error;

/**
 * Severity of a diagnostic message.
 */
enum Severity: string
{
    case Error = 'error';
    case Warning = 'warning';
    case Info = 'info';
    case Hint = 'hint';
}
