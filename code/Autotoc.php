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
            $html = $this->owner->getField('Content');
            $tocifier = new Tocifier($html);
            $this->_tocifier = $tocifier->process() ? $tocifier : false;
        }

        return $this->_tocifier;
    }

    public function getContent() {
        $tocifier = $this->_getTocifier();
        if (! $tocifier)
            return $this->owner->getField('Content');

        return $tocifier->getHtml();
    }

    public function getTOC() {
        $tocifier = $this->_getTocifier();
        if (! $tocifier)
            return null;

        $toc = $tocifier->getTOC();
        if (empty($toc))
            return '';

        return new ArrayData(array(
            'Title'    => _t('Autotoc.TOC', 'Table of contents'),
            'Children' => self::_convertChildren($toc)
        ));
    }
};
