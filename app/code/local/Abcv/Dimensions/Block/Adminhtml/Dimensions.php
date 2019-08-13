<?php
class Abcv_Dimensions_Block_Adminhtml_Dimensions extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        // The blockGroup must match the first half of how we call the block, and controller matches the second half
        // ie. foo_bar/adminhtml_baz
        $this->_blockGroup = 'abcv_dimensions';
        $this->_controller = 'adminhtml_dimensions';
        $this->_headerText = $this->__('Dimensions');
         
        parent::__construct();
    }
}