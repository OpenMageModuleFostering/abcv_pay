<?php
class Abcv_Managecustom_Model_Mysql4_Custom_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {  
        $this->_init('abcv_managecustom/custom');
    }  
}