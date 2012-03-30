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

class Harapartners_Stockhistory_Block_Adminhtml_History_Index extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{	
		parent::__construct();
		$this->_controller = 'adminhtml_history_index';
		$this->_blockGroup = 'stockhistory';
		$this->_headerText = Mage::helper('stockhistory')->__('Purchase Order Manager');
		$this->_removeButton('add');
//		$this->_addButton('update_status', array(
//												'label' => Mage::helper('stockhistory')->__('Update Status'),
//												'onclick' => "setLocation('".$this->getUrl('*/*/massStatus')."')"
//											));
//		$this->_addButton('import', array(
//     							'label'		=> 	Mage::helper('advancereport')->__('Import'),
//   						'onclick'   => "setLocation('".$this->getUrl('*/*/import')."')"
//     					),-1,4,'header','header'
//     	);
     	$this->_addButton('import', array(
            'label'     => Mage::helper('stockhistory')->__('Import Order'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/importCsv') .'\')',
      	));
//      	
//      	$this->_addButton('export', array(
//      		'label'		=> 	Mage::helper('stockhistory')->__('Export'),
//      		'onclick'	=>	'setLocation(\''. $this->getCreateUrl() . '\')',
//      	));
	}
}