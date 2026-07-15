<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->in(__DIR__ . '/examples')
    ->in(__DIR__ . '/bin')
    ->in(__DIR__ . '/lsp');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'declare_strict_types' => true,
        'no_unused_imports' => true,
        'ordered_imports' => true,
        'no_empty_comment' => true,
        'no_trailing_whitespace_in_comment' => true,
        'no_extra_blank_lines' => true,
        'phpdoc_indent' => true,
        'phpdoc_tag_type' => true,
        'phpdoc_trim' => true,
        'single_quote' => true,
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache');
