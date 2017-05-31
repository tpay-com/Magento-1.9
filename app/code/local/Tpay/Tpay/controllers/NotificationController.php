<?php

require(Mage::getBaseDir('lib') . '/tpay/_class_tpay/PaymentBasic.php');

class Tpay_Tpay_NotificationController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $order = Mage::getModel('sales/order');
        $order_id = base64_decode($this->getRequest()->getPost('tr_crc'));
        $order->loadByIncrementId($order_id);
        $methodInstance = $order->getPayment()->getMethodInstance();
        $config = $methodInstance->getBasicConfigData();
        $CardsApi = new \tpay\PaymentBasic((int)$config['id'], $config['kodp']);
        $params = $CardsApi->checkPayment();
        $Order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        $amount = round($Order->getBaseGrandTotal(), 2);
        $information = ($params['test_mode'] === 1) ? 'Paid amount ' . $params['tr_paid'] . '-<b>TEST MODE</b>'
            : 'Paid amount ' . $params['tr_paid'];

        if ($params['tr_status'] === 'TRUE' && $amount === (float)$params['tr_paid']) {
            if (!$order->getEmailSent) {
                $order->sendNewOrderEmail();
                $order->setEmailSent(true);
                $this->saveInvoice($order);
            }
            $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_PROCESSING,
                Mage::helper('tpay')->__('The payment has been accepted. ' . $information));
        } else {
            $order->cancel();
            $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED,
                Mage::helper('tpay')->__('The order has been canceled. ' . $information));
        }
        $this->directLinkTransact($order, $params['tr_id'], $params,
            Mage_Sales_Model_Order_Payment_Transaction::TYPE_PAYMENT, '');
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
