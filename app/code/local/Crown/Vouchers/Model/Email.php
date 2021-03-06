<?php

class Crown_Vouchers_Model_Email extends Mage_Core_Model_Abstract {
	
	protected $productId; // Product ID
    protected $product; // Product Object
    protected $mailer; // Mail Object
    protected $customer; // Customer Object
    protected $order;
	
	public function sendEmail($product_id,$order) {
		$this->productId = $product_id;
        $this->order = $order;
		$this->init();
		$this->send();
	}
	
	protected function init() {
		$this->mailer = Mage::getModel('core/email_template');
		$this->customer = Mage::helper('customer')->getCustomer();
		$this->product = Mage::getModel('catalog/product')->load($this->productId);
	}
	
	
	protected function send() {
		$templateId = Mage::getModel('core/email_template')->loadByCode('_trans_Virtual_Product_Redemption')->getId();
        $quote  = Mage::getModel('sales/quote')->load($this->order->getQuoteId());
        $items  = $quote->getItemsCollection();
        $code   = array();
        if($this->product->getUseDefaultCouponCodes() == 1) {
            foreach($items as $item) {
                $options = Mage::getModel('sales/quote_item_option')->setItemId($item->getId())->getCollection()->getOptionsByItem($item);
                foreach($options as $option) {
                    if($option->getCode() == 'reservation_code') {
                        $code[] = $option->getValue();
                    }
                }
            }
        } else {
            $code[] = $this->product->getVoucherCode();
        }
        Mage::getModel('core/email_template')->sendTransactional(
            $templateId,
            "sales",
            $this->customer->getEmail(),
            $this->customer->getName(),
            array(
                "virtual_product_code" => implode(', ', $code),
                "order" => $this->order,
                "store" => Mage::app()->getStore(),
                "title" => $this->product->getName(),
                "description" => $this->product->getDescription(),
                "short_description" => $this->product->getShortDescription(),
            )
        );
        	
	}
	
}