<?php 

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

class Harapartners_Customertracking_Block_Pixel extends Mage_Core_Block_Template{
	
	public function _toHtml(){
		$pixelHtml = '';
		$affiliate = Mage::getSingleton('customer/session')->getAffiliate();
		if(!!$affiliate && !!$affiliate->getId()){
			try{
				$trackingCode = json_decode($affiliate->getTrackingCode(), true);
				
				//Page detection
				$currentPageTag = strtolower(Mage::app()->getFrontController()->getAction()->getFullActionName());
				if(!empty($trackingCode[$currentPageTag])){
					$pixelHtml .= $trackingCode[$currentPageTag];
				}
				
				//Additional logic
				$cookie = Mage::app()->getCookie();
    			$key = Harapartners_Customertracking_Helper_Data::COOKIE_CUSTOMER_WELCOME;
    			if(!!$cookie->get($key)){
	    			if(!empty($trackingCode[Harapartners_Affiliate_Helper_Data::PAGE_NAME_AFTER_CUSTOMER_REGISTER_SUCCESS])){
						$pixelHtml .= $trackingCode[Harapartners_Affiliate_Helper_Data::PAGE_NAME_AFTER_CUSTOMER_REGISTER_SUCCESS];
					}
					$cookie->delete($key); //Note cookie still available till next page request
		    	}
				
			}catch(Exception $e){
			}
		}
		$varaiableArray = $this->_stringToArray($pixelHtml); 
		$pixelHtml = '';
		foreach ($varaiableArray as $text) {
			if(is_array($text)){
				foreach ($text as $realText) {
					$pixelHtml.= $realText;
				}
			}else{
				$pixelHtml.=$text;	
			}				
		}
		
		return $pixelHtml;
	}
	protected function _stringToArray($string){
		$array = explode('{{', $string);
		$i=count($array);
		for($j=1;$j<$i;$j++){
			$array[$j] = explode('}}',$array[$j]);
		}
		if($i>1){
			$customer = Mage::getSingleton('customer/session')->getCustomer();
			$orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
			$order = Mage::getModel('sales/order')->load($orderId);
			for ($j=1;$j<$i;$j++){
				if(substr($array[$j][0],0,9)=='customer.'){
					$array[$j][0] = $customer->getData(substr($array[$j][0],9));
				}elseif (substr($array[$j][0],0,6)=='order.'){
					$array[$j][0] = $order->getData(substr($array[$j][0],6));
				}else{
					$array[$j][0] = '{{'.$array[$j][0].'}}';
				}
			}
		}
		return $array;
	}
}
