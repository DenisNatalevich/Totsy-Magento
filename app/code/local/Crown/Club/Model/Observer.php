<?php
class Crown_Club_Model_Observer {

	/**
	 * Moves expired club members back to the non club members group.
	 * @param Mage_Cron_Model_Schedule $schedule
	 * @since 0.1.0
	 * @return void
	 */
	public function removeExpiredClubMembers($schedule) {
		$helper = Mage::helper('crownclub');
		if (!$helper->moduleSetupComplete()) return;

		$clubModel = Mage::getModel('crownclub/club');

		$expiredMembers = $clubModel->getExpiredMembersOutOfGracePeriod();

		foreach ($expiredMembers as $expiredMember) {
			$customer = Mage::getModel('customer/customer')->load($expiredMember->getId());
			$clubModel->removeClubMember($customer)->sendClubMembershipCancelledEmail($customer);
		}
	}

	/**
	 * Warns expired club members that their payment has failed and their subscription account
	 * will be cancelled once it exits the grace period.
	 * @param Mage_Cron_Model_Schedule $schedule
	 * @since 0.1.0
	 * @return void
	 */
	public function warnExpiredClubMembers($schedule) {
		$helper = Mage::helper('crownclub');
		if (!$helper->moduleSetupComplete()) return;

		$clubModel = Mage::getModel('crownclub/club');

		$expiredMembers = $clubModel->getExpiredMembersInGracePeriod();

		foreach ($expiredMembers as $expiredMember) {
			$customer = Mage::getModel('customer/customer')->load($expiredMember->getId());
			$clubModel->sendClubMembershipPaymentFailedEmail($customer);
		}
	}

    /**
     * Update points balance after order becomes completed
     *
     * @param Varien_Event_Observer $observer
     * @since 0.4.0
     * @return Crown_Club_Model_Observer
     */
    public function orderShipped($observer)
    {
        /* @var $shipment Mage_Sales_Model_Order_Shipment */
        $shipment = $observer->getEvent()->getShipment();

        /* @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order')->load($shipment->getOrderId());

        if ($order->getCustomerIsGuest()
            || !Mage::helper('enterprise_reward')->isEnabledOnFront($order->getStore()->getWebsiteId())
            || !$order->getData('customer_is_club_member')
        ){
            return $this;
        }

        if ($order->getCustomerId()) {
            /* @var $reward Enterprise_Reward_Model_Reward */
            $reward = Mage::getModel('enterprise_reward/reward')
                ->setActionEntity($order)
                ->setCustomerId($order->getCustomerId())
                ->setWebsiteId($order->getStore()->getWebsiteId())
                ->setAction(Crown_Club_Model_Rewards_Reward::REWARD_ACTION_CLUB)
                ->updateRewardPoints();
            if ($reward->getRewardPointsUpdated() && $reward->getPointsDelta()) {
                $order->addStatusHistoryComment(
                    Mage::helper('enterprise_reward')->__('Club customer earned %s for the order.', Mage::helper('enterprise_reward')->formatReward($reward->getPointsDelta()))
                )->save();
            }
        }
        return $this;
    }
}