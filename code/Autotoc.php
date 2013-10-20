<?php

class Autotoc extends Extension {

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

    private function _getHtml() {
        $c = $this->owner;
        $model = $c->customisedObject ? $c->customisedObject : $c->data();
        return $model ? $model->obj('Content')->forTemplate() : null;
    }

    private function _getTocifier() {
        if (is_null($this->_tocifier)) {
            $tocifier = new Tocifier($this->_getHtml());
            $this->_tocifier = $tocifier->process() ? $tocifier : false;
        }

        return $this->_tocifier;
    }

    public function getContent() {
        $tocifier = $this->_getTocifier();
        if (! $tocifier)
            return $this->_getHtml();

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

    public function getBodyAutotoc() {
        return ' data-spy="scroll" data-target=".toc"';
    }
};
