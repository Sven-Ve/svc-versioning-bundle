<?php

$year = date('Y');
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src')
;
$config = new PhpCsFixer\Config();
return $config
  ->setRules([
        '@Symfony' => true,
        'yoda_style' => false,
        'indentation_type' => true,
        'array_indentation' => true,
        'concat_space' => ["spacing" => "one"],
        'class_attributes_separation' => ['elements' => ['property' => 'one', 'method' => 'one']],
#    ->setIndent("  ")
        'header_comment' => [
          'header' => "This file is part of the svc-versioning bundle.\n\n(c) " . $year . " Sven Vetter <dev@sv-systems.com>.\n\nFor the full copyright and license information, please view the LICENSE\nfile that was distributed with this source code.",
          'separate' => 'both'
      ]
  ])
->setFinder($finder)
;