<?php
class Abcv_Dimensions_Adminhtml_DimensionsController extends Mage_Adminhtml_Controller_Action
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
     
    public function editAction()
    {  
        $this->_initAction();
     
        // Get id if available
        $id  = $this->getRequest()->getParam('id');
        $model = Mage::getModel('abcv_dimensions/dimensions');
     
        if ($id) {
            // Load record
            $model->load($id);
     
            // Check if record is loaded
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This baz no longer exists.'));
                $this->_redirect('*/*/');
     
                return;
            }  
        }  
     
        $this->_title($model->getId() ? $model->getName() : $this->__('New Dimensions'));
     
        $data = Mage::getSingleton('adminhtml/session')->getBazData(true);
        if (!empty($data)) {
            $model->setData($data);
        }  
     
        Mage::register('dimensions_data', $model);
     
        $this->_initAction()
            ->_addBreadcrumb($id ? $this->__('Edit Dimensions') : $this->__('New Dimensions'), $id ? $this->__('Edit Dimensions') : $this->__('New Dimensions'))
            ->_addContent($this->getLayout()->createBlock('abcv_dimensions/adminhtml_dimensions_edit')->setData('action', $this->getUrl('*/*/save')))
            ->_addLeft($this->getLayout()->createBlock('abcv_dimensions/adminhtml_dimensions_edit_tabs'))
			->renderLayout();
    }
     
    public function saveAction()
    {
        if ($postData = $this->getRequest()->getPost()) {
            $model = Mage::getSingleton('abcv_dimensions/dimensions');
            $model->setData($postData)->setId($this->getRequest()->getParam('id'));
 
            try {
                $model->save();
 
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The baz has been saved.'));
                $this->_redirect('*/*/');
 
                return;
            }  
            catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('An error occurred while saving this baz.'));
            }
 
            Mage::getSingleton('adminhtml/session')->setBazData($postData);
            $this->_redirectReferer();
        }
    }
     
    public function messageAction()
    {
        $data = Mage::getModel('abcv_dimensions/dimensions')->load($this->getRequest()->getParam('id'));
        echo $data->getContent();
    }
    public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('abcv_dimensions/dimensions');

				$model->setId($this->getRequest()->getParam('id'))
					->delete();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Dimension was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}
    /**
     * Initialize action
     *
     * Here, we set the breadcrumbs and the active menu
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initAction()
    {
        $this->loadLayout()
            // Make the active menu match the menu config nodes (without 'children' inbetween)
            ->_setActiveMenu('editor/abcv_dimensions_dimensions')
            ->_title($this->__('Editor'))->_title($this->__('Dimensions'))
            ->_addBreadcrumb($this->__('Editor'), $this->__('Editor'))
            ->_addBreadcrumb($this->__('Dimensions'), $this->__('Dimensions'));
         
        return $this;
    }
     
    /**
     * Check currently called action by permissions for current user
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('editor/abcv_dimensions_dimensions');
    }
}