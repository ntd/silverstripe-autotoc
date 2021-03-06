<?php

namespace eNTiDi\Autotoc\Tests;

use eNTiDi\Autotoc\Autotoc;
use eNTiDi\Autotoc\Tests\TestObject;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\View\ArrayData;

class AutotocTest extends SapphireTest
{
    public function setUp()
    {
        parent::setUp();

        // Enable the Autotoc extension on TestObject
        TestObject::add_extension('eNTiDi\Autotoc\Autotoc');
    }

    public function testBodyAutotoc()
    {
        $obj = new TestObject;
        $this->assertEquals(' data-spy="scroll" data-target=".toc"', $obj->getBodyAutotoc());
    }

    public function testContentField()
    {
        $obj = new TestObject;
        $obj->Content = '<p>Content</p>';
        $obj->Test2   = '<p>Test2</p>';

        // Check the default content field is Content
        $this->assertEquals('<p>Content</p>', $obj->OriginalContentField);

        // Try to change the content field
        $obj->config()->update('content_field', 'Test2');
        $this->assertEquals('<p>Test2</p>', $obj->OriginalContentField);

        // Change it again
        $obj->config()->update('content_field', 'Unexistent');
        $this->assertEquals('', $obj->OriginalContentField);

        // Restore original value
        $obj->config()->update('content_field', 'Content');
    }

    public function testGetAutotoc()
    {
        $obj = new TestObject;
        $toc = $obj->getAutotoc();
        $this->assertNull($toc);

        $obj->Content = file_get_contents(__DIR__.'/test1');
        $obj->Test2   = file_get_contents(__DIR__.'/test2');

        // Old TOC should still be cached
        $toc = $obj->getAutotoc();
        $this->assertNull($toc);

        $obj->clearAutotoc();

        $toc = $obj->getAutotoc();
        $this->assertTrue($toc instanceof ArrayData);
        $this->assertEquals(5, $toc->Children->count());
        $this->assertStringEqualsFile(__DIR__.'/test1', $obj->OriginalContentField);
        $this->assertStringEqualsFile(__DIR__.'/html2', $obj->ContentField);
        $this->assertStringEqualsFile(__DIR__.'/html2', $obj->Content);

        // Change the content field
        $obj->config()->update('content_field', 'Test2');
        $obj->clearAutotoc();

        $toc = $obj->getAutotoc();
        $this->assertNull($toc);
        $this->assertStringEqualsFile(__DIR__.'/test2', $obj->OriginalContentField);
        $this->assertStringEqualsFile(__DIR__.'/test2', $obj->ContentField);
    }

    public function testAugmentCallback()
    {
        $obj = new TestObject;
        $obj->Content = file_get_contents(__DIR__.'/test1');
        $obj->Test2   = file_get_contents(__DIR__.'/test2');

        // Change the augmenter at class level
        Config::inst()->update(
            get_class($obj),
            'augment_callback',
            'eNTiDi\Autotoc\Tocifier::prependAnchor'
        );
        $obj->clearAutotoc();

        $toc = $obj->getAutotoc();
        $this->assertEquals(5, $toc->Children->count());
        $this->assertStringEqualsFile(__DIR__.'/html1', $obj->Content);

        // Change the augmenter at install level: should have higher
        // precedence
        $obj->config()->update(
            'augment_callback',
            'eNTiDi\Autotoc\Tocifier::setId'
        );
        $obj->clearAutotoc();

        $toc = $obj->getAutotoc();
        $this->assertEquals(5, $toc->Children->count());
        $this->assertStringEqualsFile(__DIR__.'/html2', $obj->Content);
    }

    public function testOverriding()
    {
        $html = file_get_contents(__DIR__.'/test1');

        // The content field is not expected to be changed dynamically:
        // we need to set it *before* creating the test instance
        Config::inst()->update(TestObject::class, 'content_field', 'Something');
        $obj = new TestObject;
        $obj->Content = $html;
        $obj->Test2   = $html;
        $this->assertEquals($html, $obj->Content);
        $this->assertEquals($html, $obj->Test2);

        Config::inst()->update(TestObject::class, 'content_field', 'Content');
        $obj = new TestObject;
        $obj->Content = $html;
        $obj->Test2   = $html;
        $this->assertNotEquals($html, $obj->Content);
        $this->assertEquals($html, $obj->Test2);

        Config::inst()->update(TestObject::class, 'content_field', 'Test2');
        $obj = new TestObject;
        $obj->Content = $html;
        $obj->Test2   = $html;
        // The overriding works on a class basis, so the Content field
        // will still be overriden
        $this->assertNotEquals($html, $obj->Content);
        $this->assertNotEquals($html, $obj->Test2);
    }
}
