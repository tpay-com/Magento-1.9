<?php

require(Mage::getBaseDir('lib') . '/tpay/_class_tpay/PaymentCard.php');

class Tpay_TpayCards_NotificationController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $order = Mage::getModel('sales/order');
        $order_id = base64_decode($this->getRequest()->getPost('order_id'));
        $order->loadByIncrementId($order_id);
        $methodInstance = $order->getPayment()->getMethodInstance();
        $config = $methodInstance->getApiConfigData();
        $CardsApi = new \tpay\PaymentCard($config['key'], $config['pass'], $config['code'], 'sha1', 'tpay');
        $params = $CardsApi->handleNotification();
        /** @var Mage_Sales_Model_Order $Order */
        $Order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        $amount = round($Order->getGrandTotal(), 2);
        $currency = \tpay\Validate::validateCardCurrency($Order->getOrderCurrencyCode());
        $CardsApi->validateSign($params['sign'],
            $params['sale_auth'], $params['card'], (float)$amount, $params['date'], 'correct', $currency,
            isset($params['test_mode']) ? '1' : '', $params['order_id']);

        $information = ($params['test_mode'] === '1') ? '-<b>TEST MODE</b>' : ' ';

        if (!$order->getEmailSent) {
            $order->sendNewOrderEmail();
            $order->setEmailSent(true);
            $this->saveInvoice($order);
        }
        $this->directLinkTransact($order, $params['sale_auth'], $params,
            Mage_Sales_Model_Order_Payment_Transaction::TYPE_PAYMENT, '');
        $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_PROCESSING,
            Mage::helper('tpayCards')->__('The payment has been accepted. ' . $information));
        $order->save();
    }

    protected function saveInvoice(Mage_Sales_Model_Order $order)
    {

        try {
            if (!$order->canInvoice()) {
                Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
            }

            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

            if (!$invoice->getTotalQty()) {
                Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
            }

            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
            $invoice->register();
            if (!$invoice->getEmailSent) {
                $invoice->sendEmail();
                $invoice->setEmailSent(true);
            }
            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder());

            $transactionSave->save();
        } catch (Mage_Core_Exception $e) {

        }
    }

    protected function directLinkTransact(
        $order,
        $transactionID,
        $arrInformation = array(),
        $typename,
        $comment,
        $closed = 1
    ) {
        $payment = $order->getPayment();
        $payment->setTransactionId($transactionID);
        $transaction = $payment->addTransaction($typename, null, false, $comment);
        $transaction->setIsClosed($closed);
        $transaction->setAdditionalInformation(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,
            $arrInformation);
        $transaction->save();
        $order->save();
        return $this;
    }

}

?>
