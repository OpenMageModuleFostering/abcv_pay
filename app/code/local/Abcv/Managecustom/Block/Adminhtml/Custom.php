<?php
class Abcv_Managecustom_Block_Adminhtml_Custom extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'abcv_managecustom';
        $this->_controller = 'adminhtml_custom';
        $this->_headerText = $this->__('Manage Custom Field');         
        parent::__construct();
		$this->_removeButton('add');
    }
}