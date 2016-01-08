<?php

require_once '../code/Tocifier.php';

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
        $tocifier = new Tocifier(@file_get_contents('test1'));
        $this->assertEquals($tocifier->getHtml(), '');
        $this->assertTrue($tocifier->process());
        $this->assertStringEqualsFile('html1', $tocifier->getHtml());
    }

    public function testTOC()
    {
        $tocifier = new Tocifier(@file_get_contents('test1'));
        $this->assertEquals($tocifier->getTOC(), array());
        $this->assertTrue($tocifier->process());
        $this->assertNotNull($tocifier->getTOC());

        ob_start();
        $tocifier->dumpTOC();
        $returned = ob_get_clean();
        $this->assertStringEqualsFile('toc1', $returned);
    }
}
