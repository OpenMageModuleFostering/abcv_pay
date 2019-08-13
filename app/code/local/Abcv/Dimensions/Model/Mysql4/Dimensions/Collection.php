<?php
class Abcv_Dimensions_Model_Mysql4_Dimensions_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {  
        $this->_init('abcv_dimensions/dimensions');
    }  
}