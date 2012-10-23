<?php
/**
 * PHP Version 5.3
 *
 * @category  Totsy
 * @package   Totsy_Sailthru
 * @author    Slavik Koshelevskyy <skosh@totsy.com>
 * @copyright 2012 Totsy LLC Copyright (c) 
 */

class Totsy_Sailthru_Model_Feed extends Mage_Core_Model_Abstract 
{
	private $_cache = null;
	private $_feed = null;
	private $_output = array(
		'events'=>array(), 
		'pending'=>array(), 
		'closing'=>array(),
		'max_off'=>0
	);
	private $_shortLenght = 45;

	/**
	* Class construction method
	*
	* @return void
	*/
	public function __construct()
	{
		$this->_cache = Mage::helper('sailthru/cache');
		$this->_feed  = Mage::helper('sailthru/feed');
		parent::__construct();
	}

	public function runner()
	{
		$this->_feed->processor();

		//open&top events
		$this->_formatter(
			$this->getFeedHelper()->goingLive(
				$this->_getSortEvents('live')
			),
			'events'
		);

		// closing events
		$this->_formatter(
			$this->getFeedHelper()->filter(
				array_merge(
					//$this->_getSortEvents('live'),
					$this->_getSortEvents('live','+1 day')
				)
			),
			'closing'
		);

		//pending events       
		$this->_formatter(
			$this->getFeedHelper()->filter(
				$this->_getSortEvents('upcoming'),
				'start'
			),
			'pending'
		);
	}

	public function getOutPut(){
		$json = json_encode($this->_output);
		$this->_cache->_setRightHttpHost($json);
		return $json;
	}

	/**
	* Returns Sailthru Cache Helper
	*
	* @return Totsy_Sailthru_Helper_Cache object
	*/
	public function getCacheHelper()
	{
		return $this->_cache;
	}

	/**
	* Returns Sailthru Feed Helper
	*
	* @return Totsy_Sailthru_Helper_Feed object
	*/
	public function getFeedHelper()
	{
		return $this->_feed;
	}

	private function _getProductsIds($event_id){
    	$productCollection = Mage::getModel('catalog/category')
	        ->load($event_id)
	        ->getProductCollection()
	        ->addAttributeToSelect('entity_id')
	        ->setVisibility(
	            Mage::getSingleton('catalog/product_visibility')
	                ->getVisibleInCatalogIds()
	        );
	    $productIds = array();
	    foreach ($productCollection as $product){
	        $productIds[] = $product->getId();
	    }
	    return $productIds;
	}

    private function _formatter ($events,$type){
        $max_off = null;
        if (empty($events) || !is_array($events)){
            return; 
        } 

        foreach ($events as $key => $event){
            $event_tmp = array();
            if ($type == 'events'){
                $event['products'] = $this->_getProductsIds($event['entity_id']);
                $event_tmp = $this->getFeedHelper()->formatEvent($event);
                if ($event_tmp['discount']>$max_off){
                    $max_off = $event_tmp['discount'];
                }
            } else if ($type=='pending'){
            	$event_tmp = $this->getFeedHelper()->formatPCEvent($event,'end');
            } else if ($type=='closing'){
            	$event_tmp = $this->getFeedHelper()->formatPCEvent($event,'start');
            }

            $this->_output[$type][$key] = $event_tmp;
        }
        if (!is_null($max_off)){
            $this->_output['max_off'] = $max_off;
        }
    }

    private function _getSortEvents($type,$plus=null){
    	$date = $this->getFeedHelper()->getStartDate();
    	if (!is_null($plus)){
    		$date = $this->getFeedHelper()->getStartDate();
    	}
    	$sort = Mage::getModel('categoryevent/sortentry')->loadByDate(date('Y-m-d',$date))->getData();
    	$return = json_decode($sort[$type.'_queue'],true);	
    	return $return;
    }
}