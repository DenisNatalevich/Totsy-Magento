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

class Harapartners_Stockhistory_Block_Adminhtml_Transaction_Report extends Mage_Adminhtml_Block_Widget_Grid_Container {
	
	public function __construct() {
		$dataObject = new Varien_Object(Mage::registry('stockhistory_transaction_report_data'));
		parent::__construct();
		$this->_controller = 'adminhtml_transaction_report';
		$this->_blockGroup = 'stockhistory';
		$this->_headerText = Mage::helper('stockhistory')->__('Product Report from PO ' . $dataObject->getData('po_id'));
		$this->_removeButton('add');
		$this->_addButton('submit_dotcom_po', array(
            'label'     => Mage::helper('stockhistory')->__('Submit to DOTcom'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('stockhistory/adminhtml_transaction/submitToDOTcom/po_id/' . $dataObject->getData('po_id')) . '\')',
        ));
		$this->_addButton('print_report', array(
            'label'     => Mage::helper('stockhistory')->__('Print Report'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('stockhistory/adminhtml_transaction/print/po_id/' . $dataObject->getData('po_id')) . '\')',
        ));
	}

}