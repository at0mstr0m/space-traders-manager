<?php

declare(strict_types=1);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        '@PhpCsFixer' => true,
        'array_indentation' => true,
        'array_syntax' => ['syntax' => 'short'],
        'combine_consecutive_unsets' => true,
        'class_attributes_separation' => true,
        'multiline_whitespace_before_semicolons' => true,
        'single_quote' => true,
        'blank_line_before_statement' => [
            'statements' => [
                // 'break',
                'continue',
                'declare',
                'return',
                'throw',
                'try',
            ],
        ],
        'binary_operator_spaces' => [
            'operators' => [
                '=>' => 'single_space',
                '==' => 'single_space',
            ],
        ],
        'braces' => [
            'allow_single_line_closure' => true,
        ],
        'concat_space' => ['spacing' => 'one'],
        'declare_equal_normalize' => true,
        'function_typehint_space' => true,
        'single_line_comment_style' => true,
        'include' => true,
        'lowercase_cast' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'curly_brace_block',
                'extra',
                'parenthesis_brace_block',
                'square_brace_block',
                'throw',
                'use',
            ],
        ],
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_spaces_around_offset' => true,
        'no_unused_imports' => true,
        'no_whitespace_before_comma_in_array' => true,
        'no_whitespace_in_blank_line' => true,
        'object_operator_without_whitespace' => true,
        'phpdoc_align' => false,
        'phpdoc_no_alias_tag' => false,
        'phpdoc_separation' => true,
        'phpdoc_to_comment' => [
            'ignored_tags' => ['todo', 'var'],
        ],
        'blank_lines_before_namespace' => true,
        'ternary_operator_spaces' => true,
        'trim_array_spaces' => true,
        'unary_operator_spaces' => true,
        'whitespace_after_comma_in_array' => true,
        'php_unit_method_casing' => ['case' => 'snake_case'],
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
        ],
        'phpdoc_types_order' => [
            'null_adjustment' => 'always_last',
        ],
        'ordered_imports' => [
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
        ],
        'no_unneeded_import_alias' => true,
    ])
    ->setLineEnding("\n");
