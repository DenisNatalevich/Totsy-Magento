<?php
/**
 * 
 * @category 	Crown
 * @package 	Crown_Import 
 * @since 		1.0.0
 */
class Crown_Import_Model_Adminhtml_Source_Attribute_Set extends Mage_Core_Model_Abstract {
	/**
	 * Gets a list of the attribute sets
	 * @since 	1.0.0
	 * @return 	array
	 */
	public function getAllOptions() {
		if (! $this->_options) {
			$_extraOptions = array(
				-1 => 'Please Select an Attribute Set.',
			);
			$entity = Mage::getModel('catalog/product')->getCollection()->getEntity();
			$options = Mage::getResourceModel('eav/entity_attribute_set_collection')
	            ->setEntityTypeFilter($entity->getTypeId())
	            ->load();
			$optionsArray = $this->_toOptionArray ( $options, 'attribute_set_id', 'attribute_set_name' );
			$this->_options = $_extraOptions + $optionsArray;
		}
		return $this->_options;
	}
	
	/**
	 * Convert items array to array for select options
	 *
	 * return items array
	 * array(
	 * 		$index => array(
	 * 			'value' => mixed
	 * 			'label' => mixed
	 * 		)
	 * )
	 *
	 * @param 	object $collection
	 * @param   string $valueField
	 * @param   string $labelField
	 * @since 	1.0.0
	 * @return  array
	 */
	protected function _toOptionArray($collection, $valueField = 'id', $labelField = 'name', $additional = array()) {
		$res = array ();
		$additional ['value'] = $valueField;
		$additional ['label'] = $labelField;
		
		foreach ( $collection as $item ) {
			foreach ( $additional as $code => $field ) {
				$data [$code] = $item->getData ( $field );
			}
			$res [] = $data;
		}
		return $res;
	}
	
	/**
	 * Customer groups source for admin page
	 * @since 	1.0.0
	 * @return 	array
	 */
	public function toOptionArray() {
		if (is_null ( $this->_availableOptions ))
			$this->_availableOptions = $this->getAllOptions ();
		return $this->_availableOptions;
	}
}