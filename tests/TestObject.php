<?php

namespace eNTiDi\Autotoc\Tests;

use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\TestOnly;

class TestObject extends DataObject implements TestOnly
{
    private static $db = [
        'Content' => 'HTMLText',
        'Test2'   => 'HTMLText',
    ];
}
