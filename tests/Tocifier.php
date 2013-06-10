<?php

require_once '../code/Tocifier.php';

$tocifier = new Tocifier();
assert($tocifier->parseFile('test1'));

ob_start();
$tocifier->dumpTOC();
$result = ob_get_clean();

$expected = file_get_contents('result1');
assert($result == $expected);
