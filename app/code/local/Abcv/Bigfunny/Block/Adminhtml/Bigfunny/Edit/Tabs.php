<?php
class Abcv_Bigfunny_Block_Adminhtml_Bigfunny_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('bigfunny_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle($this->__('Bigfunny Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => $this->__('Dimensions Information'),
          'title'     => $this->__('Dimensions Information'),
          'content'   => $this->getLayout()->createBlock('abcv_bigfunny/adminhtml_bigfunny_edit_tab_form')->toHtml(),
      ));

      return parent::_beforeToHtml();
  }
}