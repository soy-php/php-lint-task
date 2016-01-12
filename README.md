# PHP Lint Task

[![Latest Stable Version](https://poser.pugx.org/soy-php/php-lint-task/v/stable)](https://packagist.org/packages/soy-php/php-lint-task) [![Total Downloads](https://poser.pugx.org/soy-php/php-lint-task/downloads)](https://packagist.org/packages/soy-php/php-lint-task) [![Latest Unstable Version](https://poser.pugx.org/soy-php/php-lint-task/v/unstable)](https://packagist.org/packages/soy-php/php-lint-task) [![License](https://poser.pugx.org/soy-php/php-lint-task/license)](https://packagist.org/packages/soy-php/php-lint-task)

## Introduction
This is a PHP Lint task for [Soy](https://github.com/soy-php/soy)

## Usage
Include `soy-php/php-lint-task` in your project with composer:

```sh
$ composer require soy-php/php-lint-task
```

Then in your recipe you can use the task as follows:
```php
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
```
