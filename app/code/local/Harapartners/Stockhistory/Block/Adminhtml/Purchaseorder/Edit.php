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

class Harapartners_Stockhistory_Block_Adminhtml_Purchaseorder_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();
		$this->_objectId = 'id';
		$this->_blockGroup = 'stockhistory';
		$this->_controller = 'adminhtml_purchaseorder';
		$this->_addButton('transaction_add', array(
            'label'     => Mage::helper('stockhistory')->__('Create Amendment'),
            'onclick'   => 'setLocation(\'' . $this->getCreateAmendmentUrl() .'\')',
			'class'		=> 'add',
      	));
	}
	
	public function getHeaderText() {
    	return Mage::helper('stockhistory')->__('Purchase Order Info');
    }

    public function getSaveUrl(){
        return $this->getUrl('*/*/save', array('_current'=>true));
    }
    
    public function getCreateAmendmentUrl()
    {
    	return $this->getUrl('stockhistory/adminhtml_history/new', array(
    									'vendor_id' => $this->getVendorId(),
    									'po_id' => $this->getPoId(),
    									));
    }
	
    public function getVendorId()
    {   
    	$poInfo = Mage::registry('po_data');
    	return $poInfo['vendor_id'];
    }
    
    public function getPoId()
    {
    	$poInfo = Mage::registry('po_data');
    	return $poInfo['id'];
    }
}