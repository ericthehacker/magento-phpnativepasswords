<?php


class EW_NativePasswords_Block_Adminhtml_Notification_Enabled extends Mage_Adminhtml_Block_Template
{
    /**
     * Should notification be shown?
     */
    public function isShow() {
        return !Mage::helper('ew_nativepasswords')->isEnabled();
    }
}