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

class Harapartners_Affiliate_Model_Observer {
	
	public function addAffiliateRouter(Varien_Event_Observer $observer){
	    $front = $observer->getEvent()->getFront();
		$front->addRouter('affiliate', new Harapartners_Affiliate_Controller_Router());			
	}
	
}