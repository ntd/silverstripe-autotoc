<?php

namespace eNTiDi\Autotoc\Tests;

use SilverStripe\ORM\DataObject;
use SilverStripe\Dev\TestOnly;

/**
 * Provided directly by this class:
 *
 * @property string $Content
 * @property string $Test2
 *
 * Inherited from Autotoc:
 *
 * @method   string getAutotoc()
 * @method   void   clearAutotoc()
 * @method   string getBodyAutotoc()
 * @method   string getContentField()
 * @method   string getOriginalContentField()
 * @property string $Autotoc
 * @property string $ContentField
 * @property string $OriginalContentField
 */
class TestObject extends DataObject implements TestOnly
{
    private static $db = [
        'Content' => 'HTMLText',
        'Test2'   => 'HTMLText',
    ];
}
