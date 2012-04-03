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

class Harapartners_Import_Helper_Processor extends Mage_Core_Helper_Abstract {
	
	protected $_errorFile 			= null;
	protected $_errorMessages 		= array();
	protected $_requiredFields 		= array();
	protected $_confSimpleProducts 	= array();
	
	protected function _logError($errorMessage){
		$errorMessage = 'Row '.$recordCount.': '.$ex->getMessage()."\n";
						$this->_errorMessages[] = $errorMessage;
						fwrite($this->getErrorFile(), $errorMessage);
	}
	
	protected function _getErrorFile($importModelId){
		if(!$this->_errorFile){
			$filename = $this->getErrorFilePath(). date('Y_m_d'). '_' . $importModelId . '.txt';
			$this->_errorFile = fopen($filename,'w');
			$this->_errorMessages = array(); 
		}
		return $this->_errorFile;
	}
	
	protected function _getErrorFilePath(){
		return Mage::getBaseDir('media').DS.'import'.DS.'errors'.DS;
	}
	
	protected function _getImportModel($importId = null){
		$import = Mage::getModel('import/import');
		if(!!$importId){
			$import->load($importId);
		}
		//If not specified, only runs the last 'uploaded' import
		if(!$import || !$import->getId()){
			$collection = Mage::getModel('import/import')->getCollection();
			$collection->addFieldToFilter('import_status', Harapartners_Import_Model_Import::IMPORT_STATUS_UPLOADED);
			$collection->getSelect()->limit(1);
			$import = $collection->getFirstItem();
		}
		return $import;
	}
	
	
	protected function _getRequiredFields(){
		$this->_requiredFields[] = 'store';
        $this->_requiredFields[] = 'type';  
        $this->_requiredFields[] = 'attribute_set';
        $this->_requiredFields[] = 'sku';
        
		$fieldset = Mage::getConfig()->getFieldset('catalog_product_dataflow', 'admin');
        foreach ($fieldset as $code => $node) {
        	if ($node->is('required')) {
                $this->_requiredFields[] = $code;
            }
        }
	}
	
	protected function _setProductSku($importData){
		$sku = '';
		if(isset($importData['vendor']) 
		&& isset($importData['vendor_style']) 
		&& isset($importData['color']) 
		&& isset($importData['size'])){
			$sku = $importData['vendor'].'-'.$importData['vendor_style'].'-'.$importData['color'].'-'.$importData['size'];
		}else{
			$sku = 'hp-'.date('y-m-d-H-i-s-u');
		}
		return $sku;
	}
	protected function _setRequiredAttributes($importData){
		foreach ($this->_requiredFields as $field) {
			if (!isset($importData[$field])){
				switch ($field) {
					case 'store':
						$importData['store'] = 'admin';
						break;
					case 'type':
						$importData['type'] = 'simple';
						break;
					case 'attribute_set':
						$importData['attribute_set'] = 'Totsy';
						break;
					case 'sku':
						$importData['sku'] = $this->_setProductSku($importData);
						break;
					case 'name':
						$importData['name'] = $importData['sku'];
						break;
					case 'description':
						$importData['description'] = 'description';
						break;
					case 'short_description':
						$importData['short_description'] = 'blurb';
						break;
					case 'weight':
						$importData['weight'] = '1';
						break;
					case 'price':
						$importData['price'] = '1';
						break;
					case 'tax_class_id':
						$importData['tax_class_id'] = 'Taxable Goods';
						break;
				}
			}
		}
		if($importData['type'] == 'configurable'){
			$importData['configurable_attribute_codes'] = 'color,size';  //Hard Coded.  Need to enforce in template!
			$importData['conf_simple_products']			= implode(',',$this->_confSimpleProducts);
		}else{
			$this->_confSimpleProducts[] = $importData['sku'];
		}
		return $importData;
	}
	
	protected function _setPurchaseOrderInfo($importData){
		$a=1;
	}
	
	public function runImport($importId = null){
		$import = $this->_getImportModel($importId);
		if(!$import || !$import->getId() || !$import->getData('import_batch_id')){
			//Nothing to run
			return true;
		}

		// ===== dataflow, processing ===== //
		try{
			$batchModel = Mage::getModel('dataflow/batch')->load($import->getData('import_batch_id'));
			if (!!$batchModel && !!$batchModel->getId()){
				$batchImportModel = $batchModel->getBatchImportModel(); //read line item
				$adapter = Mage::getModel($batchModel->getAdapter()); //processor/writer
				
				//update status to 'lock' this import
				$import->setImportStatus(Harapartners_Import_Model_Import::IMPORT_STATUS_PROCESSING);
				//$import->save();
				
				//collection load is not possible due to the large amount of data per row
				$batchId = $batchModel->getId();  
				$importIds = $batchImportModel->getIdCollection($batchId);
				
				//Get the required fields
				$this->_getRequiredFields();
				
				foreach ($importIds as $importId) {	
					try{	
						$batchImportModel->load($importId);
						if (!$batchImportModel || !$batchImportModel->getId()) {
							$this->_logError(Mage::helper('dataflow')->__('Skip undefined row'));
							continue;	
						}
						$importData = $batchImportModel->getBatchData();
						$adapter->saveRow($this->_setRequiredAttributes($importData));

						/**
						 * PO Saves Here
						 */
						$this->_setPurchaseOrderInfo($importData);
						
	
					} catch(Exception $ex) {
						//TODO
						$this->_logError(Mage::helper('dataflow')->__('Skip undefined row'));
					}  
				}
				if($hasErrors){
					$import->setImportStatus('import_import_error<a href="'.Mage::getBaseUrl().'media/import/errors/'.date('Y_m_d').'_'.$import->getData('import_import_id').'.txt">Error</a>');
					$import->save();   
				}else{
					$import->setImportStatus(Harapartners_Import_Model_Import::IMPORT_STATUS_COMPLETE);
					$import->save();
				}
		  
			}
			$batchModel->delete();
			fclose();
			return true;
		}catch(Exception $ex){
			return false;
		}
	}
	
}