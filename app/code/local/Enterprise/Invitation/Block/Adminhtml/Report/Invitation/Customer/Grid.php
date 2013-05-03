<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Enterprise
 * @package     Enterprise_Invitation
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Adminhtml invitation customer report grid block
 *
 * @category   Enterprise
 * @package    Enterprise_Invitation
 */
class Enterprise_Invitation_Block_Adminhtml_Report_Invitation_Customer_Grid
    extends Mage_Adminhtml_Block_Report_Grid
{


    /**
     * Prepare report collection
     *
     * @return Enterprise_Invitation_Block_Adminhtml_Report_Invitation_Customer_Grid
     */
    protected function _prepareCollection()
    {
        parent::_prepareCollection();
        $email = $this->getFilter('report_email');
       	$this->getCollection()->setCustomerEmail($email);
        $this->getCollection()->initReport('enterprise_invitation/report_invitation_customer_collection');
        return $this;
    }

    /**
     * Prepare report grid columns
     *
     * @return Enterprise_Invitation_Block_Adminhtml_Report_Invitation_Customer_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    =>Mage::helper('enterprise_invitation')->__('ID'),
            'index'     => 'entity_id',
            'renderer'  => 'Enterprise_Invitation_Block_Adminhtml_Report_Invitation_Customer_Renderer_Entity'
        ));

        $this->addColumn('name', array(
            'header'    =>Mage::helper('enterprise_invitation')->__('Name'),
            'index'     => 'name'
        ));

        $this->addColumn('email', array(
            'header'    =>Mage::helper('enterprise_invitation')->__('Email'),
            'index'     => 'email'
        ));

        $this->addColumn('group', array(
            'header'    =>Mage::helper('enterprise_invitation')->__('Group'),
            'index'     => 'group_name'
        ));

        $this->addColumn('sent', array(
            'header'    =>Mage::helper('enterprise_invitation')->__('Invitations Sent'),
            'type'      =>'number',
            'index'     => 'sent'
        ));


        $this->addColumn('accepted', array(
            'header'    =>Mage::helper('enterprise_invitation')->__('Invitations Accepted'),
            'type'      =>'number',
            'index'     => 'accepted'
        ));
        
		//Hara Song --Start
        $this->addColumn('ordered_increment_id', array(
        	'header'	=> Mage::helper('enterprise_invitation')->__('# of Invitees Ordered'),
        	'type'		=> 'number',
        	'index'		=> 'order_increment_id'
        ));
        //Hara Song --End
        
        $this->addExportType('*/*/exportCustomerCsv', Mage::helper('enterprise_invitation')->__('CSV'));
        $this->addExportType('*/*/exportCustomerExcel', Mage::helper('enterprise_invitation')->__('Excel XML'));

        return parent::_prepareColumns();
    }

	public function getReportWithCustomerEmail($from, $to, $email)
	{
	
        if ($from == '') {
            $from = $this->getFilter('report_from');
        }
        if ($to == '') {
            $to = $this->getFilter('report_to');
        }
        if($email == '')
        {
        	$email = $this->getFilter('report_email');
        }
        //Hara Song
        if(Mage::registry('customer_email')){
        	Mage::unregister('customer_email');
        }
        if(!!$email){
        	Mage::register('customer_email', $email);
        }
        $totalObj = Mage::getModel('reports/totals');
        $this->setTotals($totalObj->countTotals($this, $from, $to));
        $this->addGrandTotals($this->getTotals());
        return $this->getCollection()->getReportWithCustomerEmail($from, $to, $email);
    }	
	
}
