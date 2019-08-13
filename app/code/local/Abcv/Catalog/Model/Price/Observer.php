    <?php
    class Abcv_Catalog_Model_Price_Observer
    {
        public function __construct()
        {
        }
        /**
         * Applies the special price percentage discount
         * @param   Varien_Event_Observer $observer
         * @return  Xyz_Catalog_Model_Price_Observer
         */
        public function apply_discount_percent($observer)
        {
        	
          $event = $observer->getEvent();
          $product = $event->getProduct();   
          // process percentage discounts only for simple products     
//          if ($product->getSuperProduct() && $product->getSuperProduct()->isConfigurable()) {
//          } else {
//            $percentDiscount = $product->getPercentDiscount();
//     
//            if (is_numeric($percentDiscount)) {
//              $today = floor(time()/86400)*86400;
//              $from = floor(strtotime($product->getSpecialFromDate())/86400)*86400;
//              $to = floor(strtotime($product->getSpecialToDate())/86400)*86400;
//     
//              if ($product->getSpecialFromDate() && $today < $from) {
//              } elseif ($product->getSpecialToDate() && $today > $to) {
//              } else {
//                $price = $product->getPrice();
//                $finalPriceNow = $product->getData('final_price');
//     
//                $specialPrice = $price - $price * $percentDiscount / 100;
//     
//                // if special price is negative - negate the discount - this may be a mistake in data
//                if ($specialPrice < 0)
//                  $specialPrice = $finalPriceNow;
//                     
//                if ($specialPrice < $finalPriceNow)
//                  $product->setFinalPrice($specialPrice); // set the product final price
//              }
//            }   
//          }       
          return $this;
        }
        
        
        function updatePrice($observer){
        	$item = $observer->getQuoteItem();
    if ($item->getParentItem()) {
        $item = $item->getParentItem();
    }
    $h = fopen("e:/log.txt", "a");
    fwrite($h, "\n".$item->getOriginalPrice());
    
    // Discounted 25% off
    $percentDiscount = 0.25; 

    // This makes sure the discount isn't applied over and over when refreshing
    $specialPrice = $item->getOriginalPrice() - ($item->getOriginalPrice() * $percentDiscount);

    // Make sure we don't have a negative
    if ($specialPrice > 0) {
        $item->setCustomPrice($specialPrice);
        $item->setOriginalCustomPrice($specialPrice);
        $item->getProduct()->setIsSuperMode(true);
    }
//        	$event = $observer->getEvent();
//        	
//        	
//		    $quote_item = $event->getQuoteItem();
//		    if ($quote_item->getParentItem()) {
//        		$quote_item = $quote_item->getParentItem();
//    		}
//		    
//		    $h = fopen("e:/log.txt", "a");
//			fwrite($h, "\n1:". var_export($quote_item->getCalculationPrice(), true));
//			fwrite($h, "\n2:". var_export($quote_item->getCalculationPriceOriginal(), true));
//			fwrite($h, "\n3:". var_export($quote_item->getBaseCalculationPrice(), true));
//			fwrite($h, "\n4:". var_export($quote_item->getBaseCalculationPriceOriginal(), true));
//			fwrite($h, "\n5:". var_export($quote_item->getOriginalPrice(), true));
//			fwrite($h, "\n6:". var_export($quote_item->getBaseOriginalPrice(), true));
//		    
//		    $params = Mage::app()->getFrontController()->getRequest()->getParams();
//		    $templateid = $params['template'];
//		    if(!empty($templateid)){
//		    	$read = Mage::getSingleton('core/resource')->getConnection('core_read');
//		    	$sql = "SELECT * FROM abcv_multi_template WHERE TemplateIDs = '$templateid'";
//		    	$readresult = $read->query($sql);
//		    	$calculationPrice = 0;
//		    	while ($row = $readresult->fetch()) {
//		    		$calculationPrice += $quote_item->getOriginalPrice();
//		    	}
//		    	$h = fopen("e:/log.txt", "a");
//																fwrite($h, "\n". var_export($calculationPrice ,true));
//		    	$quote_item->setOriginalCustomPrice($calculationPrice);	
//		    }
        }
    }