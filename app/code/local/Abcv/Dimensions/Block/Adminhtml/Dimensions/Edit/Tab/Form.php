<?php
class Abcv_Dimensions_Block_Adminhtml_Dimensions_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {  
        $form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('dimensions_form', array('legend'=>Mage::helper('abcv_dimensions')->__('Item information')));
		
		//Get 1 demension row
		$id  = $this->getRequest()->getParam('id');
		$model = Mage::getModel('abcv_dimensions/dimensions');
		$arr = $model->load($id);
		$row_dimension = $arr->_data;
		$flag = 0;
		if (!$id)//Add action
			$flag = 1;
		//DDL store, get core_store table		
		$allStores = Mage::app()->getStores();//get all store public
		$ddl_store = array();
		foreach ($allStores as $_eachStoreId => $val){
			$_storeCode = Mage::app()->getStore($_eachStoreId)->getCode(); 
			$site_id = str_replace('sid','',$_storeCode);
			if ($site_id == 'default')
				$site_id = "6";
			//create data for DDL
			$site_name = Mage::app()->getStore($_eachStoreId)->getName();			
			$ddl_store[] = array('value'=>$site_id,'label'=>$site_name);
			//edit action		
			if ($row_dimension['SiteID'] == $site_id)
				$flag = 1;
		}  

		if($flag == 1):
			//Print DDL	
			$fieldset->addField('SiteID', 'select', array(
				  'label'     => Mage::helper('abcv_dimensions')->__('SiteID'),
				  'name'      => 'SiteID',
				  'values'    => $ddl_store,
			  ));
		endif;
		//end DDL
        $fieldset->addField('Name', 'text', array(
            'name'      => 'Name',
            'label'     => Mage::helper('abcv_dimensions')->__('Name'),
            'title'     => Mage::helper('abcv_dimensions')->__('Name'),
            'required'  => true,
        ));
		$fieldset->addField('W', 'text', array(
            'name'      => 'W',
            'label'     => Mage::helper('abcv_dimensions')->__('W'),
            'title'     => Mage::helper('abcv_dimensions')->__('W'),
            'class' 	=> 'required-entry validate-digits',
			'required'  => true,
        ));
        $fieldset->addField('H', 'text', array(
            'name'      => 'H',
            'label'     => Mage::helper('abcv_dimensions')->__('H'),
            'title'     => Mage::helper('abcv_dimensions')->__('H'),
            'class' 	=> 'required-entry validate-digits',
			'required'  => true,
        ));
		$fieldset->addField('RatioW', 'text', array(
            'name'      => 'RatioW',
            'label'     => Mage::helper('abcv_dimensions')->__('RatioW'),
            'title'     => Mage::helper('abcv_dimensions')->__('RatioW'),
            'class' 	=> 'required-entry validate-digits',
			'required'  => true,
        ));
        $fieldset->addField('RatioH', 'text', array(
            'name'      => 'RatioH',
            'label'     => Mage::helper('abcv_dimensions')->__('RatioH'),
            'title'     => Mage::helper('abcv_dimensions')->__('RatioH'),
            'class' 	=> 'required-entry validate-digits',
			'required'  => true,
        ));
        if ( Mage::getSingleton('adminhtml/session')->getCorestoreData() )
		{
		  $form->setValues(Mage::getSingleton('adminhtml/session')->getCorestoreData());
		  Mage::getSingleton('adminhtml/session')->setCorestoreData(null);
		} elseif ( Mage::registry('dimensions_data') ) {
		  $form->setValues(Mage::registry('dimensions_data')->getData());
		}
		
        return parent::_prepareForm();
    }  
}