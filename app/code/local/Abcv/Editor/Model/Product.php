<?php

class Abcv_Editor_Model_Product extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('editor/product');
    }
}