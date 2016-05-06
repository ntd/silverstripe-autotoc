<?php

class TocifierTest extends PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $tocifier = new Tocifier(1234);
        $this->assertFalse($tocifier->process());

        $tocifier = new Tocifier('');
        $this->assertFalse($tocifier->process());

        $tocifier = new Tocifier(null);
        $this->assertFalse($tocifier->process());

        $tocifier = new Tocifier(array('1234'));
        $this->assertFalse($tocifier->process());

        $tocifier = new Tocifier('1234');
        $this->assertTrue($tocifier->process());
    }

    public function testHtml()
    {
        $tocifier = new Tocifier(file_get_contents(__DIR__ . '/test1'));
        $this->assertEquals($tocifier->getHtml(), '');
        $this->assertTrue($tocifier->process());
        $this->assertStringEqualsFile(__DIR__ . '/html1', $tocifier->getHtml());
    }

    public function testTOC()
    {
        $tocifier = new Tocifier(file_get_contents(__DIR__ . '/test1'));
        $this->assertEquals($tocifier->getTOC(), array());
        $this->assertTrue($tocifier->process());
        $this->assertNotNull($tocifier->getTOC());

        ob_start();
        $tocifier->dumpTOC();
        $returned = ob_get_clean();
        $this->assertStringEqualsFile(__DIR__ . '/toc1', $returned);
    }

    public function testDataHideFromTOC()
    {
        $tocifier = new Tocifier(file_get_contents(__DIR__ . '/test2'));
        $this->assertEquals($tocifier->getHtml(), '');
        $this->assertTrue($tocifier->process());

        // Check the augmented HTML is equal to the original one
        $this->assertStringEqualsFile(__DIR__ . '/test2', $tocifier->getHtml());

        ob_start();
        $tocifier->dumpTOC();
        $returned = ob_get_clean();
        $this->assertEquals("\n", $returned);
    }
}
