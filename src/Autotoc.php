<?php

namespace eNTiDi\Autotoc;

use eNTiDi\Autotoc\Hacks;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\View\ArrayData;
use SplObjectStorage;

class Autotoc extends DataExtension
{
    /**
     * @config
     * Callable to be used for augmenting a DOMElement: specify as a
     * string in the format "class::method".  `Tocifier::prependAnchor`
     * and `Tocifier::setId` are two valid callbacks.
     */
    private static $augment_callback;

    protected static $tocifiers;


    /**
     * Initialize the Autotoc extension.
     *
     * Creates an internal SplObjectStorage where caching the table of
     * contents.
     */
    public function __construct()
    {
        parent::__construct();
        if (empty(self::$tocifiers)) {
            self::$tocifiers = new SplObjectStorage();
        }
    }

    private static function convertNode($node)
    {
        $data = new ArrayData([
            'Id'    => $node['id'],
            'Title' => $node['title']
        ]);

        if (isset($node['children'])) {
            $data->setField('Children', self::convertChildren($node['children']));
        }

        return $data;
    }

    private static function convertChildren($children)
    {
        $list = new ArrayList();

        foreach ($children as $child) {
            $list->push(self::convertNode($child));
        }

        return $list;
    }

    /**
     * Get the field name to be used as content.
     * @return string
     */
    private function contentField()
    {
        $field = $this->owner->config()->get('content_field');
        return $field ? $field : 'Content';
    }

    /**
     * Provide content_field customization on a class basis.
     *
     * Override the default setOwner() method so, when valorized, I can
     * enhance the (possibly custom) content field with anchors. I did
     * not find a better way to override a field other than directly
     * substituting it with setField().
     *
     * @param Object $owner
     */
    public function setOwner($owner)
    {
        parent::setOwner($owner);
        if ($owner) {
            Hacks::addCallbackMethodToInstance(
                $owner,
                'getContent',
                function() use ($owner) {
                    return $owner->getContentField();
                }
            );
        }
    }

    /**
     * Return the internal Tocifier instance bound to $owner.
     *
     * If not present, try to create and execute a new one. On failure
     * (e.g. because of malformed content) no further attempts will be
     * made.
     *
     * @param \SilverStripe\ORM\DataObject $owner
     * @return Tocifier|false|null
     */
    private static function getTocifier($owner)
    {
        if (!$owner) {
            $tocifier = null;
        } elseif (isset(self::$tocifiers[$owner])) {
            $tocifier = self::$tocifiers[$owner];
        } else {
            $tocifier = Injector::inst()->create(
                'eNTiDi\Autotoc\Tocifier',
                $owner->getOriginalContentField()
            );
            $callback = $owner->config()->get('augment_callback');
            if (empty($callback)) {
                $callback = Config::inst()->get(self::class, 'augment_callback');
            }
            $tocifier->setAugmentCallback(explode('::', $callback));
            if (!$tocifier->process()) {
                $tocifier = false;
            }
            self::$tocifiers[$owner] = $tocifier;
        }

        return $tocifier;
    }

    /**
     * Clear the internal Autotoc cache.
     *
     * The TOC is usually cached the first time you call (directly or
     * indirectly) getAutotoc() or getContentField(). This method allows
     * to clear the internal cache to force a recomputation.
     */
    public function clearAutotoc()
    {
        unset(self::$tocifiers[$this->owner]);
    }

    /**
     * Get the automatically generated table of contents.
     * @return ArrayData|null
     */
    public function getAutotoc()
    {
        $tocifier = self::getTocifier($this->owner);
        if (!$tocifier) {
            return null;
        }

        $toc = $tocifier->getTOC();
        if (empty($toc)) {
            return null;
        }

        return new ArrayData([
            'Children' => self::convertChildren($toc)
        ]);
    }

    /**
     * Get the non-augmented content field.
     * @return string
     */
    public function getOriginalContentField()
    {
        $model = $this->owner->getCustomisedObj();
        if (!$model) {
            $model = $this->owner->data();
        }
        if (!$model) {
            return null;
        }

        $field = $this->contentField();
        if (!$model->hasField($field)) {
            return null;
        }

        return $model->getField($field);
    }

    /**
     * Get the augmented content field.
     * @return string
     */
    public function getContentField()
    {
        $tocifier = self::getTocifier($this->owner);
        if (!$tocifier) {
            return $this->getOriginalContentField();
        }

        return $tocifier->getHTML();
    }

    /**
     * I don't remember what the hell is this...
     * @return string
     */
    public function getBodyAutotoc()
    {
        return ' data-spy="scroll" data-target=".toc"';
    }
}
