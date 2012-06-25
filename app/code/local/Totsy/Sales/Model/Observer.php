<?php
/**
 * @category    Totsy
 * @package     Totsy_Sales_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Sales_Model_Observer
{
    /**
     * Increment the customer purchase count attribute after an order has been
     * placed.
     * Subscribed to the 'sales_order_place_after' event.
     *
     * @param Varien_Event_Observer $observer
     * @return null|int The updated purchase count for the customer.
     */
    public function incrementCustomerPurchaseCount(
        Varien_Event_Observer $observer
    ) {
        $order = $observer->getEvent()->getOrder();

        if ($customer = $order->getCustomer()) {
            $customer->setPurchaseCounter($customer->getPurchaseCounter() + 1)
                ->save();

            return $customer->getPurchaseCounter();
        }

        return null;
    }

    /**
     * Decrement the customer purchase count attribute after an order has been
     * placed.
     * Subscribed to the 'order_cancel_after' event.
     *
     * @param Varien_Event_Observer $observer
     * @return null|int The updated purchase count for the customer.
     */
    public function decrementCustomerPurchaseCount(
        Varien_Event_Observer $observer
    ) {
        $order = $observer->getEvent()->getOrder();

        // not entirely sure why, but $order-getCustomer() returns NULL here
        if ($customerId = $order->getCustomerId()) {
            $customer =Mage::getModel('customer/customer')->load($customerId);
            $customer->setPurchaseCounter($customer->getPurchaseCounter() - 1)
                ->save();

            return $customer->getPurchaseCounter();
        }

        return null;
    }

    /**
     * Harapartners, Jun
     * Performance optimization, no action during batch import.
     *
     * @param Varien_Event_Observer $observer
     * @return Totsy_Sales_Model_Observer
     */
    public function catalogProductSaveAfter(Varien_Event_Observer $observer) {
        if(!!Mage::registry('is_batch_import_process')){
            return $this;
        }
        $product = $observer->getEvent()->getProduct();
        if ($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
            return $this;
        }
        Mage::getResourceSingleton('sales/quote')->markQuotesRecollect($product->getId());
        return $this;
    }
}
