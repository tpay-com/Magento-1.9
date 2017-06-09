<?php

class Tpay_Tpay_Block_Form extends Mage_Payment_Block_Form {

  protected function _construct() {
    parent::_construct();
    $this->setTemplate('tpay/tpay/form.phtml');
    $this->setMethodTitle($this->__('Zapłać wygodnie online z '));
    $this->setMethodLabelAfterHtml('<img src="https://tpay.com/img/banners/tpay-przezroczyste-logo-85x24.png" />');
  }

  public function getPaymentImageSrc() {
    return 'https://tpay.com/img/banners/tpay-full-color-449x162.png';
  }
}
