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
        if ($this->getSession()->getOrder()->getId()) {

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

    /**
     * Update quantity of order quote items
     *
     * @param   array $data
     * @return  Mage_Adminhtml_Model_Sales_Order_Create
     */
    public function updateQuoteItems($data)
    {
        if (is_array($data)) {
            try {
                foreach ($data as $itemId => $info) {
                    if (!empty($info['configured'])) {
                        $item = $this->getQuote()->updateItem($itemId, new Varien_Object($info));
                        $itemQty = (float)$item->getQty();
                    } else {
                        $item       = $this->getQuote()->getItemById($itemId);
                        $itemQty    = (float)$info['qty'];
                    }

                    if ($item) {
                        if ($item->getProduct()->getStockItem()) {
                            if (!$item->getProduct()->getStockItem()->getIsQtyDecimal()) {
                                $itemQty = (int)$itemQty;
                            } else {
                                $item->setIsQtyDecimal(1);
                            }
                        }
                        $oldItemQty = 0;
                        if($this->getSession()->getOrder()) {
                            $oldOrderItems = $this->getSession()->getOrder()->getItemsCollection();
                            foreach ($oldOrderItems as $oldItem){
                                if($oldItem->product_id == $item->getProductId()) {
                                       $oldItemQty = (int)$oldItem->getQtyOrdered();
                                }
                            }
                        }
                        if($itemQty > ((int)$item->getProduct()->getStockItem()->getQty() + $oldItemQty)) {
                            Mage::throwException(
                                Mage::helper('adminhtml')->__('The quantity requested for "%s" is not available', $item->getProduct()->getName())
                            );
                            return false;
                        }
                        $itemQty    = $itemQty > 0 ? $itemQty : 1;
                        if (isset($info['custom_price'])) {
                            $itemPrice  = $this->_parseCustomPrice($info['custom_price']);
                        } else {
                            $itemPrice = null;
                        }
                        $noDiscount = !isset($info['use_discount']);

                        if (empty($info['action']) || !empty($info['configured'])) {
                            $item->setQty($itemQty);
                            $item->setCustomPrice($itemPrice);
                            $item->setOriginalCustomPrice($itemPrice);
                            $item->setNoDiscount($noDiscount);
                            $item->getProduct()->setIsSuperMode(true);
                            $item->getProduct()->unsSkipCheckRequiredOption();
                            $item->checkData();
                        } else {
                            $this->moveQuoteItem($item->getId(), $info['action'], $itemQty);
                        }
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $this->recollectCart();
                throw $e;
            } catch (Exception $e) {
                Mage::logException($e);
            }
            $this->recollectCart();
        }
        return $this;
    }

    /**
     * Add product to current order quote
     * $product can be either product id or product model
     * $config can be either buyRequest config, or just qty
     *
     * @param   int|Mage_Catalog_Model_Product $product
     * @param   float|array|Varien_Object $config
     * @return  Mage_Adminhtml_Model_Sales_Order_Create
     */
    public function addProduct($product, $config = 1)
    {
        if (!is_array($config) && !($config instanceof Varien_Object)) {
            $config = array('qty' => $config);
        }
        $config = new Varien_Object($config);

        if (!($product instanceof Mage_Catalog_Model_Product)) {
            $productId = $product;
            $product = Mage::getModel('catalog/product')
                ->setStore($this->getSession()->getStore())
                ->setStoreId($this->getSession()->getStoreId())
                ->load($product);
            if (!$product->getId()) {
                Mage::throwException(
                    Mage::helper('adminhtml')->__('Failed to add a product to cart by id "%s".', $productId)
                );
            }
        }

        $stockItem = $product->getStockItem();
        if ($stockItem && $stockItem->getIsQtyDecimal()) {
            $product->setIsQtyDecimal(1);
        }

        $oldItemQty = 0;
        if($this->getSession()->getOrder()) {
            $oldOrderItems = $this->getSession()->getOrder()->getItemsCollection();
            foreach ($oldOrderItems as $oldItem){
                if($oldItem->product_id == $product->getId()) {
                    $oldItemQty = (int)$oldItem->getQtyOrdered();
                }
            }
        }

        if($config->getQty() > ((int) $stockItem->getQty() + $oldItemQty)) {
            Mage::throwException(
                Mage::helper('adminhtml')->__('The quantity requested for "%s" is not available', $product->getName())
            );
            return false;
        }
        $product->setCartQty($config->getQty());
        $item = $this->getQuote()->addProductAdvanced(
            $product,
            $config,
            Mage_Catalog_Model_Product_Type_Abstract::PROCESS_MODE_FULL
        );
        if (is_string($item)) {
            if ($product->getTypeId() != Mage_Catalog_Model_Product_Type_Grouped::TYPE_CODE) {
                $item = $this->getQuote()->addProductAdvanced(
                    $product,
                    $config,
                    Mage_Catalog_Model_Product_Type_Abstract::PROCESS_MODE_LITE
                );
            }
            if (is_string($item)) {
                Mage::throwException($item);
            }
        }
        $item->checkData();

        $this->setRecollect(true);
        return $this;
    }
}