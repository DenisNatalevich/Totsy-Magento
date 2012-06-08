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

class Harapartners_Promotionfactory_Block_Adminhtml_Virtualproductcoupon_Managecoupon_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct(){
        parent::__construct();
        $this->setId('virtual-product-coupon-grid');
    }

    protected function _prepareCollection(){
    	$currentProduct = Mage::registry('manage_coupon_virtual_product');
        $coupons = Mage::getModel('promotionfactory/virtualproductcoupon')->loadByProductId( $currentProduct->getId() );
        $this->setCollection($coupons);
        parent::_prepareCollection();
        return $this;
    }

    protected function _getStore(){
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

	protected function _prepareColumns(){        
        $this->addColumn('entity_id', array(
            'header'        => Mage::helper('promotionfactory')->__('ID'),
            'index'         => 'entity_id'
        ));

        $this->addColumn('product_id', array(
            'header'        => Mage::helper('promotionfactory')->__('Product ID'),
            'index'         => 'product_id'
        ));
        
        $this->addColumn('code', array(
            'header'        => Mage::helper('promotionfactory')->__('Code'),
            'index'         => 'code'
        ));
        
       $this->addColumn('created_at', array(
            'header'        => Mage::helper('promotionfactory')->__('Created At'),
            'index'         => 'created_at',
        ));
        
        $this->addColumn('updated_at', array(
            'header'        => Mage::helper('promotionfactory')->__('Updated At'),
            'index'         => 'updated_at',
        ));
        
        $this->addColumn('status', array(
            'header'        => Mage::helper('promotionfactory')->__('Status'),
            'index'         => 'status',
        	'type'			=> 'options',
            'options' => Mage::getModel( 'promotionfactory/virtualproductcoupon' )->toArrayOption()
        ));

        return parent::_prepareColumns();
    }

}