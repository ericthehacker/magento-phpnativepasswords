<?php

class EW_NativePasswords_Helper_Data extends Mage_Core_Helper_Abstract
{
    const MINIMUM_REQUIRED_PHP_VERSION = '5.3.7';
    const NATIVE_PHP_VERSION = '5.5.0';
    const CONFIG_PATH_ENABLED = 'customer/password/native_passwords_enabled';
    const CONFIG_PATH_BACKWARDS_COMPATIBLE = 'customer/password/native_passwords_backwards_compatible';

    /**
     * Determine if functionality *should* be used
     * (enabled in system configuration) and
     * *can* be used (PHP version).
     *
     * @return bool
     */
    public function isEnabled() {
        if(!(bool)Mage::getStoreConfig(self::CONFIG_PATH_ENABLED)) {
            return false;
        }

        if (version_compare(phpversion(), self::MINIMUM_REQUIRED_PHP_VERSION, '<')===true) {
            $exceptionMessage = sprintf(
                'Native passwords are enabled in system configuration, but PHP version is incompatible. ' .
                'Reminder: %s <= PHP version < %s can use compatibility library. ' .
                'PHP version >= %s use native functionality. ' .
                'Found version: %s.',
                self::MINIMUM_REQUIRED_PHP_VERSION,
                self::NATIVE_PHP_VERSION,
                self::NATIVE_PHP_VERSION,
                phpversion()
            );

            Mage::log($exceptionMessage, Zend_Log::WARN);

            trigger_error($exceptionMessage, E_WARNING);

            return false;
        }

        return true;
    }

    /**
     * Should password hashes created by Magento
     * still be used for verification?
     *
     * @return bool
     */
    public function allowBackwardsCompatibleVerification() {
        return (bool)Mage::getStoreConfig(self::CONFIG_PATH_BACKWARDS_COMPATIBLE);
    }
}