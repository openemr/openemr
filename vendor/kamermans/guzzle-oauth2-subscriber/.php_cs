<?php

$finder = PhpCsFixer\Finder::create()
            ->in(__DIR__)
            ->exclude("vendor")
            ->exclude("guzzle_environments")
;

return PhpCsFixer\Config::create()
    ->setRules(array(
         '@PSR2' => true,
         'array_syntax' => ['syntax' => 'short'],
         'no_unused_imports' => true,
         'blank_line_after_opening_tag' => true,
         'blank_line_after_namespace' => true,
    ))
    ->setFinder($finder)
;
