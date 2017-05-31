<?php

require_once (Mage::getBaseDir('lib') . '/tpay/_class_tpay/Validate.php');

class Tpay_TpayCards_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract
{

    protected $_code = 'tpayCards';
    protected $_formBlockType = 'tpayCards/form';
    protected $_canUseInternal = false;
    protected $_canUseCheckout = false;
    protected $_order;

    public function __construct()
    {
        if (!($this->getConfigData('MIDType')) && Mage::app()->getStore()->getCurrentCurrencyCode() === 'PLN') {
            $this->_canUseInternal = false;
            $this->_canUseCheckout = false;
        } else {
            $this->_canUseInternal = true;
            $this->_canUseCheckout = true;
        }
    }

    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('tpayCards/processing/redirect');
    }

    public function getRedirectUrl()
    {
        return $this->getConfigData('redirect_url');
    }

    public function getAuthIPUrl()
    {
        return $this->getConfigData('tran_ip');
    }

    public function getApiConfigData()
    {
        return array(
            'key'  => $this->getConfigData('apiKey'),
            'pass' => $this->getConfigData('apiPass'),
            'code' => $this->getConfigData('verifCode'),
            'hash' => $this->getConfigData('hashType'),
        );
    }

    public function getRedirectionFormData()
    {
        $billing = $this->getOrder()->getBillingAddress();
        $order_id = $this->getOrder()->getRealOrderId();
        $crc = base64_encode($order_id);
        $amount = round($this->getOrder()->getGrandTotal(), 2);
        $currency = \tpay\Validate::validateCardCurrency(Mage::app()->getStore()->getCurrentCurrencyCode());

        return array(
            'kwota'        => $amount,
            'currency'     => $currency,
            'opis'         => Mage::helper('tpayCards')->__('Zamówienie: %s', $this->getOrder()->getRealOrderId()),
            'email'        => $billing->getEmail() ? $billing->getEmail() : $this->getOrder()->getCustomerEmail(),
            'imie'         => $billing->getFirstname(),
            'nazwisko'     => $billing->getLastname(),
            'crc'          => $crc,
            'pow_url'      => Mage::getUrl('checkout/onepage/success/'),
            'pow_url_blad' => Mage::getUrl('customer/account/'),
            'wyn_url'      => Mage::getUrl('tpayCards/notification'),
            'jezyk'        => $billing->getCountryModel()->getIso2Code(),
        );
    }

    public function getOrder()
    {
        if (!$this->_order) {
            $this->_order = $this->getInfoInstance()->getOrder();
        }
        return $this->_order;
    }
}
