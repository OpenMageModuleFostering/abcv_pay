<?php
class Abcv_Editor_Model_Observer
{
	
	public function setCartCondition(Varien_Event_Observer $observer){

		$curr_time = time();
		$quote_time = Mage::getSingleton('core/session')->quote_time;
		if($quote_time == null || $curr_time > $quote_time){
			Mage::getSingleton('core/session')->setCustomshippingcostAmount(0);
			    Mage::getSingleton('core/session')->setCustomshippingcostDescription(null);
			    	   Mage::getSingleton('core/session')->proposal_quote_id = null; 
				   
				   		Mage::getSingleton('core/session')->shippingcostAmount = 0;
	    Mage::getSingleton('core/session')->shippingcostDescription = '';

		}
		
		
	}
    public function modifyPrice(Varien_Event_Observer $observer)
    {

         


        $customPrice = Mage::registry('customPrice');
        if (!empty($customPrice) && $customPrice > 0) {

                $event = $observer->getEvent();
                $quote_item = $event->getQuoteItem();


        //Abcv_Editor_Model_Quote_Item
    	 $quote_item->setCustomPrice($customPrice)->setOriginalCustomPrice($customPrice);
        }


    }
    
    public function getPriceFromTierPricesQty ($item_qty, $tierPrices)
    {
        if ($tierPrices == null || count($tierPrices) == 0 || $item_qty == 1)
            return 0;
        $price = 0;
        $previous_tierPrices = null;
        foreach ($tierPrices as $_index => $item_tierPrices)
        {
            $_qty = $item_tierPrices['qty'];
            if($item_qty > $_qty){
				//don't thing
            }
            elseif ($item_qty < $_qty)
            {
				$price = $previous_tierPrices['price'];
            }
            elseif ($item_qty == $_qty)
            {
				$price += $item_tierPrices['price'];
				break;
            }
            $previous_tierPrices = $item_tierPrices;
        }
        if ($price == 0 && $previous_tierPrices != null && $item_qty > $previous_tierPrices['qty'])
        {
            $price += $previous_tierPrices['price'];
        }
        return $price;
    }
    public function updatePrice($e)
    {
    	
/*
 * <!-- 2014-05-14 DANG COMMENT- change to use rewrite method
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $site_id = str_replace('sid','',Mage::app()->getStore()->getCode());
        if ($site_id == "default") $site_id = 6;
        $params = null;
        if ($e->getRequest() != null)
            $params = $e->getRequest()->getParams();
        foreach($quote->getAllVisibleItems() as $item) {

            $item_id = $item->getBuyRequest()->getData('id');
            if (!isset($params['id']))
            {
                $params['id'] = $item_id;
                $params['is_not_customize'] = $item->getBuyRequest()->getData('is_not_customize');
                $params['options'] = $item->getBuyRequest()->getData('options');
                
            }
            if ($params['id'] == $item_id){
                $is_not_customize = $params['is_not_customize'];
                if ($is_not_customize != null && $is_not_customize == 0) //Customer desgin template
                {   
                    $product = $item->getProduct();
                    $item_qty = $item->getQty();
                    $price = (double)($product->getPrice());
                  	$options= $params['options'];
                    $productoption = $product->getOptions();
                    foreach($productoption as $optionKey => $optionVal)
                    {   
                        //get is_color_option
                        $optionArr = $optionVal->toArray();
                        foreach ($options as $key_group_id => $option_id){
                            if ($key_group_id == $optionVal->getId()){
                                $is_color_option = $optionArr['dyo_option'] == 'dyocolor';
                                foreach($optionVal->getValues() as $valuesKey => $valuesVal)
                                {
                                     if($option_id ==$valuesVal->getId())
                                     {
                                        //$flag_price = true;
                                        //getting tier Prices
                                        if($is_color_option){ //color
                                            $price -= (double)($product->getPrice());
                                            $value = $valuesVal->toArray();
                                            $tierPrices = $value['tiers'];
                                            if ($item_qty != 1 && $tierPrices != null){
                                                $price += $this->getPriceFromTierPricesQty($item_qty, $tierPrices);
                                            }
                                            else
                                            {
                                                $price += $valuesVal->getPrice(); 
                                            }
                                       	}
                                        else
                                        {
                                            $price += $valuesVal->getPrice();
                                        }         
                                     }
                                }    
                            }
                       	                
                        }
                    }
        //getting tier price
                    if ($item_qty != 1)
                    {
                        $_tierPrices = $product->getTierPrice();
                        Mage::helper('weee')->processTierPrices($product, $_tierPrices);
                        $tierPricesArr = array();
                        foreach ($_tierPrices as $_index => $_price){
                             $tierPrice = array(
                                'qty' => $_price['price_qty'],
                                'price' => $_price['price'],
                             );
                             $tierPricesArr[] = $tierPrice;
                        }
                        if (count($tierPricesArr) != 0)
                        {
                            $price -= (double)($product->getPrice());
                            $price += $this->getPriceFromTierPricesQty($item_qty, $tierPricesArr);
                        }
                    }
                
                    $productid = $product->getId();
                    
                    $query = "SELECT * FROM abcv_product_options_checkbox WHERE site_id='$site_id' And product_id = '$productid' And attr = 'AllowCustomizeTemplate'";
                    $customize_template = $read->fetchRow($query);
                    $is_notCustomize_template = false;	
                    //missed: check stock product
                    if ($customize_template != null && $customize_template['value'] == 'true')
                        $is_notCustomize_template = true;
                    
                    if ($is_notCustomize_template)
                    {
                        $query = "SELECT * FROM abcv_product_options_checkbox WHERE site_id='$site_id' And product_id = '$productid' And attr = 'Price'";
                        $customize_price = $read->fetchRow($query);
                        $customize_price = ($customize_price==null)?0:$customize_price['value'];
                        $price += (double)($customize_price);
                    }
                    // Set the custom price
                    $item->setCustomPrice($price);
                    $item->setOriginalCustomPrice($price);
                    // Enable super mode on the product.
                    $item->getProduct()->setIsSuperMode(true);    
                }
            }
        }
        $quote->save();
//fclose($file);
 
 <!-- 2014-05-14 END DANG COMMENT- change to use rewrite method*/
    }
   
    public function hookToControllerActionPreDispatch(Varien_Event_Observer $observer)
    {

       
        if($observer->getEvent()->getControllerAction()->getFullActionName() == 'checkout_cart_add') 
        {

          
            Mage::dispatchEvent("add_to_cart_before", array('request' => $observer->getControllerAction()->getRequest()));
        }
    }
 
    public function hookToAddToCartBefore(Varien_Event_Observer $observer) 
    {   
       


        $params = $observer->getEvent()->getRequest()->getParams();


        /** HUY: begin 2014-04-16 **/
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $site_id = str_replace('sid','',Mage::app()->getStore()->getCode());
        if ($site_id == "default") $site_id = 6;
        $productid = $params['product'];
        $query = "SELECT * FROM abcv_product_options_checkbox WHERE site_id='$site_id' And product_id = '$productid' And attr = 'AllowCustomizeTemplate'";
        $customize_template = $read->fetchRow($query);
        $is_has_allow_customize = '0';
        if ($customize_template != null && $customize_template['value'] == 'true')
            $is_has_allow_customize = '1';
           

        /** HUY: end 2014-04-16 **/

        Mage::app()->getRequest()->setParam('is_has_allow_customize',  $is_has_allow_customize);
        Mage::app()->getRequest()->setParam('add_to_cart_from',  'the_product_page');
    }

    public function hookToQquoteadvControllerActionPreDispatch(Varien_Event_Observer $observer)
    {
	$action_name = $observer->getEvent()->getControllerAction()->getFullActionName();
        if($action_name == 'qquoteadv_view_history' || $action_name == 'qquoteadv_index_gotoquote') 
        {

          
            Mage::dispatchEvent("view_qquoteadv_history_before", array('request' => $observer->getControllerAction()->getRequest()));
        }
    }
 

    public function hookToViewQquoteadvHistoryBefore(Varien_Event_Observer $observer){

        $params = $observer->getEvent()->getRequest()->getParams();

Mage::log(var_export($params, true), null, 'mylogfile.log');
       

        if(isset($params['key']) && isset($params['quote']) 
            //&&  Mage::getModel('qquoteadv/qqadvcustomer')->load($params['quote'])->status != Ophirah_Qquoteadv_Model_Status::STATUS_CONFIRMED 
            ){
            // new data

            $quoteId = $params['quote'];

             // Mage::getSingleton('core/session')->proposal_quote_id = $quoteId; 
            Mage::getSingleton('core/session')->setProposalQuoteId($quoteId);

             $encrypt_email = str_replace("abcvirtual",'/', $params['key']);

            $encrypt_email = str_replace(" ",'+', $encrypt_email);

             $a_received_email = explode('-', $this->fnDecrypt($encrypt_email, 'abcv'));

             $received_email = isset($a_received_email['1']) ? $a_received_email['1'] : '';

      
            $quote_status = Mage::getModel('qquoteadv/qqadvcustomer')->load($params['quote'])->status;

            if (Mage::getSingleton('customer/session')->isLoggedIn()){
                // login

                $email = Mage::getSingleton('customer/session')->getCustomer()->getEmail();


                if ($email == $received_email){
                    //same
                    $add_to_checkout = true;
                  
                }else{
                    //not same
                    $add_to_checkout = false;
                    Mage::getSingleton('core/session')->setMyReceivedEmailVariable($received_email);

                    if (
                        $quote_status != Ophirah_Qquoteadv_Model_Status::STATUS_ORDERED &&
                        $quote_status != Ophirah_Qquoteadv_Model_Status::STATUS_PROPOSAL_EXPIRED &&
                        $quote_status != Ophirah_Qquoteadv_Model_Status::STATUS_CANCELED

                        ){
                        // status not ordered, expired, canceled

                        Mage::app()->getResponse()->setRedirect(Mage::getUrl("qquoteadv/view/account", array('quote'=>$quoteId)));
                    }

                }

            }else{
                // no login
                $add_to_checkout = true;

                 $customer = Mage::getModel('customer/customer');
                 $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
                 $customer->loadByEmail(trim($received_email));

                 Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer)
                    ->renewSession();


            }

 if ($quote_status == Ophirah_Qquoteadv_Model_Status:: STATUS_ORDERED ||

                 $quote_status == Ophirah_Qquoteadv_Model_Status:: STATUS_PROPOSAL_EXPIRED ||
                $quote_status == Ophirah_Qquoteadv_Model_Status:: STATUS_CANCELED
                        ){
                

                Mage::app()->getResponse()->setRedirect(Mage::getUrl("qquoteadv/view/history"));
                $add_to_checkout = false;
            }
Mage::log(var_export($params, true), null, 'mylogfile.log');
Mage::log("1". $add_to_checkout, null, 'mylogfile.log');
            if ($add_to_checkout){
                
                // update quotation status

                $data = array(
                            'updated_at' => NOW(),
                            'status' => Ophirah_Qquoteadv_Model_Status::STATUS_CONFIRMED
                        );
                Mage::getModel('qquoteadv/qqadvcustomer')->updateQuote($quoteId, $data)->save();

            
                $cart = Mage::getModel('checkout/cart');                
                $cart->truncate(); // remove all active items in cart page


                $quote = Mage::getSingleton('qquoteadv/qqadvcustomer')->load($quoteId);

 
        		
        		$resource = Mage::getSingleton('core/resource');
                $read = $resource->getConnection('core_read');
                $tblProduct = $resource->getTableName('quoteadv_product');
                $tblRequestItem = $resource->getTableName('quoteadv_request_item');

                $sql = "select * from $tblProduct p INNER JOIN $tblRequestItem i
                                    ON p.quote_id=i.quote_id 
                                    AND i.quoteadv_product_id=p.id AND p.quote_id=$quoteId";     

                $data = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($sql);

                //add items from quote to cart
                // $cart = Mage::getSingleton('checkout/cart')->init();
                $cart = Mage::getModel('checkout/cart')->init();


                foreach ($data as $item) {
                    $productId = $item['product_id'];

                    $product = Mage::getModel('catalog/product')->load($productId);

                  

                    //observer will check customPrice after add item to card/quote

            Mage::register('customPrice', $item['owner_cur_price']);

                    if ($product->getTypeId() == 'bundle') {
                        $attr = array();
                        $attr[$productId] = @unserialize($item['attribute']);
                        $attr[$productId]['qty'] = (int)$item['request_qty'];                
                        $cart->addProduct($attr);
                    } else {
                        $params = @unserialize($item['attribute']);
                        $params['qty'] = (int)$item['request_qty'];

                try {
                	$cart->addProduct($product, $params);
                } catch (Exception $e) {
                    Mage::log($e->getMessage());
                }
            }
            Mage::unregister('customPrice');
        }
	   $cart->save();
	   //Set cusstom shipping method
        	   	   Mage::getSingleton('core/session')->quote_time = time() + 30;
        		Mage::getSingleton('core/session')->shippingcostAmount = $quote->getData('shipping_price');
	    Mage::getSingleton('core/session')->shippingcostDescription = $quote->getData('shipping_method_title');

        	  	   Mage::getSingleton('core/session')->proposal_quote_id = $quoteId; 
               Mage::app()->getResponse()->setRedirect(Mage::getUrl("checkout/onepage", array('quoteadv' => $quoteId)));
            }

        }else{
            // old data
            // do nothing
        }

    }
    
    public function setC2qRefNumberAndStatus(Varien_Event_Observer $observer){
    	$order = $observer->getOrder();
        if ($quoteId = Mage::getSingleton('core/session')->proposal_quote_id) {
            $order->setData('c2q_internal_quote_id', $quoteId);
        }        
        $data = array(
	                'updated_at' => NOW(),
	                'status' => Ophirah_Qquoteadv_Model_Status::STATUS_ORDERED
	            );
        Mage::getModel('qquoteadv/qqadvcustomer')->updateQuote($quoteId, $data)->save();
	Mage::getSingleton('core/session')->proposal_quote_id = null;
    }
    
    public function fnDecrypt($sValue, $sSecretKey)
    {
        return rtrim(
            mcrypt_decrypt(
                MCRYPT_RIJNDAEL_256,
                $sSecretKey,
                base64_decode($sValue),
                MCRYPT_MODE_ECB,
                mcrypt_create_iv(
                    mcrypt_get_iv_size(
                        MCRYPT_RIJNDAEL_256,
                        MCRYPT_MODE_ECB
                    ),
                    MCRYPT_RAND
                )
            ), "\0"
        );
    }
 
}