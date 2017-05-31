<?php

require (Mage::getBaseDir('lib') . '/tpay/_class_tpay/CardApi.php');
require (Mage::getBaseDir('lib') . '/tpay/_class_tpay/Util.php');

class Tpay_TpayCards_Block_Redirect extends Mage_Core_Block_Template
{

    public function createApiTransaction()
    {
        $methodInstance = $this->_getOrder()->getPayment()->getMethodInstance();
        $config = $methodInstance->getApiConfigData();
        $params = $methodInstance->getRedirectionFormData();
        $CardsApi = new \tpay\CardAPI($config['key'], $config['pass'], $config['code'], $config['hash']);
        return $CardsApi->registerSale($params['imie'] . ' ' . $params['nazwisko'], $params['email'], $params['opis'],
            $params['kwota'], $params['currency'], $params['crc'], true, $params['jezyk'], true, $params['pow_url'],
            $params['pow_url_blad']);
    }

    protected function _getOrder()
    {
        if ($this->getOrder()) {
            return $this->getOrder();
        }
        if ($orderIncrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId()) {
            return Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        }
    }

    public function getForm()
    {
        $sale = $this->createApiTransaction();
        $methodInstance = $this->_getOrder()->getPayment()->getMethodInstance();

        $form = new Varien_Data_Form;
        $form->
        setId('tpay_tpayCards_redirection_form')->
        setName('tpay_tpayCards_redirection_form')->
        setAction($methodInstance->getRedirectUrl())->
        setMethod('post')->
        setUseContainer(true);

        $form->addField('sale_auth', 'hidden', array('name' => 'sale_auth', 'value' => $sale['sale_auth']));

        Mage::app()->getStore()->setCurrentCurrencyCode(Mage::app()->getStore()->getBaseCurrencyCode());
        return $form;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('tpay/tpay/CardsRedirect.phtml');
    }
}
