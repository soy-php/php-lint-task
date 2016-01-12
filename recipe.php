<?php

$recipe = new \Soy\Recipe();

$recipe->component('default', function (\Soy\PhpLint\RunTask $phpLintTask) {
    $phpLintTask
        ->setVerbose(true)
        ->enableCache('phplint.json')
        ->run(
            \Symfony\Component\Finder\Finder::create()
                ->in('.')
                ->name('*.php')
                ->exclude('vendor')
        );
});

return $recipe;
