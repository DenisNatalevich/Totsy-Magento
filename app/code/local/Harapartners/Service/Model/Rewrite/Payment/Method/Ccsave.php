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

class Harapartners_Service_Model_Rewrite_Payment_Method_Ccsave extends Mage_Payment_Model_Method_Ccsave {
    
	//For legacy order importing
	public function validate(){
    	return true;
    }
    
    public function isAvailable($quote = null){
        if(!!Mage::registry('order_import_allow_ccsave')){
        	return true;
        }
        return parent::isAvailable($quote);
    }
	
}