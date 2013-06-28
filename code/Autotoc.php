<?php

class Autotoc extends DataExtension {

    private $_tocifier;


    private static function _convertNode($node) {
        $data = new ArrayData(array(
            'Id'    => $node['id'],
            'Title' => $node['title']
        ));

        if (isset($node['children']))
            $data->setField('Children', self::_convertChildren($node['children']));

        return $data;
    }

    private static function _convertChildren($children) {
        $list = new ArrayList;

        foreach ($children as $child)
            $list->push(self::_convertNode($child));

        return $list;
    }

    private function _getTocifier() {
        if (is_null($this->_tocifier)) {
            $tocifier = new Tocifier($this->owner->obj('Content')->forTemplate());
            $this->_tocifier = $tocifier->process() ? $tocifier : false;
        }

        return $this->_tocifier;
    }

    public function getAugmentedContent() {
        $tocifier = $this->_getTocifier();
        if (! $tocifier)
            return $this->owner->obj('Content')->forTemplate();

        return $tocifier->getHtml();
    }

    public function getAutotoc() {
        $tocifier = $this->_getTocifier();
        if (! $tocifier)
            return null;

        $toc = $tocifier->getTOC();
        if (empty($toc))
            return '';

        return new ArrayData(array(
            'Children' => self::_convertChildren($toc)
        ));
    }
};
