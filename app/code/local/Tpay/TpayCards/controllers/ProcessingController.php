<?php

class Tpay_TpayCards_ProcessingController extends Mage_Core_Controller_Front_Action {

  private function _getCheckout() {
	 
    return Mage::getSingleton('checkout/session');
  }

  public function redirectAction() {
    $this->_getCheckout()->setTpayQuoteId($this->_getCheckout()->getQuoteId());
    $this->getResponse()->setBody($this->getLayout()->createBlock('tpayCards/redirect')->toHtml());
    $this->_getCheckout()->unsQuoteId();
    $this->_getCheckout()->unsRedirectUrl();
  }

  public function statusAction() {
     $session = $this->_getCheckout();
     $order = Mage::getModel('sales/order');
     $order->loadByIncrementId($session->getLastRealOrderId());
     
    if(!$order->getStatus() =='processing')
      return $this->norouteAction();
    $this->_redirect('tpayCards/processing/'.($order->getStatus() =='processing' ? 'success' : 'cancel'));
  }

  public function successAction() {
    $this->_getCheckout()->setQuoteId($this->_getCheckout()->getTpayQuoteId(TRUE));
    $this->_getCheckout()->getQuote()->setIsActive(FALSE)->save();
    $this->_redirect('checkout/onepage/success');
  }

  public function cancelAction() {
    $this->_getCheckout()->setQuoteId($this->_getCheckout()->getTpayQuoteId(TRUE));
    $this->_getCheckout()->addError(Mage::helper('tpayCards')->__('The order has been canceled.'));
    $this->_redirect('checkout/cart');
  }
}
