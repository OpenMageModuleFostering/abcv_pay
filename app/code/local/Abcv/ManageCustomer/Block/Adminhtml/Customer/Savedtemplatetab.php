<?php
class Abcv_ManageCustomer_Block_Adminhtml_Customer_Savedtemplatetab
extends Mage_Adminhtml_Block_Template
implements Mage_Adminhtml_Block_Widget_Tab_Interface {
    
    private $read;    
    private $write;
    
   /**
     * Set the template for the block
     *
     */
    public function _construct()
    {
        parent::_construct();
       $this->setTemplate('abcv/managecustomer/saved_template.phtml');
        $this->read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $this->write = Mage::getSingleton('core/resource')->getConnection('core_write');
    }
    
    /**
     * Get Data
     * */
    public function getCurrentCustomer(){
        $customer = Mage::registry('current_customer');
        return $customer; 
    }
    /** 
     * Get Saved Template
     * */
    public function getSavedTemplate($customer_id)
    {
        $query = "select * from abcv_product_save where customer = 'Value'";   
        $result = $this->read->fetchAll();




    }
    
    
   /**
     * Retrieve the label used for the tab relating to this block
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Saved Templates');
    }
   /**
     * Retrieve the title used by this tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Click here to view your custom tab content');
    }
   /**
     * Determines whether to display the tab
     * Add logic here to decide whether you want the tab to display
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }
    /**
     * Stops the tab being hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
    
    public function getHTML(){
        $this->setTemplate('abcv/managecustomer/ajax/_template.phtml');
    }
}