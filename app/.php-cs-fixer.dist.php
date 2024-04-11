<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['var', 'vendor', 'coverage', 'fixtures', 'node_modules', 'config'])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PhpCsFixer' => true,
        '@PSR2' => true,
        'php_unit_test_class_requires_covers' => false,
        'binary_operator_spaces' => true,
        'whitespace_after_comma_in_array' => true,
        'array_syntax' => ['syntax' => 'short'],
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_order' => false,
        'single_quote' => true,
        'no_unused_imports' => true,
        'no_extra_blank_lines' => ['tokens' => ['extra', 'continue', 'return', 'throw', 'curly_brace_block', 'parenthesis_brace_block', 'square_brace_block']],
        'no_empty_phpdoc' => true,
        'no_empty_comment' => true,
        'no_whitespace_in_blank_line' => true,
        'blank_lines_before_namespace' => true,
        'no_empty_statement' => true,
        'blank_line_after_opening_tag' => false,
        'no_leading_import_slash' => true,
        'no_leading_namespace_whitespace' => true,
        'no_trailing_comma_in_singleline' => ['elements' => ['arguments', 'array_destructuring', 'array', 'group_import']],
        'ordered_imports' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        'standardize_not_equals' => true,
        'object_operator_without_whitespace' => true,
        'no_blank_lines_after_class_opening' => true,
        'concat_space' => ['spacing' => 'one'],
        'global_namespace_import' => true,
        'single_line_comment_style' => false,
        'method_argument_space' => ["on_multiline" => "ignore"]
    ])
    ->setFinder($finder)
;
