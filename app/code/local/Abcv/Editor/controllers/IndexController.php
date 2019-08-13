<?php
class Abcv_Editor_IndexController extends Mage_Core_Controller_Front_Action
{
    //design editor html5 
    public function templateAction()
    {
        $templateid = $this->getRequest()->getParam('template');
        $productid = $this->getRequest()->getParam('product');
        if(!is_numeric($productid))
        {
        	$productid=$productid.".html";
        	$dq = "SELECT product_id
        		FROM core_url_rewrite
				WHERE request_path='$productid'
				";
			$read = Mage::getSingleton('core/resource')->getConnection('core_read'); 
			$readresult = $read->query($dq);
			$drow = $readresult->fetch();
			if(isset( $drow["product_id"]))
				$productid=$drow["product_id"];
			else 
			{
			 	$this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
			    $this->getResponse()->setHeader('Status','404 File not found');
			
			    $pageId = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_NO_ROUTE_PAGE);
			    if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
			        $this->_forward('defaultNoRoute');
			    }
			}
        }
    	
        $model = Mage::getModel('catalog/product');
		$_product = $model->load($productid);
		$productstyle = $_product->getProductstyle();//get Product Style Attribute
        
        $editor = $this->getRequest()->getParam('editor'); //when user edit design
        
        //From save template
        $productstyleid = $this->getRequest()->getParam('productstyle');
        if ($productstyleid)
            $productstyle = $productstyleid;
        $saveId = $this->getRequest()->getParam('save');
        if ($saveId == "")
            $saveId = -1;
        
        //Reviews by Product ID
        $reviews = Mage::getModel('review/review')
            ->getResourceCollection()
            ->addStoreFilter(Mage::app()->getStore()->getId()) 
            ->addEntityFilter('product', $productid)
            ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
            ->setDateOrder()
            ->addRateVotes();
        $countReviews = count($reviews);
        
        //Rating
        $avg = 0;
        $ratings = array();
        if (count($reviews) > 0) {
            foreach ($reviews->getItems() as $review) {
                foreach( $review->getRatingVotes() as $vote ) {
                    $ratings[] = $vote->getPercent();
                }
            }
            $avg = array_sum($ratings)/count($ratings);
        } 
        
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Design Your Own'));
        $block = $this->getLayout()->getBlock('editor');
        $block->setData('templateid',$templateid);
        $block->setData('productid',$productid);
        $block->setData('editor',$editor);
        
        //$block->setData('productstyleid',$productstyleid);
        
        $block->setData('save',$saveId);
        $block->setData('productstyle',$productstyle);
        
        $block->setData('countReviews',$countReviews);
        $block->setData('countRating',$avg);
        
        $this->renderLayout();
    }
    
    //design editor html5 
    public function editorAction()
    {
        $templateid = $this->getRequest()->getParam('template');
        $productid = $this->getRequest()->getParam('product');
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Saved Product Template'));
        $block = $this->getLayout()->getBlock('editor');
        $block->setData('templateid',$templateid);
        $block->setData('productid',$productid);
		$this->renderLayout();
    }
    
    /*
     * Select template
     */
     public function choosetemplateAction(){
     	$productid = $this->getRequest()->getParam('product');
     	$model = Mage::getModel('catalog/product');
     	$_product = $model->load($productid);
		$productstyle = $_product->getProductstyle();
		
        $this->loadLayout();
        $block = $this->getLayout()->getBlock('choosetemplate');
        $block->setData('productid',$productid);
        $block->setData('productstyle',$productstyle);
    	echo $block->toHtml();
     }
    
    public function sitepageAction()
    {
        $templateid = $this->getRequest()->getParam('template');
        $sitepage = $this->getRequest()->getParam('sitepage');
        $this->loadLayout();  
           
        $block = $this->getLayout()->getBlock('editor');
        $block->setData('templateid',$templateid);
        $block->setData('sitepage',$sitepage);
           
		$this->renderLayout();
    }
    
    
    
    public function productchooserAction()
    {
        $templateid = $this->getRequest()->getParam('template');
        $sitepage = $this->getRequest()->getParam('sitepage');
        $this->loadLayout();  
        $block = $this->getLayout()->getBlock('editor');
        $block->setData('templateid',$templateid);
        $block->setData('sitepage',$sitepage);
		$this->renderLayout();
    }
    
     /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array('add');

    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current active quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

    /**
     * Set back redirect url to response
     *
     * @return Mage_Checkout_CartController
     */
    protected function _goBack()
    {
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl) {

            if (!$this->_isUrlInternal($returnUrl)) {
                throw new Mage_Exception('External urls redirect to "' . $returnUrl . '" denied!');
            }

            $this->_getSession()->getMessages(true);
            $this->getResponse()->setRedirect($returnUrl);
        } elseif (!Mage::getStoreConfig('checkout/cart/redirect_to_cart')
            && !$this->getRequest()->getParam('in_cart')
            && $backUrl = $this->_getRefererUrl()
        ) {
            $this->getResponse()->setRedirect($backUrl);
        } else {
            if (($this->getRequest()->getActionName() == 'add') && !$this->getRequest()->getParam('in_cart')) {
                $this->_getSession()->setContinueShoppingUrl($this->_getRefererUrl());
            }
            $this->_redirect('checkout/cart');
        }
        return $this;
    }

    /**
     * Initialize product instance from request data
     *
     * @return Mage_Catalog_Model_Product || false
     */
    protected function _initProduct()
    {
        $productId = (int) $this->getRequest()->getParam('product');
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }

    /**
     * Add product to shopping cart action
     */
    public function addAction()
    {
        $cart   = $this->_getCart();    //getting cart
        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['qty'])) {    //checking qty
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }
            /** HUY: begin 2014-04-16 **/
            //$read = Mage::getSingleton('core/resource')->getConnection('core_read');
//            $site_id = str_replace('sid','',Mage::app()->getStore()->getCode());
//            if ($site_id == "default") $site_id = 6;
//            $productid = $params['product'];
            //$query = "SELECT * FROM abcv_product_options_checkbox WHERE site_id='$site_id' And product_id = '$productid' And attr = 'AllowCustomizeTemplate'";
//            $customize_template = $read->fetchRow($query);
//            $params['is_has_allow_customize'] = '0';
//            if ($customize_template != null && $customize_template['value'] == 'true')
//                $params['is_has_allow_customize'] = '1';
            $params['add_to_cart_from'] = "the_editor_page";
            /** HUY: end 2014-04-16 **/
            $product = $this->_initProduct();   //getting info product
            $related = $this->getRequest()->getParam('related_product');    

            /**
             * Check product availability
             */
            if (!$product) {
                $this->_goBack();
                return;
            }

            $cart->addProduct($product, $params);   //add s?n ph?m d� v�o cart
            
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();  //g?i d?n file App\code\core\mage\checkout\model\cart.php

            $this->_getSession()->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            Mage::dispatchEvent('checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()){
                    $message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
                    $this->_getSession()->addSuccess($message);
                }
                
               
            }
             echo 'OK';
        
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
            }
            echo Mage::helper('core')->escapeHtml($message);return;
            
            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
            Mage::logException($e);
            echo 'Cannot add the item to shopping cart. ';
            //$this->_goBack();
        }
    }
    
    
    /**
     * Update product configuration for a cart item
     */
    public function updateItemOptionsAction()
    {
        $cart   = $this->_getCart();
        $id = (int) $this->getRequest()->getParam('id');
        $is_multi_design = (int) $this->getRequest()->getParam('is_multi_design');
        $params = $this->getRequest()->getParams();
        
        if (!isset($params['options'])) {
            $params['options'] = array();
        }
        
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }
            /** HUY: begin 2014-04-16 **/
            $params['add_to_cart_from'] = "the_editor_page";
            /** HUY: end 2014-04-16 **/
            $quoteItem = $cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                Mage::throwException($this->__('Quote item is not found.'));
            }
            
            $item = $cart->updateItem($id, new Varien_Object($params));
            
            //   exit();
            if (is_string($item)) {
                Mage::throwException($item);
            }
            
            //if ($item->getHasError()) {
//                Mage::throwException($item->getMessage());
  //          }

            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            Mage::dispatchEvent('checkout_cart_update_item_complete',
                array('item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()){
                    //$message = $this->__('%s was updated in your shopping cart.', Mage::helper('core')->htmlEscape($item->getProduct()->getName()));
                    $message = $this->__('%s was updated in your shopping cart.',Mage::getModel('checkout/cart')->getQuote()->getItemById($id)->getProduct()->getName());
                    $this->_getSession()->addSuccess($message);
                }
                //$this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError($message);
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update the item.'));
            Mage::logException($e);
            //$this->_goBack();
        }
        // if ($is_multi_design == 0)
        //     $this->_redirect('checkout/cart/');
        // else
            echo 'ok';
    }
    
    public function removeQuoteAction()
    {
    	//$quoteID = Mage::getSingleton("checkout/session")->getQuote()->getId();
        
        
        
        
   	    $quoteID = $this->getRequest()->getParam('quoteID');
    	if(isset($quoteID))
    	{
    	    try {
    	       $session= Mage::getSingleton('checkout/session');
                $quote = $session->getQuote();
                $quote->removeItem($quoteID)->save();
                
    	        //$quote = Mage::getModel("sales/quote")->load($quoteID);
//    	        $quote->setIsActive(false);
//    	        $quote->delete();
//    	 
    	        echo "OK";
    	    } catch(Exception $e) {
    	        echo $e->getMessage();
    	    }
    	}else{
    	    echo "no quote found";
    	}
    }
    
    
    /**
     * Update apparel product configuration for a cart item
     */
    public function updateApparelItemOptionsAction()
    {
		$cart   = $this->_getCart();
        $params = $this->getRequest()->getParams();                
        $id = $this->getRequest()->getPost('id');
        try {            
            $item = Mage::getModel('checkout/cart')->getQuote()->getItemById($id);
            if (!$item) {
                Mage::throwException($this->__('Quote item is not found.'));
            }
            $data = $item->getBuyRequest()->getData();
            $data['apparel'] = $params['apparel'];
            $qty = 0;
 			if(!empty($data['apparel'])){
 				foreach($data['apparel'] as $optionKey => $optionValue){
 					foreach($optionValue as $itemOptionKey => $itemOptionValue){
 						$qty += $itemOptionValue;
 					}
 				}
 			}
 			$data['qty'] = $qty;
 			
            $item = $cart->updateItem($id, new Varien_Object($data));
            
            //   exit();
            if (is_string($item)) {
                Mage::throwException($item);
            }
            
            $cart->save();

            Mage::dispatchEvent('checkout_cart_update_item_complete',
                array( 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
            $this->_getSession()->setCartWasUpdated(true);
            
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError($message);
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update the item.'));
            Mage::logException($e);
            //$this->_goBack();
        }
       
        $this->getResponse()->setBody( json_encode(
			array("html" => Mage::app()->
							getLayout()->createBlock('Mage_Checkout_Block_Cart')
							->toHtml()
     	)));  
    }
	
	public function loadproductsAction()
    {
        $this->loadLayout();
        $this->renderLayout();
	}
    
    

}