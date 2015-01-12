<?php


class EW_NativePasswords_Block_Adminhtml_Notification_Enabled extends Mage_Adminhtml_Block_Template
{
    const MESSAGE_SHOWN_CANARY_REGISTRY_KEY = 'ew_notifications_adminhtml_notifications_enabled_shown';

    /**
     * Should notification be shown?
     */
    public function isShow() {
        return !Mage::helper('ew_nativepasswords')->isEnabled();
    }
}