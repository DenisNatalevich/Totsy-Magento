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

class Harapartners_Service_Model_Rewrite_Reward_Observer extends Enterprise_Reward_Model_Observer
{
    /**
     * Update reward points for customer, send notification
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function saveRewardPoints($observer)
    {
        if (!Mage::helper('enterprise_reward')->isEnabled()) {
            return;
        }

        $request = $observer->getEvent()->getRequest();
        $customer = $observer->getEvent()->getCustomer();
        $data = $request->getPost('reward');
        $commentInput = $data['comment'];
        $user = Mage::getSingleton('admin/session');
        $userId = $user->getUser()->getUserId();
        $userFirstname = $user->getUser()->getFirstname();
        $commentInput = $commentInput.' by '.$userFirstname.' (id:'.$userId.')';
        $data['comment'] = $commentInput;
        if ($data) {
            if (!isset($data['store_id'])) {
                if ($customer->getStoreId() == 0) {
                    $data['store_id'] = Mage::app()->getDefaultStoreView()->getWebsiteId();
                } else {
                    $data['store_id'] = $customer->getStoreId();
                }
            }
            $reward = Mage::getModel('enterprise_reward/reward')
                ->setCustomer($customer)
                ->setWebsiteId(Mage::app()->getStore($data['store_id'])->getWebsiteId())
                ->loadByCustomer();
            if (!empty($data['points_delta'])) {
                $reward->addData($data)
                    ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_ADMIN)
                    ->setActionEntity($customer)
                    ->updateRewardPoints();
            } else {
                $reward->save();
            }
        }
        return $this;
    }
    
    public function checkRates(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('enterprise_reward')->isEnabledOnFront()) {
            return $this;
        }

        $groupId    = $observer->getEvent()->getCustomerSession()->getCustomerGroupId();
        $websiteId  = Mage::app()->getStore()->getWebsiteId();

        $rate = Mage::getModel('enterprise_reward/reward_rate');

        $hasRates = $rate->fetch(
            $groupId, $websiteId, Enterprise_Reward_Model_Reward_Rate::RATE_EXCHANGE_DIRECTION_TO_CURRENCY
        )->getId() /*&&
            $rate->reset()->fetch(
                $groupId,
                $websiteId,
                Enterprise_Reward_Model_Reward_Rate::RATE_EXCHANGE_DIRECTION_TO_POINTS
            )->getId()*/;

        Mage::helper('enterprise_reward')->setHasRates($hasRates);

        return $this;
    }
    
    /**
     * Update points balance after customer registered by invitation
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    public function invitationToCustomer($observer)
    {
        /* @var $invitation Enterprise_Invitation_Model_Invitation */
        $invitation = $observer->getEvent()->getInvitation();
        $websiteId = Mage::app()->getStore($invitation->getStoreId())->getWebsiteId();
        if (!Mage::helper('enterprise_reward')->isEnabledOnFront($websiteId)) {
            return $this;
        }
        //The comment for import only that invitation wont interfere reward
        /*if ($invitation->getCustomerId() && $invitation->getReferralId()) {
            $reward = Mage::getModel('enterprise_reward/reward')
                ->setCustomerId($invitation->getCustomerId())
                ->setWebsiteId($websiteId)
                ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_INVITATION_CUSTOMER)
                ->setActionEntity($invitation)
                ->updateRewardPoints();
                
            //Harapartners,Li Lu 
            $emailTemplate = Mage::getModel( 'core/email_template' );//->loadByCode( Mage::getStoreConfig( Enterprise_Reward_Model_Reward::XML_PATH_INVITER_EMAIL_INVITEE_REGISTERED ) );
            $inviter = Mage::getModel( 'customer/customer' )->load( $invitation->getCustomerId() );
            $emailTemplate->sendTransactional( Mage::getStoreConfig( Enterprise_Reward_Model_Reward::XML_PATH_INVITER_EMAIL_INVITEE_REGISTERED ), 'support', $inviter->getEmail(), $inviter->getName() );
            //Harapartners,Li Lu
        }*/

        
        /*
        $customer = $observer->getEvent()->getCustomer();
        $customerOrigData = $customer->getOrigData();
        TODO: if new 
        send email
        */
        
        return $this;
    }
    
    
    /**
     * Update inviter points balance after referral's order completed
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Reward_Model_Observer
     */
    protected function _invitationToOrder($observer)
    {
        if (Mage::helper('Core')->isModuleEnabled('Enterprise_Invitation')) {
            $invoice = $observer->getEvent()->getInvoice();
            /* @var $invoice Mage_Sales_Model_Order_Invoice */
            $order = $invoice->getOrder();
            /* @var $order Mage_Sales_Model_Order */
            if ($order->getBaseTotalDue() > 0) {
                return $this;
            }
            $invitation = Mage::getModel('enterprise_invitation/invitation')
                ->load($order->getCustomerId(), 'referral_id');
            if (!$invitation->getId() || !$invitation->getCustomerId()) {
                return $this;
            }
            $reward = Mage::getModel('enterprise_reward/reward')
                ->setActionEntity($invitation)
                ->setCustomerId($invitation->getCustomerId())
                ->setStore($order->getStoreId())
                ->setAction(Enterprise_Reward_Model_Reward::REWARD_ACTION_INVITATION_ORDER)
                ->updateRewardPoints();
                
            //Harapartners, Li Lu    
            $emailTemplate = Mage::getModel( 'core/email_template' );//->loadByCode( Mage::getStoreConfig( Enterprise_Reward_Model_Reward::XML_PATH_INVITER_EMAIL_INVITEE_FIRST_PURCHASE ) );
            $inviter = Mage::getModel( 'customer/customer' )->load( $invitation->getCustomerId() );
            $emailTemplate->sendTransactional( Mage::getStoreConfig( Enterprise_Reward_Model_Reward::XML_PATH_INVITER_EMAIL_INVITEE_FIRST_PURCHASE ), 'support', $inviter->getEmail(), $inviter->getName() );
            //Harapartners, Li Lu 
        }
        
        
        /*
        //TODO: send email to inviter
        */
        
        return $this;
    }
    

}
