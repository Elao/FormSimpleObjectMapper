<?php

$header = <<<'EOF'
This file is part of the "elao/form-simple-object-mapper" package.

Copyright (C) 2016 Elao

@author Elao <contact@elao.com>
EOF;

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude(__DIR__ . '/tests/Fixtures/Integration/Symfony/app/cache')
;

return PhpCsFixer\Config::create()
    ->setUsingCache(true)
    ->setFinder($finder)
    ->setRules([
        '@Symfony' => true,
        'psr0' => false,
        'concat_space' => ['spacing' => 'one'],
        'phpdoc_short_description' => false,
        'phpdoc_order' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => true,
        'simplified_null_return' => false,
        'header_comment' => ['header' => $header],
    ])
;
