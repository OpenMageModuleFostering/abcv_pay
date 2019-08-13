<?php
class Abcv_Editor_Block_Product extends Mage_Core_Block_Template
{
    public function __construct() {
        parent::__construct(); 
        $customerData = Mage::getSingleton('customer/session')->getCustomer();
            
        if ($customerData->getId() == 0)
        {
            $save_ids = array();
            $objCookie = Mage::getModel('core/cookie')->get('dyo3st');
            if ($objCookie)
                foreach ($objCookie as $id => $pass)
                    array_push($save_ids, $id);
            else
                $save_ids = "-1";
            $collection_cookie = Mage::getModel('editor/product')->getCollection()->addFieldToFilter('save_id', $save_ids)->addfieldtofilter('customer_id', $customerData->getId());
            $this->setCollection($collection_cookie);            
        }
        else
        {
            $collection_customer = Mage::getModel('editor/product')->getCollection()->addfieldtofilter('customer_id', $customerData->getId());
            $this->setCollection($collection_customer);    
        }
    }
        
	public function _prepareLayout()
    {
		parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager');
        $pager->setAvailableLimit(array(20=>20,30=>30,50=>50));
        $pager->setCollection($this->getCollection());
        $this->setChild('pager', $pager);
        $this->getCollection()->load(); 
        return $this;
    }
    public function getPagerHtml() {
        return $this->getChildHtml('pager'); 
    }
    public function getContentHtml(){
        $collection = $this->getCollection();
        $productsDesigns = array();
        foreach ($collection as $record) {
            $t = $record->toArray();
            $productsDesigns[] = $t;  
   	    }
        $html = "<script><!-- \$jquery('.dyo_gridCell_footer').find('input[type=checkbox]').change(function () {
            \$jquery('.error-msg').hide(200);
            \$jquery('.success-msg').hide(200);
        });
-->
</script>";
        $html .= "<div class='dyo_gridShell'>";
        foreach($productsDesigns as $design)
        {
            $pos = strpos($design['product_id'], 'quote_');
            if ($pos === false) //no quote, new template
            {
                $product_id = str_replace("product_", "", $design['product_id']);
                $url = '/editor/index/template/template/'.$design['template_id'].'/product/'.$product_id.'/productstyle/'.$design['productstyle_id'].'/save/'.$design['save_id'];
            }
            else//quote
            {
                $product_id = str_replace("quote_", "", $design['product_id']);
                $url = '/editor/index/template/template/'.$design['template_id'].'/product/'.$product_id.'/productstyle/'.$design['productstyle_id'].'/save/'.$design['save_id'].'/editor/'.rand();
            }
            $html .= "<div class=dyo_gridCell id='cell_".$design['save_id']."'>";
            
    
              $html .= "<a class='open' href='#' onclick='\$jquery(\"#hiddenSaveId\").val(".$design['save_id'].");\$jquery(\"#dialog-confirm\").dialog(\"open\");'>delete</a>";
    
            $html .= "<div class='dyo_gridCell_content'>";
            $images = json_decode($design['image'], true);
            $html .= "<script type='text/javascript'><!-- var imagesDYO = new Array();";
            $html .="if (typeof imagesDYO === 'undefined') var imagesDYO = {};";
            $html .="imagesDYO[".$design['save_id']."] = new Array();";
                    for($index=0; $index< count($images); $index++)
                    
                        $html.= 'imagesDYO['.$design['save_id'].'].push("'.$images[$index]['imageData'].'");';
                
            $html .= "--> </script>";
                    
            
                $tip = "Tip('Click to continue design');switchImages(this,imagesDYO[".$design['save_id']."]);";
                $html .= "<a href=\"".$url."\" onmouseover=\"".$tip."\" onmouseout='UnTip();outTagImages(this,imagesDYO[".$design['save_id']."]);'>";
                
                    for ($index=0; $index < count($images); $index++)
                    {
                        if ($index != 0)
                            $html .= '<img alt='. $index.' style="display:none;" src="'.$images[$index]['imageData'].'" />';
                        else
                            $html .= '<img alt='. $index.' src="'.$images[$index]['imageData'].'" />';
                    }
                    //$html .= '<img src="'.$images[0]['imageData'].'" />';
                    
                    //$html .= "<img src='".$design['image']."' />";
                $html .= "</a> ";
            $html .= "</div>";
            $html .= "<div class='dyo_gridCell_footer'>";
                $html .= "<input type='checkbox' value=".$design['save_id']." /><span>".$design['date_at']."</span>";
            $html .= "</div>";
                      
            $html .= "</div>";
        }
        $html .= "</div>";
        return $html;
    }
     public function getProduct()     
     { 
        if (!$this->hasData('product')) {
            $this->setData('product', Mage::registry('product'));
        }
        return $this->getData('product');
        
    }
    
}