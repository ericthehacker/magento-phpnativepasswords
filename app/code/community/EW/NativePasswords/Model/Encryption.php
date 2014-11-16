<?php

// make compat library available for 5.3.7 <= PHP version < 5.5
require_once(Mage::getBaseDir('lib') . '/password_compat/password.php');

//change class signature based on Magento edition
if(Mage::getEdition() == Mage::EDITION_ENTERPRISE) {
    abstract class EW_NativePasswords_Model_Encryption_Abstract extends Enterprise_Pci_Model_Encryption {}
} else {
    abstract class EW_NativePasswords_Model_Encryption_Abstract extends Mage_Core_Model_Encryption {}
}

class EW_NativePasswords_Model_Encryption extends EW_NativePasswords_Model_Encryption_Abstract {
    const COST_DEFAULT = 10;
    /** This salt is used when a constant salt is needed (eg, admin url secure key) */
    const CONSTANT_SALT = 'ew_nativepasswords_constant_salt';

    /**
     * Prevents infinite recursion when backwards compatibility is enabled
     * and merchant using enterprise edition.
     *
     * @var bool
     */
    private $_infiniteRecursionLock = false;

    /**
     * Convenience method to get helper instance.
     *
     * @return EW_NativePasswords_Helper_Data
     */
    protected function _getHelper() {
        return Mage::helper('ew_nativepasswords');
    }

    /**
     * Get password hash algorithm
     *
     * @return int
     */
    protected function _getHashAlgorithm() {
        return PASSWORD_DEFAULT; //@todo: system config to determine this?
    }

    /**
     * Get encryption cost
     *
     * @return int
     */
    protected function _getCost() {
        $value = $this->_getHelper()->getConfiguredCost();

        if($this->_getHelper()->validateCost($value)) {
            return $value;
        }

        throw new EW_NativePasswords_Exception_InvalidCostException(
            $this->_getHelper()->__(
                'Configured cost %d is invalid. Cost must be in interval [%d,%d].',
                $value,
                EW_NativePasswords_Helper_Data::COST_MIN,
                EW_NativePasswords_Helper_Data::COST_MAX
            )
        );
    }

    /**
     * Auto generate salt even if salt provided?
     *
     * @return bool
     */
    protected function _autoGenerateSalt() {
        return $this->_getHelper()->forceNativeSalt();
    }

    /**
     * Convenience method to assemble options array
     *
     * @return array
     */
    public function getOptions() {
        $options = array(
            'cost' => $this->_getCost()
        );

        //don't know if salt available, so can't configure in options array yet.

        return $options;
    }

    /**
     * Hash using fast (unsecure for passwords) algorithm.
     * Should only be used for non-mission-critical applications.
     *
     * @param string $value
     * @param bool $salt
     * @return string
     */
    public function fastHash($value, $salt = false) {
        return parent::getHash($value, $salt);
    }

    /**
     * Generate a [salted] hash using native password methods,
     * in enabled.
     *
     * $salt can be:
     * false - a random will be generated
     * integer - a random with specified length will be generated
     * string
     *
     * @param string $password
     * @param mixed $salt
     * @return string
     */
    public function getHash($password, $salt = false)
    {
        if(!$this->_getHelper()->isEnabled()) { //bail if not enabled
            return parent::getHash($password, $salt);
        }

        $options = $this->getOptions();

        if(!$salt) { // calling method intended for no salt to apply -- don't add one.
            $options['salt'] = self::CONSTANT_SALT;
        } else if(!$this->_autoGenerateSalt()) { //some salt was supplied, and config allows
            $options['salt'] = $salt;
        } // else, ignore supplied salt and auto gen using native methods

        return password_hash($password, $this->_getHashAlgorithm(), $options);
    }

    /**
     * Validate hash against hashing method (with or without salt)
     *
     * @param string $password
     * @param string $hash
     * @return bool
     * @throws Exception
     */
    public function validateHash($password, $hash)
    {
        if(!$this->_getHelper()->isEnabled()) { //bail if not enabled
            return parent::validateHash($password, $hash);
        }

        $valid = password_verify($password, $hash);

        //if backwards compatibility enabled, allow parent to also verify hash
        if($this->_getHelper()->allowBackwardsCompatibleVerification()) {
            $this->_infiniteRecursionLock = true;
            $valid = $valid || parent::validateHash($password, $hash);
            $this->_infiniteRecursionLock = false; //reset lock
        }

        return $valid;
    }

    /**
     * Enterprise compatibility method.
     *
     * @param $password
     * @param $hash
     * @param $version -- can't use enterprise constant for community compatibility
     * @return bool
     */
    public function validateHashByVersion($password, $hash, $version = 1) {
        if(!$this->_getHelper()->isEnabled()) { //bail if not enabled
            return parent::validateHashByVersion($password, $hash, $version);
        }

        if($this->_infiniteRecursionLock) { //prevent infinite recursion and call parent instead
            return parent::validateHashByVersion($password, $hash, $version);
        }

        return $this->validateHash($password, $hash);
    }
}