<?php
class Abcv_Bigfunny_Block_Adminhtml_Bigfunny_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Init class
     */
    public function __construct()
    {  
        $this->_blockGroup = 'abcv_bigfunny';
        $this->_controller = 'adminhtml_dimensions';
     
        parent::__construct();
     
        $this->_updateButton('save', 'label', Mage::helper('abcv_bigfunny')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('abcv_bigfunny')->__('Delete'));
    }  
     
    /**
     * Get Header text
     *
     * @return string
     */
    public function getHeaderText()
    {  
        if (Mage::registry('dimensions_data')->getId()) {
            return Mage::helper('abcv_bigfunny')->__('Edit Dimensions');
        }  
        else {
            return Mage::helper('abcv_bigfunny')->__('New Dimensions');
        }  
    }  
}