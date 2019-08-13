<?php
class Abcv_Editor_Adminhtml_AjaxController extends Mage_Adminhtml_Controller_action
{ 
    public function getProductsAction()
    {
        $attributeCode = $this->getRequest()->getPost('attributeCode');
        echo $attributeCode;
        return;
        $result = array();
        if ($this->getRequest()->isPost()) {
            $attributeCode = $this->getRequest()->getPost('attributeCode');
            /*$product = Mage::getModel('catalog/product')->load(27);
            $attributeNames = array_keys($product->getData());
            echo $product->getData('attribute_set_id'); //gia tri nhan ben trong attribute
            */
            $collection = Mage::getModel('catalog/product')->getCollection();
            $collection->addAttributeToSelect('name');
            $collection->addAttributeToSelect('image');
            $collection->addAttributeToSelect('productstyle');
            $collection->addAttributeToSelect('description');
            $collection->addAttributeToSelect('price');
            $collection->addAttributeToSelect('special_price');
            $collection->addAttributeToSelect('templateid');
            $support_product_ids = array();
            while($row = mysql_fetch_array($result)) {
                $support_product_ids[] = array('attribute'=>'attribute_set_id','eq'=>$attributeCode);
            }
            $collection->addFieldToFilter($support_product_ids);
            $collection->setPage(1, 10);//set khoang 10 trang dau, va lay trang so 1
            
            $html = "<table>";
            foreach ($collection as $product) {
                $html .= "<tr><td>";
                $html.=" Iowa State iPad 2 &amp; 3 Cover - Cy on a Red Patterned Background - Protective Leather and Suede Case";
                $html.="  <p><span id='spanTemplate'>Template: </span>";
                $html.="   <span id='spanProductStyle'>Style: </span></p>";
                $html.="</td></tr>";        
            }
            $html.= "</table>";
            $result['body'] = $html;
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
}
?>