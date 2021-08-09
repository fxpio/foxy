<?php
$config = new PhpCsFixer\Config();
$finder = new PhpCsFixer\Finder();

return ($config)
    ->setRules(array(
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@PHPUnit60Migration:risky' => true,
        'array_syntax' => array('syntax' => 'long'),
        'class_definition' => array('single_line' => false),
        'declare_strict_types' => false,
        'ordered_imports' => true,
        'php_unit_expectation' => false,
        'php_unit_no_expectation_annotation' => false,
        'php_unit_strict' => false,
        'php_unit_test_class_requires_covers' => false,
        'self_accessor' => false,
        'single_line_comment_style' => false,
        'visibility_required' => array('elements' => array('property', 'method')),
    ))
    ->setRiskyAllowed(true)
    ->setFinder(
        ($finder)
            ->in(__DIR__)
    )
    ->setCacheFile('.php-cs-fixer.cache')
;
