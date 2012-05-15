<?php
/**
 * Harapartners
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Harapartners License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.Harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@Harapartners.com so we can send you a copy immediately.
 *
 */

class Harapartners_Rushcheckout_Model_Observer {
    
    const CUSTOMER_VALIDATION_CHECK_URL = 'customer/revalidate/index';
    const CUSTOMER_REVALIDATE_TIMER_LIMIT = 3600;
    /**
     * Double validation check HP
     */
    public function checkLastValidation($session){
        $session->setData('revalidate_before_auth_url', Mage::helper('core/url')->getCurrentUrl());
        $lastValidationTime = $session->getData('CUSTOMER_LAST_VALIDATION_TIME');
        $timeDiff = strtotime(now()) - strtotime($lastValidationTime);
        
        if ( $timeDiff >= self::CUSTOMER_REVALIDATE_TIMER_LIMIT ) {
            $session->setCheckLastValidationFlag(false);   
			$url = Mage::getBaseUrl() . self::CUSTOMER_VALIDATION_CHECK_URL;
			Mage::app()->getFrontController()->getResponse()->setRedirect($url);
        } else {
            $session->setCheckLastValidationFlag(true);
        }
    }
    
    public function customerRevalidate($observer){    
    
   		//$test = Mage::getStoreConfig('config/rushcheckout_timer/limit_timer');
    
        $session = $observer->getCustomerSession();    
        if ( $session->isLoggedIn() && !!$session->getData('CUSTOMER_LAST_VALIDATION_TIME') ){
            $moduleArrary = array(
                'customer' => array(
                    'account',
                    'address',
                    'review'
                ),
                'checkout' => array(
                    'index',
                    'cart',
                    'multishipping',
                    'onepage'
                ),
                'hpcheckout' => array(
                    'checkout',
                )
            );
            
            $controllerName = Mage::app()->getRequest()->getControllerName();
            $moduleName = Mage::app()->getRequest()->getModuleName();
            $actionName = Mage::app()->getRequest()->getActionName();
            
            foreach ( $moduleArrary as $module => $controllers ){
                if ( $moduleName == $module 
                        && in_array($controllerName, $controllers)
                        && $actionName != 'forgotpassword'
                        && $actionName != 'forgotpasswordpost'
                        && $actionName != 'logoutAction'
                        && $actionName != 'logoutSuccess'
                        && $actionName != 'resetPasswordAction'
                        && $actionName != 'resetPasswordPost' ){
                    $this->checkLastValidation($session);
                }
            }
        }
    }
    
    public function updateReservationByQuoteItem($observer){
        $quoteItem = $observer->getEvent()->getItem();
        Mage::helper('rushcheckout/reservation')->updateReservationByQuoteItem($quoteItem);
        return $this;
    }
    
    public function updateReservationByStockItem($observer){
        $stockItem = $observer->getEvent()->getItem();
        Mage::helper('rushcheckout/reservation')->updateReservationByStockItem($stockItem);
        return $this;
    }
    
    // ===== Cronjob related ===== //
    public function cleanExpiredQuotes() {
        // ensure there are no other expired quote cleaners running
        $cleanerJobs = Mage::getModel('cron/schedule')->getCollection();
        $cleanerJobs->addFilter('job_code', 'rushcheckout_clean_expired_quotes')
            ->addFilter('status', 'pending')
            ->addFilter('status', 'running', 'or');

        if (count($cleanerJobs)) {
            return false;
        }

        $lifetimes = Mage::getConfig()->getStoresConfigByPath('config/rushcheckout_timer/limit_timer');
        foreach ($lifetimes as $storeId => $lifetime) {
            $quoteCollection = Mage::getModel('sales/quote')->getCollection();
            $quoteCollection->addFieldToFilter('store_id', $storeId);
            $quoteCollection->addFieldToFilter('is_active', 1);
            $quoteCollection->addFieldToFilter('updated_at', array('to' => date("Y-m-d H:i:s", time() - $lifetime)));
            foreach($quoteCollection as $quote){
                foreach($quote->getAllItems() as $item){
                    $item->isDeleted(true);
                    $item->delete();            
                }
            }
        }
        return $this;
    }
    
}
