<?php

class EW_NativePasswords_Model_Observer
{
    const ADMIN_NOTIFICATIONS_BLOCK_NAME = 'notifications';

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

    /**
     * If template symlinks are disabled and this module is installed but not enabled
     * then add session notification in addition to template notification,
     * as template notification may not be seen if module installed via modman.
     * Observes: core_block_abstract_to_html_after
     *
     * @param Varien_Event_Observer $observer
     */
    public function addNotEnabledWarning(Varien_Event_Observer $observer) {
        /* @var $block Mage_Core_Block_Abstract */
        $block = $observer->getBlock();
        /* @var $helper EW_NativePasswords_Helper_Data */
        $helper = Mage::helper('ew_nativepasswords');

        if($block->getNameInLayout() != self::ADMIN_NOTIFICATIONS_BLOCK_NAME) {
            return; //not interested in this block
        }

        if($helper->isEnabled()) {
            return; //module functionality enabled -- no concerns.
        }

        if(Mage::registry(EW_NativePasswords_Block_Adminhtml_Notification_Enabled::MESSAGE_SHOWN_CANARY_REGISTRY_KEY)) {
            return; //message successfully shown via template notification
        }

        //module is installed (or this method wouldn't exist), but functionality is disabled,
        //and template symlinks are disabled.
        //fall back to session notification.

        $message = $helper->getNotEnabledMessage();

        if(!Mage::getStoreConfig(Mage_Core_Block_Template::XML_PATH_TEMPLATE_ALLOW_SYMLINK)) {
            //add message about template symlinks
            $message .= "<br /><br />" . //line break
                $helper->__(
                'Additionally, template symlinks are disabled. It is required to enable these for modules installed via modman. ' .
                'Please visit the Developer -> Template Settings section of '.
                '<a href="%s">system configuration</a> and set Allow Symlinks to Yes.',
                    Mage::getUrl('adminhtml/system_config/edit', array('section'=>'dev'))
            );
        }

        Mage::getSingleton('adminhtml/session')->addWarning($message);
    }
}