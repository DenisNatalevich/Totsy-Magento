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

class Harapartners_Stockhistory_Model_Transaction extends Mage_Core_Model_Abstract {
    
    const STATUS_PENDING = 1;
    const STATUS_PROCESSED = 2;
    const STATUS_FAILED = 3;
    
    const ACTION_TYPE_AMENDMENT = 1;
    const ACTION_TYPE_EVENT_IMPORT = 2;
    const ACTION_TYPE_DIRECT_IMPORT = 3;
    const ACTION_TYPE_REMOVE = 4;
    
    public function _construct() {
        $this->_init('stockhistory/transaction');
    }
    
    protected function _beforeSave(){
        parent::_beforeSave(); 
        if(!$this->getId()){
            $this->setData('created_at', now());
        }
        $this->setData('updated_at', now());
        
        if(!$this->getStoreId()){
            $this->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);
        }
        $this->validate();
        return $this;
    }
    
    public function loadByProductId($productId) {
        $this->addData($this->getResource()->loadByProductId($productId));
        return $this;
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
        
        //Load product
        $product = Mage::getModel('catalog/product');
        if(!!$dataObj->getdata('product_id')){
            $product->load($dataObj->getdata('product_id'));
        }elseif(!!$dataObj->getdata('product_sku')){
            $product->loadByAttribute('sku', $dataObj->getdata('product_sku'));
        }
        if(!$product || !$product->getId()){
            Mage::throwException('Invalid Product.');
        }
        if($product->getTypeId() != 'simple'){
            Mage::throwException('Purchase should only contain simple product. Other product types are ignored.');
        }
        $dataObj->setData('product_id', $product->getId());
        $dataObj->setData('product_sku', $product->getSku());
        $dataObj->setData('vendor_style', $product->getVendorStyle());
        
        if(!$dataObj->getData('action_type')){
            $dataObj->setData('action_type', self::ACTION_TYPE_AMENDMENT);
        }
        
    	if($dataObj->getData('qty_delta') === '00') {
        	//put a flag to indicate this item should be delete remove this line item
        	$qtyDelta = 0;
        	$dataObj->setData('action_type', self::ACTION_TYPE_REMOVE);
        }
        
        $this->addData($dataObj->getData());
        return $this;
    }
    
    public function validate(){
        if(!$this->getData('vendor_id')){
            throw new Exception('Vendor ID is required.');
        }
        if(!$this->getData('po_id')){
            throw new Exception('Purchase order ID is required.');
        }
        if(!$this->getData('category_id')){
            throw new Exception('Category ID is required.');
        }
        
        if(!$this->getData('product_id')){
            throw new Exception('Product ID is required.');
        }
        $product = Mage::getModel('catalog/product')->load($this->getData('product_id'));
        if($product->getTypeId() != 'simple'){
            Mage::throwException('Purchase should only contain simple product. Other product types are ignored.');
        }
        $qtyDelta = $this->getData('qty_delta');
        if(!isset($qtyDelta)){
            throw new Exception('Qty Changed is required.');
        }
        if(!!$this->getData('unit_cost')){
            $unitCost = $this->getData('unit_cost');
            if($unitCost < 0){
                throw new Exception('Unit cost must be a non-negative number.');
            }
        }else{
            throw new Exception('Unit Cost is required.');
        }
        
        return $this;
    }
    
    public function updateProductStock(){
        $product = Mage::getModel('catalog/product')->load($this->getData('product_id'));
        if($product->getTypeId() != 'simple'){
            Mage::throwException('Purchase should only contain simple product. Other product types are ignored.');
        }

      //  var_dump($this->getData());
     //  var_dump($product->getData());
        $productData = $product->getData();

        $tempProductId = $this->getData('product_id');
        $category = Mage::getModel('catalog/category')->load($this->getData('category_id'));
        $sold = Mage::helper('stockhistory')->getProductSoldInfoByCategory($category, array( $tempProductId => $tempProductId ));
        debug('Sold is : ' . $sold[$tempProductId]['qty'] . " It's boolean is " . (bool)$sold[$tempProductId]['qty']) ;
        debug("Action type is " . $this->getData('action_type') );
        debug("Product is a case pack? " . (bool)$product->getData('is_master_pack') );
      //  var_dump($sold[$tempProductId]['qty']);
        
        $bool = $sold[$tempProductId]['qty'] || $product->getData('is_master_pack');
        debug('First condition result ~sold_qty || is case pack:' . $bool);
        $bool = $bool && ($this->getData('action_type') == 4);
        debug('Second condition result First bool && action_type == 4' . $bool);
       // var_dump(!$bool);
       // die();
        if (!$bool) {
            $stock = $product->getStockItem();
            $qtyStock = $stock->getQty();
            $qtyDelta = $this->getData('qty_delta');
            if(($qtyStock + $qtyDelta) < 0){
                throw new Exception('This stock update will result in a negative value. Ignored.');
            }
            
            $stock->setQty($qtyStock + $qtyDelta);
            $stock->save();
            return true;
        } else {
            throw new Exception('Cannot remove item that ' . ($product->getData('is_master_pack')) ? 'is a case pack ' : 'has been sold');
        }
    }

    public function changeCasePackStatus($items, $changeto) {

        $product_collection = Mage::getModel('catalog/product')->getCollection();
        $product_collection->getSelect()->where('entity_id in (' . implode(',' , $items) . ')' );
        foreach($product_collection as $product) {
			$product->setData('_edit_mode', true);
            $product->setFulfillmentTYpe('dotcom');
            $product->setIsMasterPack((int)$changeto);
            $product->setVisibility(1);
            $product->save();           
        }
    }
    
}
