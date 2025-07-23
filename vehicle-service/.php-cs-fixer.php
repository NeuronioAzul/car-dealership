<?php

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12'                      => true,                    // PSR-12 (padrão mais aceito)
        'array_syntax'                => ['syntax' => 'short'], // Usa [] ao invés de array()
        'binary_operator_spaces'      => ['default' => 'align_single_space'], // Alinha operadores
        'blank_line_before_statement' => [
            'statements' => ['return', 'throw', 'if', 'switch', 'try'],
        ],
        'no_unused_imports'    => true,         // Remove use's não usados
        'no_extra_blank_lines' => [
            'tokens' => [
                'extra',
                'throw',
                'use',
                'parenthesis_brace_block',
                'square_brace_block',
                'curly_brace_block',
            ],
        ],
        'ordered_imports'             => ['sort_algorithm' => 'alpha'], // Ordena os imports
        'single_quote'                => true,              // Usa aspas simples quando possível
        'trailing_comma_in_multiline' => ['elements' => ['arrays']], // Vírgula no final de arrays multilinha
        'phpdoc_order'                => true,              // Ordena @param, @return, etc. no phpdoc
        'phpdoc_trim'                 => true,               // Remove espaços em branco antes/apos docblocks
        'no_blank_lines_after_phpdoc' => true, // Não deixa linha em branco após phpdoc
        'method_argument_space'       => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'concat_space' => ['spacing' => 'one'], // Espaço ao concatenar strings
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__ . '/src')
    );
