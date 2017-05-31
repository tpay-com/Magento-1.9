<?php

class Tpay_TpayCards_Model_HashTypes
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'sha1', 'label' => Mage::helper('tpayCards')->__('sha1')),
            array('value' => 'sha256', 'label' => Mage::helper('tpayCards')->__('sha256')),
            array('value' => 'sha512', 'label' => Mage::helper('tpayCards')->__('sha512')),
            array('value' => 'ripemd160', 'label' => Mage::helper('tpayCards')->__('ripemd160')),
            array('value' => 'ripemd320', 'label' => Mage::helper('tpayCards')->__('ripemd320')),
            array('value' => 'md5', 'label' => Mage::helper('tpayCards')->__('md5')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(

            'sha1'      => Mage::helper('tpayCards')->__('sha1'),
            'sha256'    => Mage::helper('tpayCards')->__('sha256'),
            'sha512'    => Mage::helper('tpayCards')->__('sha512'),
            'ripemd160' => Mage::helper('tpayCards')->__('ripemd160'),
            'ripemd320' => Mage::helper('tpayCards')->__('ripemd320'),
            'md5'       => Mage::helper('tpayCards')->__('md5'),
        );
    }
}
