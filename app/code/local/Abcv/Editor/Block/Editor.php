<?php
class Abcv_Editor_Block_Editor extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getEditor()     
     { 
        if (!$this->hasData('editor')) {
            $this->setData('editor', Mage::registry('editor'));
        }
        return $this->getData('editor');
        
    }
    
    function savingPrice($price, $unitPrice)
    {
         //if ($product->getFinalPrice() != $product->getPrice())
         //if ($qty*$price != $product->getPrice())
         //{
            //$saving = $product->getPrice() - $product->getFinalPrice();
            $saving = $price*100/$unitPrice;
            $saving = number_format(100-$saving);
            return  $saving.'%';
   	     //}
         //return $this->__('no save');
    }


    function savingPriceTemp($product)
    {
        $_coreHelper = Mage::helper('core');
        $_taxHelper  = Mage::helper('tax');
     
         //if ($product->getFinalPrice() != $product->getPrice())
         if ($product->getQty()*$product->getPrice() != $product->getPrice())
         {
            $saving = $product->getPrice() - $product->getFinalPrice();
            return  $this->__('Save ').$_coreHelper->currency($_taxHelper->getPrice($product, $saving, true), true, false);
   	     }
         return $this->__('no save');
    }

    function savingPercent($product)
    {
         if ($product->getFinalPrice() != $product->getPrice())
         {
            $saving = $product->getPrice() - $product->getFinalPrice();
            $saving = number_format($saving / $product->getPrice() * 100);
            return  $this->__('Save ').$saving.'%';
         }
    }
    function getProductByDimension(){


        //$dbh = new PDO('mysql:host=localhost;dbname=dyo3victory', 'root', '');
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
            
        $template_id = $this->getRequest()->getParam('id');
       

        $productIds = array();
        $query = "SELECT product_id from abcv_product_sides where dimension_id in (select Dimension as dimension_id from Templates where ID  =  '$template_id' and SiteID = 26) and site_id = 26";
        //echo $query;
        $result = $read->fetchAll($query);
       //print_r($result);
        //foreach ($dbh->query($query) as $row) {
       foreach ($result as $row) {
            $productIds[] = (int)$row['product_id'];
        }
       
       
        $products = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in' => $productIds));
        return $products;

    }
    public function getPriceHtml($product)
    {
        $this->setTemplate('catalog/product/price.phtml');
        $this->setProduct($product);
        return $this->toHtml();
    }

}