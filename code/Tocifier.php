<?php

/*
 * Tocifier is intentionally decoupled from SilverStripe to be able to
 * test it without needing to put all the test infrastructure up.
 */
class Tocifier
{
    // Prefix to prepend to every URL fragment
    public static $prefix = 'TOC-';

    // The original HTML
    private $_raw_html = '';

    // $_raw_html augmented for proper navigation
    private $_html = '';

    // The most recently generated TOC tree.
    private $_tree;

    // Array of references to the potential parents
    private $_dangling = array();

    // Callback for augmenting a single DOMElement
    private $_augment_callback;


    /**
     * Get the TOC node closest to a given nesting level.
     *
     * @param  int $level  The requested nesting level.
     * @return array
     */
    private function &_getParent($level)
    {
        while (--$level >= 0) {
            if (isset($this->_dangling[$level])) {
                return $this->_dangling[$level];
            }
        }
        // This should never be reached
        assert(false);
    }

    /**
     * Get the plain text content from a DOM element.
     *
     * @param  DOMElement $tag  The DOM element to inspect.
     * @return string
     */
    private function _getPlainText(DOMElement $tag)
    {
        // Work on a copy
        $clone = $tag->cloneNode(true);

        // Strip unneded tags (<small>)
        while (($tag = $clone->getElementsByTagName('small')) && $tag->length) {
            $tag->item(0)->parentNode->removeChild($tag->item(0));
        }

        return $clone->textContent;
    }

    /**
     * Create a new TOC node.
     *
     * @param  string $id     Node id, used for anchoring
     * @param  string $text   Title text
     * @param  int    $level  The nesting level of the node
     * @return array
     */
    private function &_newNode($id, $text, $level)
    {
        $node = array(
            'id'    => $id,
            'title' => $text
        );

        // Clear the trailing dangling parents after level, if any
        end($this->_dangling);
        $last = key($this->_dangling);
        for ($n = $level+1; $n <= $last; ++$n) {
            unset($this->_dangling[$n]);
        }

        // Consider this node a potential dangling parent
        $this->_dangling[$level] =& $node;

        return $node;
    }

    /**
     * Process the specific document.
     *
     * @param  DOMDocument $doc  The document to process.
     */
    private function _processDocument($doc)
    {
        $this->_tree =& $this->_newNode(self::$prefix, '', 0);
        $n = 1;

        $xpath = new DOMXPath($doc);
        $query = '//*[translate(name(), "123456", "......") = "h."][not(@data-hide-from-toc)]';

        foreach ($xpath->query($query) as $h) {
            $text = $this->_getPlainText($h);
            $level = (int) substr($h->tagName, 1);
            $id = self::$prefix . $n;
            ++$n;

            // Build the tree
            $parent =& $this->_getParent($level);
            $node =& $this->_newNode($id, $text, $level);
            if (! isset($parent['children'])) {
                $parent['children'] = array();
            }
            $parent['children'][] =& $node;

            call_user_func($this->_augment_callback, $doc, $h, $id);
        }

        $body = $doc->getElementsByTagName('body')->item(0);
        $this->_html = str_replace(array("<body>\n", '</body>'), '',
                                   $doc->saveHTML($body));
    }

    /**
     * Debug function for dumping a TOC node and its children.
     *
     * @param  array  $node    The TOC node to dump
     * @param  string $indent  Indentation string.
     */
    private function _dumpBranch($node, $indent = '')
    {
        echo $indent . $node['title'] . "\n";
        if (isset($node['children'])) {
            foreach ($node['children'] as &$child) {
                $this->_dumpBranch($child, "$indent\t");
            }
        }
    }


    /**
     * Create a new TOCifier instance.
     *
     * A string containing the HTML to parse for TOC must be passed
     * in. The real processing will be triggered by the process()
     * method.
     *
     * Parsing a file can be easily performed by using
     * file_get_contents():
     *
     * <code>
     * $tocifier = new Tocifier(@file_get_content($file));
     * </code>
     *
     * @param string $html A chunk of valid HTML (UTF-8 encoded).
     */
    public function __construct($html)
    {
        $this->_raw_html = $html;
        // Default augmenting method (kept for backward compatibility)
        $this->setAugmentCallback(array(__CLASS__, 'prependAnchor'));
    }

    /**
     * Change the augment method used by this Tocifier instance.
     *
     * By default the HTML is augmented prepending an anchor before
     * every valid destination. This behavior can be changed by using
     * Tocifier::setId() (that directly sets the ID on the destination
     * elements) or by providing your own callback.
     *
     * The signature of the callback to pass in should be compatible
     * with:
     *
     *     function callback(DOMDocument $dom, DOMElement $element, $id)
     *
     * @param callable $callback  The new function to call for
     *                            augmenting DOMElement
     */
    public function setAugmentCallback($callback)
    {
        $this->_augment_callback = $callback;
    }

    /**
     * Parse and process the HTML chunk.
     *
     * The parsing phase involves picking up all the HTML header
     * elements (from <h1> to <h6>), so if the HTML is not well formed
     * or any other error is encountered this function will fail.
     *
     * @return boolean true on success, false on errors.
     */
    public function process()
    {
        // Check if $this->_raw_html is valid
        if (! is_string($this->_raw_html) || empty($this->_raw_html)) {
            return false;
        }

        // DOMDocument sucks ass (welcome to PHP, you poor shit). I
        // really don't understand why it is so difficult for loadHTML()
        // to read a chunk of text in UTF-8...
        $html = mb_convert_encoding($this->_raw_html, 'HTML-ENTITIES', 'UTF-8');

        // Parse the HTML into a DOMDocument tree
        $doc = new DOMDocument();
        if (! @$doc->loadHTML($html)) {
            return false;
        }

        // Process the doc
        $this->_processDocument($doc);
        return true;
    }

    /**
     * Get the TOC (Table Of Contents) from the provided HTML.
     *
     * The HTML must be provided throught the constructor.
     *
     * The TOC is represented in the form of:
     *
     * <code>
     * array(
     *     array('id'       => 'TOC-1',
     *           'title'    => 'Item 1',
     *           'children' => array(
     *               array('id'       => 'TOC-2',
     *                     'title'    => 'Subitem 1.1'
     *               ),
     *               array('id'       => 'TOC-3',
     *                     'title'    => 'Subitem 1.2',
     *                     'children' => array(
     *                         array('id'      => 'TOC-4',
     *                               'title    => 'Subsubitem 1.2.1'
     *     ))))),
     *     array('id'       => 'TOC-5,
     *           'title'    => 'Item 2',
     *           'children' => array(
     *               array('id'       => 'TOC-6',
     *                     'title'    => 'Subitem 2.1'
     *               ),
     *               array('id'       => 'TOC-7',
     *                     'title'    => 'Subitem 2.2'
     * ))));
     * </code>
     *
     * The TOC is cached, so subsequent calls will return the same tree.
     *
     * @return Array An array representing the TOC. A valid array is
     *               always returned.
     */
    public function getTOC()
    {
        return isset($this->_tree['children']) ? $this->_tree['children'] : array();
    }

    /**
     * Get the HTML augmented for proper navigation.
     *
     * The HTML must be provided throught the feedHtml() method.
     * The returned string is cached, so subsequent calls will return
     * the same string without further processing.
     *
     * @return String The augmented HTML.
     */
    public function getHtml()
    {
        return $this->_html;
    }

    /**
     * Dump the TOC to stdout for debugging purpose.
     */
    public function dumpTOC()
    {
        $this->_dumpBranch($this->_tree);
    }

    /**
     * Augment a DOMElement by prepending an anchor.
     *
     * An HTML fragment such as:
     *
     *     <h1>First</h2>
     *     <h2>Second</h1>
     *
     * will become:
     *
     *     <a id="TOC-1" class="anchor"></a><h1>First</h2>
     *     <a id="TOC-2" class="anchor"></a><h2>Second</h1>
     *
     * @param DOMDocument $dom      The DOM owning $element
     * @param DOMElement  $element  The element to augment
     * @param string      $id       The destination ID
     */
    public static function prependAnchor(DOMDocument $dom, DOMElement $element, $id)
    {
        $anchor = $dom->createElement('a');
        $anchor->setAttribute('id', $id);
        $anchor->setAttribute('class', 'anchor');
        $element->parentNode->insertBefore($anchor, $element);
    }

    /**
     * Augment a DOMElement by setting its ID.
     *
     * An HTML fragment such as:
     *
     *     <h1>First</h2>
     *     <h2>Second</h1>
     *
     * will become:
     *
     *     <h1 id="TOC-1" class="anchor">First</h2>
     *     <h2 id="TOC-2" class="anchor">Second</h1>
     *
     * @param DOMDocument $dom      The DOM owning $element
     * @param DOMElement  $element  The element to augment
     * @param string      $id       The destination ID
     */
    public static function setId(DOMDocument $dom, DOMElement $element, $id)
    {
        $element->setAttribute('id', $id);
        $element->setAttribute('class', 'anchor');
    }
}
