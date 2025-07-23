<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/admin-service/src',
        __DIR__ . '/auth-service/src',
        __DIR__ . '/customer-service/src',
        __DIR__ . '/vehicle-service/src',
        __DIR__ . '/payment-service/src',
        __DIR__ . '/reservation-service/src',
        __DIR__ . '/sales-service/src',
        __DIR__ . '/saga-orchestrator/src',
        __DIR__ . '/shared/src',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setFinder($finder)
    ->setRules([
        '@PSR12' => true,                    // PSR-12 (padrão mais aceito)
        '@PHP84Migration' => true,           // Regras para PHP 8.4
        'array_syntax' => ['syntax' => 'short'], // Usa [] ao invés de array()
        'binary_operator_spaces' => ['default' => 'single_space'], // Alinha operadores
        'blank_line_before_statement' => [
            'statements' => ['return', 'throw', 'if', 'switch', 'try'],
        ],
        'no_unused_imports' => true,         // Remove use's não usados
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
        'ordered_imports' => ['sort_algorithm' => 'alpha'], // Ordena os imports
        'single_quote' => true,              // Usa aspas simples quando possível
        'trailing_comma_in_multiline' => ['elements' => ['arrays']], // Vírgula no final de arrays multilinha
        'phpdoc_order' => true,              // Ordena @param, @return, etc. no phpdoc
        'phpdoc_trim' => true,               // Remove espaços em branco antes/apos docblocks
        'no_blank_lines_after_phpdoc' => true, // Não deixa linha em branco após phpdoc
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'concat_space' => ['spacing' => 'one'], // Espaço ao concatenar strings
        'declare_strict_types' => true,      // Adiciona declare(strict_types=1)
        'cast_spaces' => true,               // Espaços em type casting
        'class_attributes_separation' => [
            'elements' => [
                'method' => 'one',
            ],
        ],
        'function_typehint_space' => true,   // Espaços em type hints
        'return_type_declaration' => true,   // Espaços em return types
        'visibility_required' => true,       // Obriga declaração de visibilidade
    ])
    ->setRiskyAllowed(true);
