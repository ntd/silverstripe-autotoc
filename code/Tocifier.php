<?php

class Tocifier {

    // List of headers elements
    private $_headers;

    // Array of references to the potential parents
    private $_dangling;

    // Hold the most recently generated tree
    private $_tree;


    private function _pickHeaders($html) {
        $doc = new DOMDocument();
        if (! @$doc->loadHTML($html))
            return false;

        $this->_headers = array();
        $xpath = new DOMXPath($doc);

        foreach ($xpath->query('//h1|//h2|//h3|//h4|//h5|//h6') as $h) {
            $level = (int) substr($h->tagName, 1);
            $this->_headers[] = array($level, $h->textContent);
        }

        return true;
    }

    private function &_getParent($level) {
        while (--$level >= 0) {
            if (isset($this->_dangling[$level]))
                return $this->_dangling[$level];
        }
        // This should never be reached
        assert(false);
    }

    private function &_newNode($text, $level) {
        $node = array('title' => $text);

        // Clear the trailing dangling parents after level, if any
        end($this->_dangling);
        $last = key($this->_dangling);
        for ($n = $level+1; $n <= $last; ++$n)
            unset($this->_dangling[$n]);

        // Consider this node a potential dangling parent
        $this->_dangling[$level] =& $node;

        return $node;
    }

    private function _buildTree() {
        // Initialize the data structures
        $this->_dangling = array();
        $this->_tree =& $this->_newNode('', 0);

        // Create the tree
        foreach ($this->_headers as $header) {
            list($level, $text) = $header;
            $parent =& $this->_getParent($level);
            $node =& $this->_newNode($text, $level);
            isset($parent['children']) or $parent['children'] = array();
            $parent['children'][] =& $node;
        }
    }

    private function &_getTree() {
        if (is_null($this->_tree)) {
            if (empty($this->_headers))
                $this->_tree = array();
            else
                $this->_buildTree();
        }

        return $this->_tree;
    }

    private function _dumpBranch($node, $indent = '') {
        $title = $node['title'];
        echo "$indent$title\n";
        if (isset($node['children'])) {
            foreach ($node['children'] as &$child)
                $this->_dumpBranch($child, "$indent\t");
        }
    }


    /**
     * Feed a file to the TOC generator.
     *
     * The file is read by using file_get_contents() so, if properly
     * enabled at PHP level, a URI can be provided instead of a path.
     *
     * @param String $file Path or URI to the HTML file.
     * @return boolean     true on success, false on errors.
     */
    public function parseFile($file) {
        $html = @file_get_contents($file);
        if ($html === false)
            return false;

        return $this->parseHtml($html);
    }

    /**
     * Feed an HTML string to the TOC generator.
     *
     * The parsing phase involves picking up all the HTML header
     * elements (from <h1> to <h6>), so if the HTML is not well formed
     * this function will fail.
     *
     * @param String $html A chunk of valid HTML.
     * @return boolean     true on success, false on errors.
     */
    public function parseHtml($html) {
        if (! $this->_pickHeaders($html)) {
            $this->_tree = false;
            return false;
        }

        $this->_tree = null;
        return true;
    }

    /**
     * Get the TOC (Table Of Contents) from the provided HTML.
     *
     * The HTML to parse should be provided throught the parseHtml() or
     * the parseFile() methods.
     *
     * The TOC is represented in the form of:
     *
     * <code>
     * array(
     *     array('title' => 'Item 1',
     *           'children' => array(
     *               array('title' => 'Subitem 1.1'),
     *               array('title' => 'Subitem 1.2',
     *                     'children => array(
     *                         array('title => 'Subsubitem 1.2.1')
     *     )))),
     *     array('title' => 'Item 2',
     *           'children' => array(
     *               array('title' => 'Subitem 2.1'),
     *               array('title' => 'Subitem 2.2')
     *     ))
     * );
     * </code>
     *
     * The TOC is cached, so subsequent calls will return the same tree.
     *
     * @return Array An array representing the TOC. A valid array is
     *               always returned.
     */
    public function getTOC() {
        $tree =& $this->_getTree();
        return isset($tree['children']) ? $tree['children'] : array();
    }

    /**
     * Dump the TOC to stdout for debugging purpose.
     */
    public function dumpTOC() {
        $tree =& $this->_getTree();
        $this->_dumpBranch($tree);
    }
};
