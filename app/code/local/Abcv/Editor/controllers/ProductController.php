<?php
class Abcv_Editor_ProductController extends Mage_Core_Controller_Front_Action
{
    public function indexAction(){
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('My Templates'));
        //if (!Mage::getSingleton('customer/session')->isLoggedIn()) 
//        {
//            $this->_redirect("customer/account/login/*");
//        }
        $this->renderLayout();
        
    }
    
    public function indexAjaxAction(){
        $block = Mage::app()->getLayout()->createBlock('editor/product');
        echo $block->getPagerHtml();
        echo $block->getContentHtml();
        echo $block->getPagerHtml();
    }
    
    public function saveProductAction(){
        if ($this->getRequest()->isPost()) {
            $result = array();
            $saveid = $this->getRequest()->getPost('saveid');
            $productid = $this->getRequest()->getPost('productid');
            $templateid = $this->getRequest()->getPost('templateid');
            $customerid = $this->getRequest()->getPost('customerid');
            $json = $this->getRequest()->getPost('json');
            $img = $this->getRequest()->getPost('img');
            $productstyleid = $this->getRequest()->getPost('productstyleid');
            
            if ($saveid == -1){//new save
                $model = Mage::getModel('editor/product');
            }
            else //update save
            {
                $model = Mage::getSingleton('editor/product')->load($saveid);
            }
            $model->setData('product_id',$productid);
            $model->setData('template_id',$templateid);
            $model->setData('customer_id',$customerid);
            $model->setData('json',$json);
            $model->setData('image',$img);
            $model->setData('productstyle_id',$productstyleid);
            try {
                $model->save();
                //To Anh
                $arr= explode("_", $productid);
                $read = Mage::getSingleton('core/resource')->getConnection('core_read');
                    //HUY: begined 2014-01-21
                    $store = Mage::app()->getStore();
                    $query = "select IFNULL((select case name when '' then 'noname' else name end from Templates where id='".$templateid."'),'noname') name,(SELECT request_path from core_url_rewrite
											where target_path = 'catalog/product/view/id/".$arr[count($arr)-1]."' and store_id='".$store->getStoreId()."') request_path";
                    //HUY: end 2014-01-21
                $readresult = $read->query($query);
        		$rowsTemp = $readresult->fetch();
        		$result['link']="/".str_replace(".html", "",$rowsTemp["request_path"])."/editor_save/$templateid/"."$productstyleid/$saveid/".Mage::getModel('catalog/product_url')->formatUrlKey ($rowsTemp["name"]).".html";
        		//end To Anh
                $result['id'] = $model->getId(); 
                $result['message'] = 'Template was '. ($saveid == -1 ? 'added' : 'updated'). ' successfully.';
                //creating cookie
                if ($customerid == 0)   //cusomter not login
                {
                    //creating random password 
                    $length = 8;
                    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                    $pass =  substr(str_shuffle($chars),0,$length);
                    
                    //setting cookie
                    $name_cookie = 'dyo3st['.$result['id'].']';
                    $value_cookie = $pass; 
                    $period_cookie = 86400; //(second) ~ 1 day
                    $result['pass_save'] = $value_cookie;
                    Mage::getModel('core/cookie')->set($name_cookie, $value_cookie, $period_cookie);
                    
                }
                else 
                {
                    $name_cookie = 'dyo3st['.$result['id'].']';
                     Mage::getModel('core/cookie')->delete($name_cookie);
                }
                //update path
                if ($saveid == -1)
                {
                    $model = Mage::getSingleton('editor/product')->load($result['id']);
                    $img = str_replace('{id}',$result['id'],$img);
                    $model->setData('image',$img);
                    $model->save();
                } 
            }
            catch (Exception $e)
            {
                $result['id'] = '-1'; 
                $result['message'] = $e->getMessage();
            }    
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
    
    public function loadProductAction(){
        if ($this->getRequest()->isPost()) {
            $saveid = $this->getRequest()->getPost('saveid');
            $model = Mage::getSingleton('editor/product')->load($saveid);
            echo $model->getData('json');
        }
    }
    
    public function deleteProductAction()
    {
        if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('editor/product');
				$model->setId($this->getRequest()->getParam('id'))->delete();
                echo 'Item was successfully deleted';
			} catch (Exception $e) {
				echo $e->getMessage();
			}
		}
    }
    
    public function deleteMutiProductAction()
    {
        if( $this->getRequest()->getParam('id') > 0 ) {
			try {
                $saveId = $this->getRequest()->getParam('id');
                for($i = 0; $i< count($saveId); $i++)
                {
                    $model = Mage::getModel('editor/product');
    				$model->setId($saveId[$i])->delete();    
                }
                echo 'Items was successfully deleted';
			} catch (Exception $e) {
				echo $e->getMessage();
			}
		}
    }
    
    public function updateProductAction()
    {
        $result = array();
        if ($this->getRequest()->getParam('id') && $this->getRequest()->getParam('pass'))
        {
            //getting param
            $saveid = $this->getRequest()->getParam('id');
            $pass = $this->getRequest()->getParam('pass');
            //check cookie
            $objCookie = Mage::getModel('core/cookie')->get('dyo3st');
            
            $check = false;
            foreach ($objCookie as $cookie_id => $cookie_pass) {
//                $result['cooki']["id"][] = $cookie_id;
//                $result['cooki']["pass"][] = $cookie_pass;
                if ($cookie_id == $saveid && $cookie_pass == $pass)
                {
                    $check = true;
                    //remove cookie
                    $name_cookie = 'dyo3st['.$saveid.']';
                    Mage::getModel('core/cookie')->delete($name_cookie);
                    break;
                }
            }
            
            if ($check)
            {
                //get customer
                $customerData = Mage::getSingleton('customer/session')->getCustomer();
                $customerid = $customerData->getId();
                //update data
                $model = Mage::getSingleton('editor/product')->load($saveid);
                $model->setData('customer_id',$customerid);
                $model->save();
                $result['id'] = $saveid;
                $result['success'] = 'Saved.';
                
            }
            else
            {
                $result['error'] = 'Wrong code.';   
            }
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
        else
        {
            $result['error'] = 'Error.';
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
    
    public function nevershowAction()
    {
        Mage::getSingleton('core/session')->setNeverShowSaveTemplateToAccount('nevershow');
    }
    
}