<?php

require_once '../code/Tocifier.php';

class TocifierTest extends PHPUnit_Framework_TestCase {

    public function testInput() {
        $tocifier = new Tocifier(1234);
        $this->assertFalse($tocifier->process());

        $tocifier = new Tocifier('');
        $this->assertFalse($tocifier->process());
    }

    public function testParser() {
        $tocifier = new Tocifier(@file_get_contents('test1'));
        $this->assertTrue($tocifier->process());

        // Check the augmented HTML
        $returned = $tocifier->getHtml();
        $this->assertStringEqualsFile('html1', $returned);

        // Check the TOC
        ob_start();
        $tocifier->dumpTOC();
        $returned = ob_get_clean();
        $this->assertStringEqualsFile('toc1', $returned);
    }
}
