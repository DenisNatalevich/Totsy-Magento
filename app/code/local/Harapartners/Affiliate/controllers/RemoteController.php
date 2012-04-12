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

class Harapartners_Affiliate_RemoteController extends Mage_Core_Controller_Front_Action{

    public function registerAction(){
    	$response = Mage::app()->getResponse();
		$response->setHeader('Content-type', 'application/json');
    	$request = $this->getRequest();
    	$email = $request->getParam('email');
    	$productUrl = $request->getParam('product_url');
    	$password = $request->getParam('password');
        $affiliateCode = $this->formatCode($request->getParam('affiliate_code'));
        $affiliate = Mage::getModel('affiliate/record')->loadByAffiliateCode($affiliateCode);    
        $session = Mage::getSingleton('customer/session');  
        $result = array(); 
        if(!!$affiliate && !!$affiliate->getId()){
        	$affiliateInfo = array();
	        $subAffiliateCode = $request->getParam('sub_affiliate_code');
	        if(!!$subAffiliateCode){
	        	$affiliateInfo['sub_affiliate_code'] = $subAffiliateCode;
	        }
	        $params = $request->getParams();
	        $registrationParam = array();
	        foreach ($params as $index=>$value) {
	        	if($index!='email' && $index!='password' && $index!='product_url' && $index!='genpswd' ){
	        		$registrationParam[$index]=$value;	
	        	}
	        }	        	        
	        $affiliateInfo['registration_param'] = json_encode();
	        $session->setData('affiliate_id', $affiliate->getId());
	        $session->setData('affiliate_info', $affiliateInfo);
	       
        }
        $store = Mage::app()->getStore();
        $customer = Mage::getModel('customer/customer')
                ->setWebsiteId($store->getWebsiteId())
                ->loadByEmail($email);       
		if(!$email || !Zend_Validate::is($email, 'EmailAddress')) {
            $result['status'] = 'failed';
			$result['error_message'] = 'invalid email address: '.$email;
        }elseif(!!$customer->getId()){
			$result['status'] = 'failed';
        	$result['error_message'] = 'customer with the email '.$email.' already existed.';
        }else{
        	//create account with random password associated with affiliate 
        	//send email notification
        	try{
        		if(!$password || $request->getParam('genpswd')){
        			$password = $customer->generatePassword(); 
        		}	        	       		      		
	        	$customer->setEmail($email);
	        	$customer->setpassword($password);
	        	$customer->setStoreId($store->getId());
	           	Mage::register('new_account',1);//Harapartners, Edward, Start of setting for Affliate email validation
	            $customer->save();
				Mage::unregister('new_account');//Harapartners, Edward, End of setting for Affliate email validation 
				Mage::dispatchEvent('customer_register_success',array('account_controller' => $this, 'customer' => $customer));// for affiliate customer tracking
				$customer->sendNewAccountEmail('registered','',$store->getId());       	    
	        	$result['status'] = 'success';
	        	//redirect customer as login customer
	        	if(!$productUrl){
	        		$result['login_url'] = Mage::getBaseUrl().'remote/login?email='.$email.'&password='.Mage::getModel('core/encryption')->encrypt($password);
	        	}else{
	        		$result['login_url'] = Mage::getBaseUrl().'remote/login?email='.$email.'&password='.Mage::getModel('core/encryption')->encrypt($password).'&product_url='.$productUrl;
	        	}
        	}catch(Exception $e){
        		$result['status'] = 'failed';
        		$result['error_message'] = $e->getMessage();
        	}
        }
        $response->setBody(json_encode($result));
    }
    
    public function loginAction(){
    	$request = $this->getRequest();
    	$email = $request->getParam('email');
    	$password = Mage::getModel('core/encryption')->decrypt($request->getParam('password'));
    	if(!!$email && !!$password){
    		$session = Mage::getSingleton('customer/session');
    	    try {
                 $session->login($email, $password);
                 if ($session->getCustomer()->getIsJustConfirmed()) {
                     $this->_welcomeCustomer($session->getCustomer(), true);
                 }if(!!$productUrl = $request->getParam('product_url')){
                 	$this->_redirectURL($productUrl);
                 }else{
                 	$this->_redirect();
                 }
             } catch (Mage_Core_Exception $e) {
                 switch ($e->getCode()) {
                     case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                         $value = Mage::helper('customer')->getEmailConfirmationUrl($email);
                         $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                         break;
                     case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                         $message = $e->getMessage();
                         break;
                     default:
                         $message = $e->getMessage();
                 }
                 $session->addError($message);
                 $session->setUsername($email);
                 $this->_redirect('customer/account/login');
             } catch (Exception $e) {
                 // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                $session->addError($e->getMessage()); 
             	$this->_redirect('customer/account/login');
             }
    	}else{
            $session->addError($this->__('Login and password are required.'));
            $this->_redirect('customer/account/login');
    	}
    }
    
    public function formatCode($code){
    	return preg_replace("/[^a-z0-9_]/", "_", trim(strtolower((urldecode($code)))));
    }
}