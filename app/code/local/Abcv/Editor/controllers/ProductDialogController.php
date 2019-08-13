<?php
class Abcv_Editor_ProductDialogController extends Mage_Core_Controller_Front_Action
{
    public function indexAction(){
        //check method POST
        if ($this->getRequest()->isPost()) {
            $result = array();
            //get Param
            $quoteId = $this->getRequest()->getPost('quoteId');
            $result['quoteId'] = $quoteId;
            //get customer id
            $customerData_id = 0;
            if(Mage::getSingleton('customer/session')->isLoggedIn()) {
                $customerData = Mage::getSingleton('customer/session')->getCustomer();
                $customerData_id = $customerData->getId();
            }
            
            $result['customerId'] = $customerData_id;
            
            //getting product
            $quoteItem = $cart = Mage::getModel('checkout/cart')->getQuote()->getItemById($quoteId);//getting product of cart
            if ($quoteItem == NULL)
            {
            	$result['result'] = 'error';
                $result['message'] = 'Sorry, no quote item.'; 
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                return;
            }
            $templateid =$quoteItem->getOptionByCode('templateid')->getValue(); //template use to design 
            $_product = $quoteItem->getProduct();//getting Product of magento
            
            //getting attribute
            $price = $quoteItem->getPrice(); //getting Price
            $productName = $_product->getName();    //getting Product Name
            $qty = $quoteItem->getQty();    //getting Qty
            $specialPrice = $_product->getSpecialPrice();   //getting Special Price
            $model = Mage::getModel('catalog/product'); //getting product model
            $_product = $model->load($_product->getId()); //getting product object for particular product id
            $desription = $_product->getDescription();  //getting Description    
            $_tierPrices = $_product->getTierPrice();   //getting tierPrices
            
            Mage::helper('weee')->processTierPrices($_product, $_tierPrices);

            $var_quantities = '';
            $var_prices = '';
            $var_saves = '';
            $var_real_prices = '';
            $countTierPrices = 0;
            foreach ($_tierPrices as $_index => $_price){
                $var_quantities .= round($_price['price_qty']);
                $var_prices .= $_price['formated_price_incl_weee_only'];
                $var_saves .= number_format(100-$_price['price']*100/$price).'%';
                $var_real_prices .= $_price['price'];
                $countTierPrices++;
                if($countTierPrices != count($_tierPrices))
                {
                    $var_quantities .= ',';$var_prices .= ',';$var_saves .= ','; $var_real_prices .= ',';     
                }
            }
            
            //getting product option
            //$helper = Mage::helper('catalog/product_configuration');
            //$opList = $helper->getCustomOptions($quoteItem);
            $qtopList = $quoteItem->getBuyRequest()->getData('options');    //get quote options
            $productoption = $_product->getOptions();
            $colorSelectData = array();
    		$colorSelectData['options'] = array();
            foreach($productoption as $optionKey => $optionVal)
            {   
                if ($optionVal->getType() == 'drop_down'){
                	$optionArr = $optionVal->toArray();
                    $is_color_option = $optionArr['dyo_option'] == 'dyocolor';        	
        			$jsFunc = $is_color_option ? "changeColorProductOptions(this.value)" : "changeSelectProductOptions()";
            		$optStr .= "<p><label class='productoptionsLabel'><select onchange='javascript:". $jsFunc .";' class='productoptions' id='options_".$optionVal->getId()."_".$optionVal->getIsrequire()."' name='".$optionVal->getTitle()."' title='". $optionVal->getDescription() ."'>";
                    foreach($optionVal->getValues() as $valuesKey => $valuesVal)
                    {
                    	if($is_color_option){
		            		$colorSelectData['id'] = "options_". $optionVal->getId(). "_". $optionVal->getIsrequire();
		            		$colorSelectData['options'][] = $valuesVal->toArray();
		            	}
                        $optStr .= "<option value='".$valuesVal->getId()."_".$valuesVal->getPrice()."' ";
                        foreach($qtopList as $qtopKey => $qtopVal)
                            if ($qtopKey == $optionVal->getId() && $qtopVal == $valuesVal->getId())
                                $optStr .= " selected ";    
                        $optStr .= ">";
                        $optStr .= $valuesVal->getTitle();
                        if ($valuesVal->getPrice() != 0 &&  !$is_color_option)
                             $optStr .= ' +'. Mage::app()->getStore()->formatPrice($valuesVal->getPrice());
                        $optStr .= "</option>";
                    }
                    $optStr .= "</select></label></p>";            
                }
            }
            
            //get template color
            $id_array = explode("_", $templateid);
            $templateids = "";
            foreach($id_array as $item){
            	if(empty($item)) continue;
            	$templateids .= $item . ",";
            }
            $templateids = substr($templateids, 0, strlen($templateids) - 1);
            $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
            $countColorList = $readConnection->fetchCol('SELECT CountColor FROM Templates where id in ('. $templateids .')');
   			
   			$countColor = 0;
   			foreach($countColorList as $item){
   				$countColor += $item;
   			}
   			
            $result['templateId'] =$templateid;
            $result['countColor'] =$countColor;
            $result['price'] = Mage::app()->getStore()->formatPrice($price);
            $result['productName'] = $productName;
            $result['qty'] = $qty;
            $result['specialPrice'] = Mage::app()->getStore()->formatPrice($specialPrice);
            if ($specialPrice == 0 || $specialPrice=='')
                $result['numberPrice'] = $price;
            else
                $result['numberPrice'] = $specialPrice;
            $result['productId'] = $_product->getId();
            $result['desription'] = $desription;
            $result['var_quantities'] = $var_quantities;
            $result['var_prices'] = $var_prices;
            $result['var_saves'] = $var_saves;
            $result['var_real_prices'] = $var_real_prices;
            $result['productOptions'] = $optStr;
            $result['one_qty_price'] = $_product->getPrice();
            $result['colorSelectData'] = $colorSelectData;
            $result['site_id'] = str_replace('sid','',Mage::app()->getStore()->getCode());
            $result['currencySymbol'] = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
    
	public function getApparelQuantityAction(){
		$result = array();
		if ($this->getRequest()->isPost()) {
            //get Param
            $quoteId = $this->getRequest()->getPost('quoteId');
            $apparelOptionId = $this->getRequest()->getPost('apparelOptionId');
            
            //getting product
	        $quoteItem = $cart = Mage::getModel('checkout/cart')->getQuote()->getItemById($quoteId);//getting product of cart
	        if ($quoteItem == NULL)
	        {
	        	$result['result'] = 'error';
	            $result['message'] = 'Sorry, no quote item.'; 
	            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	            return;
	        } 
	        $_product = $quoteItem->getProduct();
	        $apparelOption = $quoteItem->getBuyRequest()->getData('apparel');
	        $productoption = $_product->getOptions();
	        $apparelFieldGroupData = array();
	        foreach($productoption as $optionKey => $optionVal)
		    {   
		        if($optionVal->getId() == $apparelOptionId){
	        		$apparelFieldGroupData['id'] = $optionVal->getId();
	        		$apparelFieldGroupData['title'] = $optionVal->getTitle();
	        		$apparelFieldGroupData['isRequire'] = $optionVal->getIsrequire();
		        	foreach($optionVal->getValues() as $valuesKey => $valuesVal){  
		        		$itemArray = $valuesVal->toArray();
		        		if(isset($apparelOption[$itemArray['option_id']])
		        		   && !empty($apparelOption[$itemArray['option_id']][$itemArray['option_type_id']])){
		        		   	 $itemArray['value'] = $apparelOption[$itemArray['option_id']][$itemArray['option_type_id']];
		        		}else{
		        			$itemArray['value'] = '';
		        		}
		        		$apparelFieldGroupData['options'][] = $itemArray;
		        	}
		        	break;
		        }
		    }
		    $result['quoteId'] = $quoteId;
		    $result['result'] = 'success';
		    $result['apparelFieldGroupData'] = $apparelFieldGroupData;
		}else{
			$result['result'] = 'error';
	    	$result['message'] = 'Invalid request.';
		}
		 
	    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	            
	}
	
	
	
	     
}