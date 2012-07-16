<?php 
class TinyBrick_OrderEdit_Model_Edit_Updater_Type_Payment extends TinyBrick_OrderEdit_Model_Edit_Updater_Type_Abstract
{
    public function edit(TinyBrick_OrderEdit_Model_Order $order, $data = array())
    {
        try {
            $customerId = $order->getCustomerId();
            $billing = $order->getBillingAddress();
            $data = $this->cleanPaymentData($data);
            $payment = new Varien_Object($data);
            #Check if there is already a cybersource profile if yes, dont create a new one
            $profile = Mage::getModel('paymentfactory/profile');
            $profile->loadByCcNumberWithId($payment->getData('cc_number').$customerId.$payment->getCcExpYear().$payment->getCcExpMonth());
            if($profile && $profile->getId()) {
                return true;
            }
            Mage::getModel('paymentfactory/tokenize')->createProfile($payment, $billing, $customerId, $billing->getId());
            $this->replacePaymentInformation($order, $payment);
        } catch(Exception $e){
            $array['status'] = 'error';
            $array['msg'] = "Error updating payment informations : ".$e->getMessage();
            return false;
        }
        return true;
    }

    public function cleanPaymentData($datas) {
        $cleanDatas = null;
        foreach($datas as  $key => $data) {
            $key = str_replace('[','',$key);
            $key = str_replace(']','',$key);
            $key = str_replace('payment','',$key);
            $cleanDatas[$key] = $data;
        }
        return $cleanDatas;
    }

    public function replacePaymentInformation($order, $payment) {
        $paymentOrder = $order->getPayment();
        $paymentOrder->setData('cc_exp_month', $payment->getData('cc_exp_month'));
        $paymentOrder->setData('cc_last4', substr($payment->getCcNumber(), -4));
        $paymentOrder->setData('cc_type', $payment->getData('cc_type'));
        $paymentOrder->setData('cc_exp_year', $payment->getData('cc_exp_year'));
        $paymentOrder->setData('cc_trans_id', $payment->getData('cc_trans_id'));
        $paymentOrder->setData('cybersource_token', $payment->getData('cybersource_token'));
        $paymentOrder->setData('cybersource_subid', $payment->getData('cybersource_subid'));
        $paymentOrder->save();
    }
}