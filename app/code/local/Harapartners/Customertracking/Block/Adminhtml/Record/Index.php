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


class Harapartners_Customertracking_Block_Adminhtml_Record_Index extends Mage_Adminhtml_Block_Widget_Grid_Container{
	
    public function __construct(){
		$this->_blockGroup = 'customertracking';
		$this->_controller = 'adminhtml_record_index';
    	$this->_headerText = Mage::helper('customertracking')->__('Customertracking');
    	//Remove add button, it should be read only!!
  		//$this->_addButtonLabel = Mage::helper('adminsecurity')->__('Add New Activity'); 		
        parent::__construct();
        $this->_removeButton('add');
    }
 
}