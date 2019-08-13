<?php
class Abcv_Managecustom_Adminhtml_CustomController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {  
        // Let's call our initAction method which will set some basic params for each action
        $this->_initAction()
            ->renderLayout();
    }  
     
    public function newAction()
    {  
        // We just forward the new action to a blank edit form
        $this->_forward('edit');
    }  
     
   
    protected function _initAction()
    {
        $this->loadLayout()
            // Make the active menu match the menu config nodes (without 'children' inbetween)
            ->_setActiveMenu('editor/abcv_managecustom_custom')
            ->_title($this->__('Editor'))->_title($this->__('Managecustom'))
            ->_addBreadcrumb($this->__('Editor'), $this->__('Editor'))
            ->_addBreadcrumb($this->__('Managecustom'), $this->__('Managecustom'));         
        return $this;
    }
     
    /**
     * Check currently called action by permissions for current user
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('editor/abcv_managecustom_custom');
    }
}