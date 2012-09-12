<?php
/**
 * @category    Totsy
 * @package     Totsy_Adminhtml_Model_Sales_Order_Create
 * @author      Tom Royer <troyer@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */
class Totsy_Adminhtml_Model_Sales_Order_Create extends Mage_Adminhtml_Model_Sales_Order_Create
{

    /**
     * Create new order
     *
     * @return Mage_Sales_Model_Order
     */
    public function createOrder()
    {
        $this->_prepareCustomer();
        $this->_validate();
        $quote = $this->getQuote();
        $this->_prepareQuoteItems();

        $service = Mage::getModel('sales/service_quote', $quote);
        if ($this->getSession()->getOrder()->getId()) {
            $oldOrder = $this->getSession()->getOrder();
            $originalId = $oldOrder->getOriginalIncrementId();
            if (!$originalId) {
                $originalId = $oldOrder->getIncrementId();
            }
            $orderData = array(
                'original_increment_id'     => $originalId,
                'relation_parent_id'        => $oldOrder->getId(),
                'relation_parent_real_id'   => $oldOrder->getIncrementId(),
                'edit_increment'            => $oldOrder->getEditIncrement()+1,
                'increment_id'              => $originalId.'-'.($oldOrder->getEditIncrement()+1)
            );
            $quote->setReservedOrderId($orderData['increment_id']);
            $service->setOrderData($orderData);
        }

        $order = $service->submit();
        if ((!$quote->getCustomer()->getId() || !$quote->getCustomer()->isInStore($this->getSession()->getStore()))
            && !$quote->getCustomerIsGuest()
        ) {
            $quote->getCustomer()->setCreatedAt($order->getCreatedAt());
            $quote->getCustomer()
                ->save()
                ->sendNewAccountEmail('registered', '', $quote->getStoreId());;
        }
        //Sync Item Stock with Item Stock Status
        foreach($order->getItemsCollection() as $item) {
            $indexerStock = Mage::getModel('cataloginventory/stock_status');
            $indexerStock->updateStatus($item->getProductId());
            //Make Sure that parent product status stay 1
            $configurable_product_model = Mage::getModel('catalog/product_type_configurable');
            $parentIds = $configurable_product_model->getParentIdsByChild($item->getProductId());
            if ($parentIds) {
                foreach ($parentIds as $parentId) {
                    var_dump($parentId);
                    $stockStatus = Mage::getModel('cataloginventory/stock_status')->load($parentId,'product_id');
                    $stockStatus->setData('stock_status','1')
                                ->save();
                }
            }        }
        if ($this->getSession()->getOrder()->getId()) {
            $oldOrder = $this->getSession()->getOrder();

            $this->getSession()->getOrder()->setRelationChildId($order->getId());
            $this->getSession()->getOrder()->setRelationChildRealId($order->getIncrementId());
            $this->getSession()->getOrder()->cancel()
                ->setStatus('updated')
                ->setState('updated')
                ->save();
            $order->save();
        }
        if ($this->getSendConfirmation()) {
            $order->sendNewOrderEmail();
        }

        Mage::dispatchEvent('checkout_submit_all_after', array('order' => $order, 'quote' => $quote));

        return $order;
    }

}