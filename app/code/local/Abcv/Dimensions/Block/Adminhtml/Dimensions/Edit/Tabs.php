<?php
class Abcv_Dimensions_Block_Adminhtml_Dimensions_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('dimensions_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle($this->__('Dimensions Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => $this->__('Dimensions Information'),
          'title'     => $this->__('Dimensions Information'),
          'content'   => $this->getLayout()->createBlock('abcv_dimensions/adminhtml_dimensions_edit_tab_form')->toHtml(),
      ));

      return parent::_beforeToHtml();
  }
}