<?php

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => [
            'default' => 'align_single_space',
            'operators' => [
                '=' => 'align_single_space',
                '=>' => 'align_single_space',
                '==' => 'align_single_space',
                '===' => 'align_single_space',
                '!=' => 'align_single_space',
                '!==' => 'align_single_space',
                '<' => 'align_single_space',
                '<=' => 'align_single_space',
                '>' => 'align_single_space',
                '>=' => 'align_single_space',
                'instanceof' => 'align_single_space',
            ],
        ],
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
            ->exclude('vendor')
    );
