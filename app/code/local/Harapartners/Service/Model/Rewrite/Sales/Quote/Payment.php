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

class Harapartners_Service_Model_Rewrite_Sales_Quote_Payment extends Mage_Sales_Model_Quote_Payment {
    
    public function importData(array $data, $shouldCollectTotal = true, $withValidate = true ){
        if( isset( $data ) && isset( $data[ 'cybersource_subid' ] ) && !! $data[ 'cybersource_subid' ] ) {
            return $this->importDataWithToken($data, $shouldCollectTotal);
        } else if( ! $withValidate ) {
        	return $this->importDataWithoutValidation( $data );
    	} else{
            return $this->importDataDefaultWay($data);
        }
    }
         
    public function importDataWithToken(array $data, $shouldCollectTotal = true){
        $data = new Varien_Object($data);
        Mage::dispatchEvent(
            $this->_eventPrefix . '_import_data_before',
            array(
                $this->_eventObject=>$this,
                'input'=>$data,
            )
        );

        $this->setMethod($data->getMethod());
        $method = $this->getMethodInstance();

        if($shouldCollectTotal){
            $this->getQuote()->collectTotals();
        }

        if (!$method->isAvailable($this->getQuote())) {
            Mage::throwException(Mage::helper('sales')->__('The requested Payment Method is not available.'));
        }
        
        //Special logic for tokenized payments, decrypt if necessary
        $subscriptionId = $this->_decryptSubscriptionId($data->getData('cybersource_subid'));
        $this->setData('cybersource_subid', $subscriptionId);
        $method->setData('cybersource_subid', $subscriptionId);
        
        /*
        * validating the payment data
        */
        $method->validate();
        return $this;
    }
    
    //For reward points and collect totals
	public function importDataWithoutValidation(array $data)
    {
        $data = new Varien_Object($data);
        Mage::dispatchEvent(
            $this->_eventPrefix . '_import_data_before',
            array(
                $this->_eventObject=>$this,
                'input'=>$data,
            )
        );

        $this->setMethod($data->getMethod());
        $method = $this->getMethodInstance();

        /**
         * Payment avalability related with quote totals.
         * We have recollect quote totals before checking
         */
        $this->getQuote()->collectTotals();

        if (!$method->isAvailable($this->getQuote())) {
            Mage::throwException(Mage::helper('sales')->__('The requested Payment Method is not available.'));
        }

        $method->assignData($data);
        /*
        * validating the payment data
        */
        //$method->validate();
        return $this;
    }

    public function importDataDefaultWay(array $data)
    {
        $data = new Varien_Object($data);
        Mage::dispatchEvent(
            $this->_eventPrefix . '_import_data_before',
            array(
                $this->_eventObject=>$this,
                'input'=>$data,
            )
        );

        $this->setMethod($data->getMethod());
        $method = $this->getMethodInstance();

        /**
         * Payment avalability related with quote totals.
         * We have recollect quote totals before checking
         */
        $this->getQuote()->collectTotals();

        if (!$method->isAvailable($this->getQuote())) {
            Mage::throwException(Mage::helper('sales')->__('The requested Payment Method is not available.'));
        }
        
        $method->assignData($data);
        $this->getQuote()->setData('saved_by_customer', $data[ 'saved_by_customer' ]);
        
        /*
        * validating the payment data
        */
        $method->validate();
        return $this;
    }
    
    protected function _decryptSubscriptionId($subId){
        try{
            $testSubId = Mage::getModel('core/encryption')->decrypt(base64_decode($subId));
            if(is_numeric($testSubId)){
                $subId = $testSubId;
            }
        }catch (Exception $e){
        }
        return $subId;
    }
    
}