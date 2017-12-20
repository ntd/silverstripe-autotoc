<?php

namespace eNTiDi\Autotoc;

use eNTiDi\Autotoc\Tocifier;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;

class AutotocExtension extends Extension
{
    /**
     * @config
     * Callable to be used for augmenting a DOMElement.
     * Look at Tocifier::prependAnchor and Tocifier::setId as
     * implementation samples.
     */
    private static $augment_callback;

    private $tocifier;


    private static function convertNode($node)
    {
        $data = ArrayData::create([
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
        $list = ArrayList::create();

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
     * @param Object      $owner      The owner instance
     * @param string|null $base_class The name of the base class this
     *                                extension is applied to
     */
    public function setOwner($owner, $base_class = null)
    {
        parent::setOwner($owner, $base_class);

        if ($owner) {
            $tocifier = $this->getTocifier();
            $content  = $tocifier ? $tocifier->getHtml() : $this->getHtml();
            $owner->setField($this->contentField(), $content);
        }
    }

    /**
     * @return string
     */
    private function getHtml()
    {
        $c = $this->owner;
        $model = $c->customisedObject ? $c->customisedObject : $c->data();
        if (! $model) {
            return null;
        }

        $field = $this->contentField();
        if (! $model->hasField($field)) {
            return null;
        }

        return $model->obj($field)->forTemplate();
    }

    /**
     * Return the internal Tocifier instance bound to this Autotoc.
     *
     * If not preset, try to create and execute a new one. On failure
     * (e.g. because of malformed content) no further attempts will be
     * made.
     *
     * @return Tocifier|false
     */
    private function getTocifier()
    {
        if (is_null($this->tocifier)) {
            $tocifier = new Tocifier($this->getHtml());
            // TODO: not sure this is the best approach... maybe I
            // should look to $this->owner->dataRecord before
            $config = Config::inst()->get(__CLASS__, 'augment_callback');
            // Take only the first two, because SilverStripe merges
            // arrays with the same key instead of overwriting them
            $tocifier->setAugmentCallback(array_slice($config, 0, 2));
            $this->tocifier = $tocifier->process() ? $tocifier : false;
        }

        return $this->tocifier;
    }

    public function getAutotoc()
    {
        $tocifier = $this->getTocifier();
        if (! $tocifier) {
            return null;
        }

        $toc = $tocifier->getTOC();
        if (empty($toc)) {
            return '';
        }

        return ArrayData::create([
            'Children' => self::convertChildren($toc)
        ]);
    }
    /**
     * @return string
     */
    public function getBodyAutotoc()
    {
        return ' data-spy="scroll" data-target=".toc"';
    }
}
