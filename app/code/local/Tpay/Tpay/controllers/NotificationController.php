<?php

class Tpay_Tpay_NotificationController extends Mage_Core_Controller_Front_Action {

	public function indexAction() {
		$order = Mage::getModel('sales/order');
		$order_id = base64_decode($this->getRequest()->getPost('tr_crc'));
		$order->loadByIncrementId($order_id);
		$methodInstance = $order->getPayment()->getMethodInstance();

		$ip_table=array(
		'195.149.229.109',
		'148.251.96.163',
		'178.32.201.77',
		'46.248.167.59',
		'46.29.19.106'
		);

			if(empty($_POST['tr_id']) || !isset($_POST['tr_id'])  || !in_array($_SERVER['REMOTE_ADDR'], $ip_table))
				exit();

			$status_transakcji = $_POST ['tr_status'];
			$id_transakcji = $_POST ['tr_id'];
			$kwota_transakcji = $_POST ['tr_amount'];
			$kwota_zaplacona = $_POST ['tr_paid'];
			$blad = $_POST ['tr_error'];
			$data_transakcji = $_POST ['tr_date'];
			$opis_transackji = $_POST ['tr_desc'];
			$ciag_pomocniczy = $_POST ['tr_crc'];
			$email_klienta = $_POST ['tr_email'];
			$data['checksum'] =$_POST['md5sum'];
			$hash_type = 'md5';



		$information=($_POST['test_mode']=='1')? " -". $id_transakcji. "-<b>TEST MODE</b> (".$kwota_zaplacona."<b> PLN</b>)" : " ". $id_transakcji.' - ('.$kwota_zaplacona.'<b> PLN</b>)';
		// sprawdzenie stanu transakcji

		$data['local_checksum'] = hash($hash_type,$methodInstance->getConfigData('id') . $id_transakcji.$kwota_transakcji.$ciag_pomocniczy . $methodInstance->getConfigData('kodp'));

		if ( $data['checksum']=== $data['local_checksum']) {
			if ($status_transakcji == 'TRUE' ) {
				if (!$order->getEmailSent) {
					$order->sendNewOrderEmail();
					$order->setEmailSent(true);
					$this->saveInvoice($order);
				}
				$order->addStatusToHistory(Mage_Sales_Model_Order::STATE_PROCESSING, Mage::helper('tpay')->__('The payment has been accepted'.$information));
			} else {
				$order->cancel();
				$order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED, Mage::helper('tpay')->__('The order has been canceled'.$information));
			}
			$order->save();
		}

		echo 'TRUE'; // odpowiedz dla serwera o odebraniu danych

	}

	protected function saveInvoice(Mage_Sales_Model_Order $order) {

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

}

?>