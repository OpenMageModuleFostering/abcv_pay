<?php
class Abcv_Editor_Block_Adminhtml_CustomDyotab extends Mage_Core_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct(){
        $this->setTemplate('editor/custom_dyotab.phtml');
        parent::__construct();
    }

    //Label to be shown in the tab
    public function getTabLabel(){
        return Mage::helper('core')->__('Custom DYO');
    }
    
    //Title for contact
    public function getTabTitle(){
        return Mage::helper('core')->__('Custom DYO');
    }

    public function canShowTab(){
        return true;
    }  

    public function isHidden(){
        return false;
    }
}