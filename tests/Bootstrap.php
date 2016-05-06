<?php

/* This is a helper file for easy testing:
 *
 * phpunit --bootstrap tests/Bootstrap.php tests/
 */

spl_autoload_register(function ($class) {
    switch ($class) {

    case 'Tocifier':
        require_once __DIR__ . '/../code/Tocifier.php';
        break;
    }
});
