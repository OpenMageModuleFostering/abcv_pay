<?php
class Abcv_Editor_Adminhtml_CategoryController extends Mage_Adminhtml_Controller_Action
{ 
    protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('editor/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}
	public function testAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function saveAction() {
		//print_r($this->getRequest()->getPost());
		
		$cateInfo=$this->getRequest()->getPost("general");
		//print_r($cateInfo);
		$cate_ids="";$cate_num="";
			
		try{
			$write = Mage::getSingleton('core/resource')->getConnection('core_write');  
			//print_r($cateInfo);
			if (empty($cateInfo["id"]))//add
			{
				$read = Mage::getSingleton('core/resource')->getConnection('core_read'); 
				//get category'll be changed 
				$readresult=$read->query("SELECT IFNULL(MAX(sortorder)+1,1) sortorder
										FROM TemplateDirectories  tem
										WHERE tem.Parent=".$cateInfo["Parent"]);
				$row = $readresult->fetch();
				$cateInfo["sortorder"] = $row["sortorder"];
				$write->insert("TemplateDirectories", $cateInfo);
	             $cateInfo["id"] = $write->lastInsertId();
	             if(isset($_FILES["thumbnail"]))
	             {
	             	$fileName=Mage::getBaseDir()."/images"."/templatedirectories"."/templatedirectories_".$cateInfo["id"].".png";
	             	move_uploaded_file( $_FILES["thumbnail"]["tmp_name"],$fileName );
	             }
	             	//move_uploaded_file( $_FILES["thumbnail"]["tmp_name"], Mage::getBaseDir()."/images"."/templatedirectories"."/templatedirectories_".$cateInfo["id"]".png");
			}
			else//update
			{
				$query=" update TemplateDirectories set 
					Name='".$cateInfo["name"]."', 
					description='".$cateInfo["description"]."', 
					view='".$cateInfo["view"]."', 
					status=".$cateInfo["status"]." ";
				if(isset($cateInfo["thumbnail"]["delete"]))//delete image
				{
					$fileName=Mage::getBaseDir()."/images"."/templatedirectories"."/templatedirectories_".$cateInfo["id"].".png";
					if(file_exists($fileName))
						unlink($fileName);
				}	
				elseif(isset($_FILES["thumbnail"])&&!empty($_FILES["thumbnail"]["name"]))
				{
					//print_r($_FILES);
					$fileName=Mage::getBaseDir()."/images"."/templatedirectories"."/templatedirectories_".$cateInfo["id"].".png";
					if(file_exists($fileName))
						unlink($fileName);
					move_uploaded_file( $_FILES["thumbnail"]["tmp_name"],$fileName );
				}
				$query.=" where ID=".$cateInfo["id"];
				$write->query($query);
			}
			//set template for category
			$in_template_add=$this->getRequest()->getPost('in_template_add');
			if(!empty($in_template_add))
			{
				$in_template_add=str_replace(",,", ",", $in_template_add);
				
				/*$read = Mage::getSingleton('core/resource')->getConnection('core_read'); 
				//get category'll be changed 
				$readresult=$read->query("SELECT DISTINCT `Directory` from Templates
					WHERE ID in(-1 $in_template_add -1) ORDER BY `Directory` ");
				$row = $readresult->fetch();
				$cate_ids=$row["Directory"];
				while ($row = $readresult->fetch() ) {
					$cate_ids.=",".$row["Directory"];
				}
				$write->query("UPDATE Templates
								SET `Directory` = ".$cateInfo["id"]."
								WHERE ID in(0 $in_template_add 0)");
				$readresult=$read->query("SELECT DISTINCT Name,(select count(*) from Templates tem1 where tem.ID=tem1.`Directory`) number
								from TemplateDirectories tem
								WHERE ID in($cate_ids) 
								ORDER BY ID ");
				$row = $readresult->fetch();
				$cate_num=$row["Name"]."(".$row["number"].")";
				while ($row = $readresult->fetch() ) {
					$cate_num.=",".$row["Name"]."(".$row["number"].")";
				}*/
				$write->query("INSERT INTO TemplatesOfCategory (`TemplateID`,`CategoryID`)
								SELECT ID,".$cateInfo["id"]."
    								FROM Templates 
									WHERE ID in(0 $in_template_add 0)");
			}
			$in_template_delete=$this->getRequest()->getPost('in_template_delete');
			if(!empty($in_template_delete))
			{
				$in_template_delete=str_replace(",,", ",", $in_template_delete);
				$write->query("DELETE FROM TemplatesOfCategory
								WHERE CategoryID=".$cateInfo["id"]." AND TemplateID in(0 $in_template_delete 0)");
			}
			// ABCV DANG - remove cache
			Mage::helper('editor')->rebuildTemplateCategory();
			// END ABCV
		}
		catch (Exception $e)
		{
			echo $e;
		}
			//call javascript to refresh tree
		$this->getResponse()->setBody(
            '<script type="text/javascript">parent.updateContent("'.$cate_ids.'","'.$cate_num.'","'.$cateInfo["id"].'");</script>'
        );
	}
}
?>