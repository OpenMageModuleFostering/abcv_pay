<?php
class Abcv_Editor_Model_Quote_Item extends Mage_Sales_Model_Quote_Item
{
    public function calcRowTotal()
    {
        parent::calcRowTotal();
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        
        $product = $this->getProduct();
        $product->load($product->getId());
 		
 		//addtional fee
 		$addtional_fee = 0;
 		$apparelOptionId = 0;
 		$apparelOption = $this->getBuyRequest()->getData('apparel');
 		if(!empty($apparelOption)){
			foreach($apparelOption as $apparelKey => $apparelValue){
 				$apparelOptionId = $apparelKey;
 				break;
 			} 			
 		}
 		
        $productoption = $product->getOptions();
        foreach($productoption as $optionKey => $optionVal)
	    {   
	        if($optionVal->getId() == $apparelOptionId){
	        	foreach($optionVal->getValues() as $valuesKey => $valuesVal){  
	        		$itemArray = $valuesVal->toArray();
	        		if(isset($apparelOption[$itemArray['option_id']])
	        		   && !empty($apparelOption[$itemArray['option_id']][$itemArray['option_type_id']])){
	        		   	 $itemArray['value'] = $apparelOption[$itemArray['option_id']][$itemArray['option_type_id']];
	        		   	 $addtional_fee += $itemArray['price'] * $itemArray['value'];
	        		}
	        	}
	        	break;
	        }
	    }
	    
	    //customize_template fee	    
        $is_not_customize = $this->getBuyRequest()->getData('is_not_customize');
    	$site_id = str_replace('sid','',Mage::app()->getStore()->getCode());
    	if ($site_id == "default") $site_id = 6;
		$productid = $product->getId();
        $customize_price = Mage::helper('editor')->getCustomizePrice($productid, $site_id, $is_not_customize);
        
        // add fee
        $baseTotal = $this->getBaseRowTotal() + $addtional_fee + $customize_price;
 	
        $total = $this->getStore()->convertPrice($baseTotal);
        $this->setRowTotal($this->getStore()->roundPrice($total));
        $this->setBaseRowTotal($this->getStore()->roundPrice($baseTotal));
        
        return $this;
    }
}
