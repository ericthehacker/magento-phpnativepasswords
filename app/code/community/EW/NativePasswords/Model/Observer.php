<?php

class EW_NativePasswords_Model_Observer
{
    /**
     * Validate environment and throw exception if not compatible
     *
     * @throws EW_NativePasswords_Exception_IncompatibleEnvironmentException
     */
    protected function _validateEnvironment() {
        /* @var $helper EW_NativePasswords_Helper_Data */
        $helper = Mage::helper('ew_nativepasswords');

        if(!$helper->isEnvironmentCompatible()) {
            throw new EW_NativePasswords_Exception_IncompatibleEnvironmentException(
                $helper->__(
                    'Native passwords are enabled in system configuration, but PHP version is incompatible. ' .
                    'Reminder: %s <= PHP version < %s can use compatibility library. ' .
                    'PHP version >= %s use native functionality. ' .
                    'Found version: %s.',
                    EW_NativePasswords_Helper_Data::MINIMUM_REQUIRED_PHP_VERSION,
                    EW_NativePasswords_Helper_Data::NATIVE_PHP_VERSION,
                    EW_NativePasswords_Helper_Data::NATIVE_PHP_VERSION,
                    phpversion()
                )
            );
        }
    }

    /**
     * Validate cost and throw exception if invalid
     *
     * @param $cost
     * @throws EW_NativePasswords_Exception_IncompatibleEnvironmentException
     */
    protected function _validateCost($cost) {
        /* @var $helper EW_NativePasswords_Helper_Data */
        $helper = Mage::helper('ew_nativepasswords');

        if(!$helper->validateCost($cost)) {
            throw new EW_NativePasswords_Exception_InvalidCostException(
                $helper->__(
                    'Invalid cost %d. Cost must be in interval [%d,%d].',
                    $cost,
                    EW_NativePasswords_Helper_Data::COST_MIN,
                    EW_NativePasswords_Helper_Data::COST_MAX
                )
            );
        }

        $optimalCost = $helper->getOptimalCost();

        if($optimalCost != $cost) {
            Mage::getSingleton('adminhtml/session')->addNotice(
                $helper->__(
                    'Native passwords cost updated to %d, but optimal cost computed to be %d.',
                    $cost,
                    $optimalCost
                )
            );
        }
    }

    /**
     * Ensure environment can handle native password hashing
     * when enabling via system config.
     * Observes: model_config_data_save_before
     *
     * @param Varien_Event_Observer $observer
     */
    public function validateEnvironment(Varien_Event_Observer $observer) {
        $groups = $observer->getObject()->getGroups();

        $enabled = false;

        if(isset($groups['password'])
            && isset($groups['password']['fields'])
            && isset($groups['password']['fields']['native_passwords_enabled'])
            && isset($groups['password']['fields']['native_passwords_enabled']['value'])) {
            $enabled = (bool)$groups['password']['fields']['native_passwords_enabled']['value'];
        }

        if(!$enabled) {
            return; //nothing to do here
        }

        /* @var $helper EW_NativePasswords_Helper_Data */
        $helper = Mage::helper('ew_nativepasswords');

        $oldCost = $helper->getConfiguredCost();
        $cost = intval($groups['password']['fields']['native_passwords_cost']['value']);

        $this->_validateEnvironment();

        if($oldCost != $cost) {
            $this->_validateCost($cost);
        }
    }
}