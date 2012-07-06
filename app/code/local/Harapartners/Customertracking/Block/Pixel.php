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

class Harapartners_Customertracking_Block_Pixel
    extends Mage_Core_Block_Template
{
    public function _toHtml()
    {
        $affiliate = Mage::getSingleton('customer/session')->getAffiliate();
        $eventName = $this->getCurrentAffiliateEventName();
        if ($affiliate && $affiliate->getId() && !empty($eventName)) {
            return $this->renderView();
        } else {
            return '';
        }
    }

    public function getCurrentAffiliateEventName()
    {
        $pageMap = Mage::helper('affiliate')->getFormTrackingPageCodeArray();

        if(!($pageTag = Mage::app()->getFrontController()->getRequest()->getParam('pageTag'))) {
            $pageTag = strtolower(Mage::app()->getFrontController()
                ->getAction()
                ->getFullActionName());
        }

        return isset($pageMap[$pageTag]) ? $pageMap[$pageTag] : '';
    }

    public function getCurrentAffiliatePixel()
    {
        $htmlPixel = '';
        $affiliate = Mage::getSingleton('customer/session')->getAffiliate();
        if ($affiliate && $affiliate->getId()) {
            $trackingCodes = json_decode($affiliate->getTrackingCode(), true);
            if(!($pageTag = Mage::app()->getFrontController()->getRequest()->getParam('pageTag'))) {
                $pageTag = strtolower(Mage::app()->getFrontController()
                    ->getAction()
                    ->getFullActionName());
            }

            // additional logic to ensure the post-registration pixel fires
            // only once, by checking a tracking cookie
            $cookie = Mage::app()->getCookie();
            $key = Harapartners_Customertracking_Helper_Data::COOKIE_CUSTOMER_WELCOME;
            if ($cookie->get($key)) {
                $htmlPixel .= $trackingCodes[Harapartners_Affiliate_Helper_Data::PAGE_NAME_AFTER_CUSTOMER_REGISTER_SUCCESS];
                $cookie->delete($key);
            }

            if (isset($trackingCodes[$pageTag])) {
                $htmlPixel .= $trackingCodes[$pageTag];
            }
        }

        return $this->_templateReplace($htmlPixel);
    }

    protected function _templateReplace($html)
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        $order = Mage::getModel('sales/order')->load($orderId);

        return preg_replace_callback('/{{[\w.]+}}/', function($matches) use ($customer, $order) {
            $parts = explode('.', substr($matches[0], 2, -2));

            if (2 == count($parts)) {
                list($modelType, $field) = $parts;
                switch ($modelType) {
                    case 'customer':
                        return $customer->getData($field);
                        break;
                    case 'order':
                        return $order->getData($field);
                        break;
                }

                return $matches[0];
            }
        }, $html);
    }
}
