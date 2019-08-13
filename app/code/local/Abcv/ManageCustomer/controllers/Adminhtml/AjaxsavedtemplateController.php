<?php
class Abcv_ManageCustomer_Adminhtml_AjaxsavedtemplateController extends Mage_Adminhtml_Controller_Action
{

    private $read;    
    private $write;
    public function _construct()
    {
        parent::_construct();
        $this->read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $this->write = Mage::getSingleton('core/resource')->getConnection('core_write');
    }
    
    public function indexAction()
    {
        $postData = $this->getRequest()->getPost();
        
        //set data
        $current_page = $postData['page'];
        $itemsPerPage = $postData['item_per_page'];
        
        //get data
        $offset = ($postData['page'] - 1) * $postData['item_per_page'];
        $limit = $postData['item_per_page']; 
        $query = "select * from abcv_product_save where customer_id = '". $postData['customer_id'] ."' ";
        //where
        $where = "";
        if (!empty($postData['save_id']))
        {
            if ($where != "") $where .= " AND ";
            $where .= " save_id = '". $postData['save_id'] ."'"; 
        }
        if (!empty($postData['from_date']))
        {
            $d = new DateTime($postData['from_date']);
            if ($where != "") $where .= " AND ";
            $where .= " date_at >= '" . $d->format("Y-m-d") ."'";
        }
        if (!empty($postData['to_date']))
        {
            $d = new DateTime($postData['to_date']);
            if ($where != "") $where .= " AND ";
            $where .= " date_at <= '" . $d->format("Y-m-d") ."'";
        }
        if (!empty($postData['product_id']))
        {
            if ($where != "") $where .= " AND ";
            $where .= " product_id = 'product_". $postData['product_id'] ."'"; 
        }
        if ($where != "")
            $query .= " AND ";
        $query .= $where;
        //order
        $order_by = "";
        if (!empty($postData['asc']))
        {
            if ($order_by != "") $order_by .= " , ";
            $order_by .= $postData['asc'] ." asc ";
        }
        if (!empty($postData['desc']))
        {
            if ($order_by != "") $order_by .= " , ";
            $order_by .= $postData['desc'] ." desc ";
        }
        if (empty($order_by))
        {
            $order_by .= "date_at desc";
        }
        $order_by = " ORDER BY " . $order_by;
        $query .= $order_by;
        //limmit
        $query .= " LIMIT " . $offset ."," . $limit;
        
        $save_template = $this->read->fetchAll($query);
        
        //count total template
//        $query = "select * from abcv_product_save where customer_id = '". $postData['customer_id'] ."' ";
//        if ($where != "")
//            $query .= " AND ";
//        $query .= $where;
//        $result = $this->read->fetchAll($query);
//        $total_template = count($result);
        $query = "select count(save_id) count from abcv_product_save where customer_id = '". $postData['customer_id'] ."' ";
        if ($where != "")
            $query .= " AND ";
        $query .= $where;
        $total_template = $this->read->fetchOne($query);
        

        include ('html/_pattern.php');
        
    }

}