<?php
class Abcv_Editor_Block_ChooseTemplate extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getChooseTemplate()     
     { 
        if (!$this->hasData('chooseTemplate')) {
            $this->setData('chooseTemplate', Mage::registry('chooseTemplate'));
        }
        return $this->getData('chooseTemplate');
        
    }
}