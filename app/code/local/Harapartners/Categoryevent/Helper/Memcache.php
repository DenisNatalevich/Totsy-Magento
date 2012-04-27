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
 
class Harapartners_Categoryevent_Helper_Memcache extends Mage_Core_Helper_Abstract{	

	protected $_memcache = null;
	
	//Important, these lifetime should match the lifetime defined in the cache.xml for memcache!
	protected $_indexDataLifeTime = 14400;
	protected $_indexDataMemcachKey = 'catalog_event_index';
	protected $_topNavDataLifeTime = 1800;
	protected $_topNavDataMemcachKey = 'catalog_event_topnav';
	
	
	protected function _getMemcache(){
    	if(!$this->_memcache){
    		$this->_memcache = Mage::getSingleton('memcachedb/resource_memcache');
    	}
    	return $this->_memcache;
    }
    
    public function getIndexDataObject(){
		$indexData = array();
		$memcacheKey = 'DATA_' . Mage::app()->getStore()->getCode() . '_' . $this->_indexDataMemcachKey;
		
		$indexData = $this->_getMemcache()->read($memcacheKey);
		if(!$this->_validateIndexData($indexData)){
			$indexData = $this->_getIndexDataFromDb();
			$this->_getMemcache()->write($memcacheKey, $indexData, $this->_indexDataLifeTime);
		}
		return new Varien_Object($indexData);
    }
    
	public function getTopNavDataObject(){
		$topNavData = array();
		$memcacheKey = 'DATA_' . Mage::app()->getStore()->getCode() . '_' . $this->_topNavDataMemcachKey;
		//Need to put request parameter in the key
		$params = Mage::app()->getRequest()->getParams();
		if(isset($params) && !!$params){
			foreach ($params as $key => $value){
				$memcacheKey .= '_' . $key . '_' . $value;
			}
		}
		
		$topNavData = $this->_getMemcache()->read($memcacheKey);
		if(!$this->_validateTopNavData($topNavData)){
			$topNavData = $this->_getTopNavDataFromDb();
			$this->_getMemcache()->write($memcacheKey, $topNavData, $this->_topNavDataLifeTime);
		}
		return new Varien_Object($topNavData);
    }
    
    
    // ===== Internal processing ===== //
    // ===== Index ===== //
    protected function _getIndexDataFromDb(){
    	$indexData = array();
		$defaultTimezone = date_default_timezone_get();
		$mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
		date_default_timezone_set($mageTimezone);
		$sortDate = now("Y-m-d");
		$currentTime = now();
		date_default_timezone_set($defaultTimezone);
		$storeId = Harapartners_Service_Helper_Data::TOTSY_STORE_ID;
		//$storeId = Mage::app()->getStore()->getId();
		//$sortentry = Mage::getModel('categoryevent/sortentry')->loadByDate($sortDate, $storeId, false);
		$sortentry = Mage::getModel('categoryevent/sortentry')->filterByCurrentTime($sortDate, $currentTime, $storeId);
		
		$indexData['toplive'] = json_decode($sortentry->getData('top_live_queue'), true);
		$indexData['live'] = json_decode($sortentry->getData('live_queue'), true);
		$indexData['upcoming'] = json_decode($sortentry->getData('upcoming_queue'), true);
		return $indexData;
    }
    
    protected function _validateIndexData($indexData){
    	if(!isset($indexData['toplive'])){
			return false;
		}
		if(!isset($indexData['live'])){
			return false;
		}
		return true;
    }
    

    // ===== Top Nav ===== //
	protected function _getTopNavDataFromDb(){
		$topNavData = array();
		$topNavData = $this->_prepareTopNavData();
		return $topNavData;
    }
    
	protected function _validateTopNavData($topNavData){
		if(empty($topNavData['category_product_complete_data'])){
			return false;
		}
		return true;
	}
	
	protected function _prepareTopNavData(){
		//Default data structure/values
		$topNavData = array(
				'attr_text_label' => 'Results',
				'category_product_complete_data' => array()
		);
		
		try{
			$defaultTimezone = date_default_timezone_get();
			$mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
			date_default_timezone_set($mageTimezone);
			$sortDate = now("Y-m-d");
			date_default_timezone_set($defaultTimezone);
			$storeId = Mage::app()->getStore()->getId();
			
			//To Jun: Note for rebuild script try not to get from cached or indexed data??
			
			// ---------- //
			// Load sorted live category info

			$sortentryLive = Mage::getModel('categoryevent/sortentry')->loadByDate($sortDate, $storeId, false)->getLiveQueue();
			$liveCategoryInfoArray = json_decode($sortentryLive, true);
			$liveCategoryIdArray = array();
			foreach ($liveCategoryInfoArray as $category){
				if(isset($category['entity_id']) && $category['entity_id']){
					$liveCategoryIdArray[] = $category['entity_id'];
				}
			}
			
			//To Jun: these code need to moved into the resource models
			// ---------- //
			// Load category <==> product relationship
			//Important validation
			if(!$liveCategoryIdArray){
				throw new Exception('No event available');
			}
			
			$readAdapter = Mage::getSingleton('core/resource')->getConnection('core_read');
			$selectQuery = $readAdapter->select()->from('catalog_category_product')->where('`category_id` IN(' . implode(',', $liveCategoryIdArray) . ')');
			$categoryProductRelations = $readAdapter->fetchAll($selectQuery);
			// Prepare unique product ID for the product collection query
			// Also, group products by category
			$uniqueProductIds = array();
			$categoryProductCompleteData = array();
			foreach($categoryProductRelations as $relation){
				if(isset($relation['category_id']) 
						&& !!$relation['category_id']
						&& isset($relation['product_id']) 
						&& !!$relation['product_id']){
							
					if(!in_array($relation['product_id'], $uniqueProductIds)){
						$uniqueProductIds[] = $relation['product_id'];
					}
					if(!array_key_exists($relation['category_id'], $categoryProductCompleteData)){
						$categoryProductCompleteData[$relation['category_id']] = array('product_list' => array());
					}
					if(!array_key_exists($relation['product_id'], $categoryProductCompleteData[$relation['category_id']]['product_list'])){
						$categoryProductCompleteData[$relation['category_id']]['product_list'][$relation['product_id']] = array();
					}
				}
			}
			
			// ---------- //
			// Load product collection
			
			//Important validation
			if(!$uniqueProductIds){
				throw new Exception('No product available');
			}
			
			//$type = Mage::registry('attrtype');
			$type = Mage::app()->getRequest()->getParam('type');
			//$value = Mage::registry('attrvalue');
			$value = Mage::app()->getRequest()->getParam('value');
			$typeAttributes = Mage::getModel('catalog/product')->getResource()->getAttribute($type);
			$valueId = $typeAttributes->getSource()->getOptionId($value);
			$label = Mage::helper('catalog')->__($value);
			
			
			$cateAttr = Mage::getModel('catalog/category')->getResource()->getAttribute($type);
			$catelabel = $cateAttr->getSource()->getOptionText($value);
			$categoryLabel = Mage::helper('catalog')->__($catelabel);
			
			
			$productCollection = Mage::getModel('catalog/product')->getCollection();
			$productCollection->getSelect()->where('`e`.`entity_id` IN(' . implode(',', $uniqueProductIds) . ')');
			$productCollection->addFieldToFilter($type, array('like' => '%'.$valueId.'%'));
			$productCollection->addFieldToFilter('visibility', array("in" => array(
					Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
					Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
			)));
			$productCollection->addAttributeToSelect(array(
					'name', 
	        		'type_id', 
	        		'small_image', 
	        		'thumbnail',
	        		'url_path',
					'special_price',
					'original_price',
					'price')
	        );
			$productInfoArray = $productCollection->load()->toArray();
			
			foreach($liveCategoryInfoArray as &$liveCategoryInfo){
				$liveCategoryInfo['product_info_array'] = array();
				foreach ($categoryProductRelations as $relation){
					if(isset($relation['category_id'])
							&& isset($relation['product_id'])
							&& isset($liveCategoryInfo['entity_id'])
							&& $relation['category_id'] == $liveCategoryInfo['entity_id']){
						$productId = $relation['product_id'];
							
					}
				}
			}
			
			// ---------- //
			// Assemble data here
			// Note pass array by reference!
			$mediaBaseDir = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
			$emptyCategoryIds = array();
			foreach($categoryProductCompleteData as $categoryId => &$categoryInfoContainer){
				//put in product info
				if(empty($categoryInfoContainer['product_list'])){
					unset($categoryProductCompleteData[$categoryId]);
				}
				$categoryHasProduct = false;
				foreach($categoryInfoContainer['product_list'] as $containerProductId => &$containerProductInfo){
					$productFound = false;
					foreach($productInfoArray as $productId => $productInfo){
						if($containerProductId == $productId){
							$containerProductInfo = $productInfo;
							if(isset($productInfo['small_image'])){
								//$containerProductInfo['small_image'] = $mediaBaseDir . str_ireplace('/', DS, $productInfo['small_image']);    //it call from file system like F:\www\totsy\media\catalog\product\n\e\newborn_tiered_dots_top_2pc_diaper_set.jpg
								if ($productInfo['small_image'] != 'no_selection'){
									$containerProductInfo['small_image'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product'.$productInfo['small_image'];
								}else {
									$containerProductInfo['small_image'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product/placeholder/default/small_image.jpg';
								}
							}
							$categoryHasProduct = true;
							$productFound = true;
							break;
						}
					}
					if(!$productFound){
						unset($categoryInfoContainer['product_list'][$containerProductId]);
					}
				}
				if(!$categoryHasProduct){
					unset($categoryProductCompleteData[$categoryId]);
				}
				
				//put in category info
				$isCategoryLive = false;
				foreach($liveCategoryInfoArray as $liveCategoryInfo){
					if(isset($liveCategoryInfo['entity_id'])
							&& $liveCategoryInfo['entity_id'] == $categoryId){
						$categoryInfoContainer['category_info'] = $liveCategoryInfo;
						$categoryInfoContainer['prepare_timer'] = $this->_prepareTimer($liveCategoryInfo['event_end_date']);
						
						$isCategoryLive = true;
						break;
					}
				}
				if(!$isCategoryLive){
					unset($categoryProductCompleteData[$categoryId]);
				}
			}
			
			$topNavData['attr_text_label'] = $categoryLabel;
			//$topNavData['attr_text_label'] = $label; use this if you want to show something like infant without 0-6m in the title
			
			
			$topNavData['category_product_complete_data'] = $categoryProductCompleteData;
		}catch(Exception $e){
			$topNavData ['category_product_complete_data'] = array();
		}
		
		return $topNavData;
	}

	protected function _prepareTimer($eventEndDate){
		
		$defaultTimezone = date_default_timezone_get();
		$mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
		$endcount_utc = strtotime($eventEndDate);
		
		//$endcount_lc: local end count date
		date_default_timezone_set($mageTimezone);
		$endcount_lc = date("F j, Y, G:i:s", $endcount_utc);
		date_default_timezone_set($defaultTimezone);
		
		if ( !Mage::getSingleton('customer/session')->hasData('countdown_timer') ) {
			$timer = 0;
			Mage::getSingleton('customer/session')->setData('countdown_timer', $timer++);
			$timer = Mage::getSingleton('customer/session')->getData('countdown_timer');
		} else {	
			$timer = Mage::getSingleton('customer/session')->getData('countdown_timer');
			Mage::getSingleton('customer/session')->setData('countdown_timer', ++$timer);
		}
		
		$returnparam=array('endcount_lc'=>$endcount_lc, 'timer'=>$timer);
		return $returnparam;
	}
	
}