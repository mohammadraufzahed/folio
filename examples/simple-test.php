<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Document\Pdf;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Text;

// Very simple test with just one text node
Pdf::make()
    ->page(
        Page::a4()->withContent(
            Text::make('Test')
        )
    )
    ->save(__DIR__ . '/simple-test.pdf');

echo "Simple test PDF generated\n";
