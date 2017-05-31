<?php

class Tpay_TpayCards_Block_Form extends Mage_Payment_Block_Form {

  protected function _construct() {
    parent::_construct();
    $this->setTemplate('tpay/tpay/CardsForm.phtml');
  }

  public function getPaymentImageSrc() {
    return 'https://tpay.com/img/banners/tpay-full-300x69.png';
  }
}
