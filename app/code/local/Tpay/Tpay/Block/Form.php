<?php

class Tpay_Tpay_Block_Form extends Mage_Payment_Block_Form {

  protected function _construct() {
    parent::_construct();
    $this->setTemplate('tpay/tpay/form.phtml');
  }

  public function getPaymentImageSrc() {
    return 'https://tpay.com/img/banners/tpay-full-color-449x162.png';
  }
}