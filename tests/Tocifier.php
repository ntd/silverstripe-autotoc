<?php

require_once '../code/Tocifier.php';

// Check for invalid HTML
$tocifier = new Tocifier(1234);
assert(! $tocifier->process());

$tocifier = new Tocifier('');
assert(! $tocifier->process());

// Check for valid HTML
$tocifier = new Tocifier(@file_get_contents('test1'));
assert($tocifier->process());

// Check the augmented HTML
$given = $tocifier->getHtml();
$expected = file_get_contents('html1');
assert($given == $expected);

// Check the TOC
ob_start();
$tocifier->dumpTOC();
$given = ob_get_clean();
$expected = file_get_contents('toc1');
assert($given == $expected);
