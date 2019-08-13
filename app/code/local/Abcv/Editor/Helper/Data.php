<?php

class Abcv_Editor_Helper_Data extends Mage_Core_Helper_Abstract
{
	function getDYOImages($dyo_product_id, $get_first_image = false){
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $readresult = $read->query("SELECT * FROM abcv_product_sides WHERE product_id='$dyo_product_id'");
        $_images_array = array();
        while ($rows = $readresult->fetch())    //each sides
        {
            $side_id_db = $rows['side_id'];
            $side_name = $rows['side_name'];
            $default_template_id = $rows['default_template_id'];
            //Showing ProductStyles in database
            $readProductStyles = $read->query("SELECT * FROM ProductStyles WHERE productside_id ='$side_id_db' AND is_deleted = '0' AND is_show = 1 ORDER BY sortorder ASC");
            while ($rowsProductStyle = $readProductStyles->fetch())
            {
            	$imageObj = new stdClass();
                $productstyle_name = $rowsProductStyle['StyleName'];
                $productstyle_id = $rowsProductStyle['ID'];
                $is_overlay =  $rowsProductStyle['is_overlay'];
                $sortorder =  $rowsProductStyle['sortorder'];
                $is_hidden =  $rowsProductStyle['is_show'];
                $url_product_style = "/image.php?ID=".$productstyle_id."&style";
                $url_template = '/image.php?ID='.$default_template_id.'&template';
                $url_full = '/images/dyotab/dyotab_'.$productstyle_id.'.png';
                $rowPath = '';
                if ($is_overlay==0)
                	$imageObj->image_url = $url_product_style;
                else
                	$imageObj->image_url = $url_full;
                $name = ($productstyle_name=='')?$side_name:$side_name.' : '.$productstyle_name;
                $imageObj->image_name = $name;                
                Array_push($_images_array, $imageObj);
                if($get_first_image) break;
            }
            if($get_first_image && count($_images_array) > 0) break;
        }
        return $_images_array;
	}
	
	function rebuildTemplateCategory($site_id = 6){
		//require_once("/dyo3-functions/f_ShowTemplates.php");
		//if($site_id == null)
		$cache = Mage::app()->getCache();
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql= "SELECT ID FROM Dimensions";
		if(!empty($site_id)) $sql .= " WHERE SiteID = '$site_id'";
		$result = $read->query($sql);	
		while($row = $result->fetch()){
			$key = "f_ShowTemplates_". $site_id. "_". $row['ID'];
			$cache->remove($key);
			//$cache->clean(array("tplAssociateCate"));	
		}
	}
	
	function isAccessFromMobile() {
//    	$browser = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone") || strpos($_SERVER['HTTP_USER_AGENT'],"iPod") || strpos($_SERVER['HTTP_USER_AGENT'],"Android"); 
//		if ($browser == true && $ci->config->item('fullsize')==0 )  {	
//			return true;
//		}
//		return false;				 
		$regex_match = "/(nokia|iphone|android|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|"  
                 . "htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|"  
                 . "blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|"  
                 . "symbian|smartphone|mmp|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|"  
                 . "jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320x320|240x320|176x220"  
                 . ")/i";  

	    if (preg_match($regex_match, strtolower($_SERVER['HTTP_USER_AGENT']))) {  
	        return TRUE;  
	    }  
	
	    if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {  
	        return TRUE;  
	    }      
	
	    $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));  
	    $mobile_agents = array(  
	        'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',  
	        'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',  
	        'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',  
	        'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',  
	        'newt','noki','oper','palm','pana','pant','phil','play','port','prox',  
	        'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',  
	        'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',  
	        'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',  
	        'wapr','webc','winw','winw','xda ','xda-');  
	
	    if (in_array($mobile_ua,$mobile_agents)) {  
	        return TRUE;  
	    }  
	
	    if (isset($_SERVER['ALL_HTTP']) && strpos(strtolower($_SERVER['ALL_HTTP']),'OperaMini') > 0) {  
	        return TRUE;  
	    }  
	
	    return FALSE;
	}
	
	function getCustomizePrice($productid, $site_id, $is_not_customize){
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$customize_price = 0;        
        $is_notCustomize_template = false;
        //Customer desgin template
        if ($is_not_customize != null && $is_not_customize == 0) 
        {        
        	$query = "SELECT * FROM abcv_product_options_checkbox WHERE site_id='$site_id' And product_id = '$productid' And attr = 'AllowCustomizeTemplate'";
        	$customize_template = $read->fetchRow($query);           
        	if ($customize_template != null && $customize_template['value'] == 'true')
	            $is_notCustomize_template = true;    
	        if ($is_notCustomize_template)
	        {
	            $query = "SELECT * FROM abcv_product_options_checkbox WHERE site_id='$site_id' And product_id = '$productid' And attr = 'Price'";
	            $customize_price = $read->fetchRow($query);
	            $customize_price = ($customize_price==null)?0:$customize_price['value'];
	        }	
        }
        return $customize_price;
	}
	
	function getApparelAdditionalCharge($quote){
		$apparelOptions = $quote->getBuyRequest()->getData('apparel');
		$apparelAdditionalCharge = 0;
		foreach($apparelOptions as $appOptionKey => $appOptionValue){
			$optionApparelObject = $quote->getProduct()->getOptionById($appOptionKey);							
			foreach($appOptionValue as $appKey => $appValue){
					if(empty($appValue)) continue;					
					$itemApparelObject = $optionApparelObject->getValueById($appKey);
				    if($itemApparelObject->getPrice() > 0){
				    	 $apparelAdditionalCharge += $appValue * $itemApparelObject->getPrice();
				    }
			}
		}
		return $apparelAdditionalCharge;
	}
}