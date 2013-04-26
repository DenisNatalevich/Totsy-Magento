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

class Harapartners_Stockhistory_Model_Purchaseorder extends Mage_Core_Model_Abstract {
    
    const STATUS_OPEN = 1;
    const STATUS_ON_HOLD = 2;
    const STATUS_SUBMITTED = 3;
    const STATUS_COMPLETE = 4;
    const STATUS_CANCELLED = 5;
    
    protected $_vendorObj;
    
    public function _construct() {
        $this->_init('stockhistory/purchaseorder');
    }
    
    protected function _beforeSave(){
        if(!$this->getId()){
            $this->setData('created_at', now());
        }
        $this->setData('updated_at', now());
        
        if(!$this->getStoreId()){
            $this->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);
        }
        parent::_beforeSave();  
    }
    
    public function loadByVendorId($vendorId, $storeId = null){
        $this->addData($this->getResource()->loadByVendorId($vendorId, $storeId));
        return $this;
    }
    
    public function getVendorObj() {
        if(!$this->_vendorObj){
            $this->_vendorObj = Mage::getModel('stockhistory/vendor')->load($this->getData('vendor_id'));
        }
        return $this->_vendorObj;
    }
    
    public function generatePoNumber(){
        return strtoupper(substr($this->getVendorObj()->getVendorCode(), 0, 3)) . strtotime($this->getCreatedAt()); 
    }
    
    public function importData($dataObj){
        
        //Type casting
        if(is_array($dataObj)){
            $dataObj = new Varien_Object($dataObj);
        }
        if(!($dataObj instanceof Varien_Object)){
            Mage::throwException('Invalid data type, Array or Varien_Object needed.');
        }
        
        $vendor = Mage::getModel('stockhistory/vendor');
        if(!!$dataObj->getdata('vendor_id')){
            $vendor->load($dataObj->getdata('vendor_id'));
        }elseif(!!$dataObj->getdata('vendor_code')){
            $vendor->loadByCode($dataObj->getdata('vendor_code'));
        }
        if(!$vendor || !$vendor->getId()){
            Mage::throwException('Invalid Vendor.');
        }
        $dataObj->setData('vendor_id', $vendor->getId());
        $dataObj->setData('vendor_code', $vendor->getVendorCode());
        
        //Load category
        $category = Mage::getModel('catalog/category');
        if(!!$dataObj->getdata('category_id')){
            $category->load($dataObj->getdata('category_id'));
        }
        if(!$category || !$category->getId()){
            Mage::throwException('Invalid Category/Event.');
        }
        $dataObj->setData('category_id', $category->getId());
        
        if(!$dataObj->getData('status')){
            $dataObj->setData('status', self::STATUS_OPEN);
        }
        
        if(!$dataObj->getData('po_number')) {
        	$dataObj->setData('po_number', $this->generatePoNumber());
        }
        
        $this->addData($dataObj->getData());

        return $this;
    }

    public function totalPoUnits($collection) {
        foreach($collection as $result) {
            $transactionsColl = Mage::getModel('stockhistory/transaction')->getCollection();
            $transactionsColl->getSelect()->where('po_id=' . $result->getId() . ' and product_id IS NOT null and action_type= 2')->order('id DESC');
            if ($transactionsColl->getSize()) {
                $total_units = $qty = 0;
                $pre_buys = array();
                $count = 0;
                $category_id = $transactionsColl->getFirstItem()->getCategoryId();
                $event = Mage::getModel('catalog/category')->load($category_id);
                $start_date = $event->getData('event_start_date');
                $end_date = $event->getData('event_end_date');

                $start_date = strtotime($start_date) - 60*60*24*7;	// 7 days before
                $end_date = strtotime($end_date) + 60*60*24*3;	// 3 days after

                $start_date = date('Y-m-d H:i:s', $start_date);
                $end_date = date('Y-m-d H:i:s', $end_date);
                foreach($transactionsColl as $trans) {
                    $product_id = $trans->getProductId();
                    $product = Mage::getModel('catalog/product')
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->load($product_id);
                    if($product->getIsMasterPack()) {
                        if ( !in_array($product_id, $pre_buys)) {
                            $amend_prod = Mage::getModel('stockhistory/transaction')->getCollection();
                            $amend_prod->getSelect()->where('po_id=' . $result->getId() . ' and product_id='. $product_id .' and action_type= 4');
                            if ($amend_prod->getSize()  == 0) {
                                ++$count;
                                unset($amend_prod);
                                $amend_prod = Mage::getModel('stockhistory/transaction')->getCollection();
                                $amend_prod->getSelect()->where('po_id=' . $result->getId() . ' and product_id='. $product_id .' and action_type= 1');
                                $qty = (int)$trans->getQtyDelta();
                                foreach ($amend_prod as $value) {
                                    $qty += $value->getQtyDelta();
                                }
                                $pre_buys[] = $product_id;
                                $total_units += $qty;
                            }
                        }
                    } else {
                        $ordersColl = Mage::getModel('sales/order_item')->getCollection();
                        $ordersColl->getSelect()->where('product_id =' . $product_id . ' and created_at between "'. $start_date . '" and "' . $end_date .'"');
                        foreach($ordersColl as $order) {

                            if($order->getParentItemId()) {
                                $parent_item_id = $order->getParentItemId();
                                $parent_order_line = Mage::getModel('sales/order_item')->getCollection();
                                $parent_order_line->getSelect()->where('item_id =' . $parent_item_id);
                                $order = $parent_order_line->getFirstItem();
                            }
                            $qty = $order->getQtyOrdered() - $order->getQtyReturned() - $order->getQtyCanceled();
                            $total_units += $qty;
                       }
                    }
                    $qty = 0;
                }
           }
            $result->setData('total_units', $total_units);
            $result->save();
        }
    }

    public function totalUnitsSold($collection) {
        foreach($collection as $result) {
            $transactionsColl = Mage::getModel('stockhistory/transaction')->getCollection();
            $transactionsColl->getSelect()->where('po_id=' . $result->getId() . ' and product_id IS NOT null and action_type= 2')->order('id DESC');
            if ($transactionsColl->getSize()) {
                $total_units = $qty = 0;
                $pre_buys = array();
                $count = 0;
                $category_id = $transactionsColl->getFirstItem()->getCategoryId();
                $event = Mage::getModel('catalog/category')->load($category_id);
                $start_date = $event->getData('event_start_date');
                $end_date = $event->getData('event_end_date');

                $start_date = strtotime($start_date) - 60*60*24*7;	// 7 days before
                $end_date = strtotime($end_date) + 60*60*24*3;	// 3 days after

                $start_date = date('Y-m-d H:i:s', $start_date);
                $end_date = date('Y-m-d H:i:s', $end_date);
                foreach($transactionsColl as $trans) {
                    $product_id = $trans->getProductId();
                    $product = Mage::getModel('catalog/product')
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->load($product_id);

                    $ordersColl = Mage::getModel('sales/order_item')->getCollection();
                   $ordersColl->getSelect()->where('product_id =' . $product_id . ' and created_at between "'. $start_date . '" and "' . $end_date .'"');

                    foreach($ordersColl as $order) {
                        if($order->getParentItemId()) {
                            $parent_item_id = $order->getParentItemId();
                            $parent_order_line = Mage::getModel('sales/order_item')->getCollection();
                            $parent_order_line->getSelect()->where('item_id =' . $parent_item_id);
                            $order = $parent_order_line->getFirstItem();
                        }
                        $qty = $order->getQtyOrdered() - $order->getQtyReturned() - $order->getQtyCanceled();
                        $total_units += $qty;
                    }
                }
            }
            $result->setData('units_sold', $total_units);
            $result->save();
        }
    }
}
