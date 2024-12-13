<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setFinder((new Finder())->in(__DIR__))
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
    ]);
