<?php
class Abcv_Editor_Block_Adminhtml_Editor extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_editor';
    $this->_blockGroup = 'editor';
    $this->_headerText = Mage::helper('editor')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('editor')->__('Add Item');
    parent::__construct();
  }
}