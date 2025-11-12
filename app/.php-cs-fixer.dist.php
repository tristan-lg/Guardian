<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['var', 'vendor', 'coverage', 'fixtures', 'config'])
;


return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR2' => true,
        '@PSR12' => true,
        '@Symfony' => true,
        '@PhpCsFixer' => true,

        'php_unit_test_class_requires_covers' => false,
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_after_opening_tag' => false,
        'no_leading_namespace_whitespace' => true,
        'concat_space' => ['spacing' => 'one'],
        'global_namespace_import' => true,
        'method_argument_space' => ["on_multiline" => "ignore"]
    ])
    ->setFinder($finder)
    ;

