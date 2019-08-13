<?php
class Abcv_Custom_Model_Observer{
	public function saveQuoteBefore($evt){
		$quote = $evt->getQuote();
		$post = Mage::app()->getFrontController()->getRequest()->getPost();
		if(isset($post['custom']['ssn'])){
			$quote->setSsn($post['custom']['ssn']);
			$quote->setEmployer($post['custom']['employer']);
			$quote->setOccupation($post['custom']['occupation']);
		}
	}
	/*
	public function saveQuoteAfter($evt){
		$quote = $evt->getQuote();
		$var = $quote->getEmployer();
		if(isset($var) && $var != ''){
			$model = Mage::getModel('custom/custom_quote');
			$model->deteleByQuote($quote->getId(),'ssn');
			$model->setQuoteId($quote->getId());
			$model->setKey('ssn');
			$model->setValue($quote->getSsn());
			$model->setEmployer($quote->getEmployer());
			$model->setOccupation($quote->getOccupation());
			$model->save();
		}	
	}*/
	public function loadQuoteAfter($evt){
		$quote = $evt->getQuote();
		$model = Mage::getModel('custom/custom_quote');
		$data = $model->getByQuote($quote->getId());
		foreach($data as $key => $value){
			$quote->setData($key,$value);
		}
	}
	public function saveOrderAfter($evt){
		$order = $evt->getOrder();
		$quote = $evt->getQuote();
		$var = $quote->getEmployer();
		if(isset($var) && $var != ''){
			$model = Mage::getModel('custom/custom_order');
			$model->deleteByOrder($order->getId(),'ssn');
			$model->setOrderId($order->getId());
			$model->setKey('ssn');
			$model->setValue($quote->getSsn());
			$model->setEmployer($quote->getEmployer());
			$model->setOccupation($quote->getOccupation());
			$order->setSsn('');
			$model->save();
		}
		
	}
	public function loadOrderAfter($evt){
		$order = $evt->getOrder();
		$model = Mage::getModel('custom/custom_order');
		$data = $model->getByOrder($order->getId());
		foreach($data as $key => $value){
			$order->setData($key,$value);
		}
	}

}