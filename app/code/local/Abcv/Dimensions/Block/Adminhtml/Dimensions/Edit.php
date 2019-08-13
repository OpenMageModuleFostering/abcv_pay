<?php
class Abcv_Dimensions_Block_Adminhtml_Dimensions_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Init class
     */
    public function __construct()
    {  
        $this->_blockGroup = 'abcv_dimensions';
        $this->_controller = 'adminhtml_dimensions';
     
        parent::__construct();
     
        $this->_updateButton('save', 'label', Mage::helper('abcv_dimensions')->__('Save Dimensions'));
        $this->_updateButton('delete', 'label', Mage::helper('abcv_dimensions')->__('Delete Dimensions'));
    }  
     
    /**
     * Get Header text
     *
     * @return string
     */
    public function getHeaderText()
    {  
        if (Mage::registry('dimensions_data')->getId()) {
            return Mage::helper('abcv_dimensions')->__('Edit Dimensions');
        }  
        else {
            return Mage::helper('abcv_dimensions')->__('New Dimensions');
        }  
    }  
}