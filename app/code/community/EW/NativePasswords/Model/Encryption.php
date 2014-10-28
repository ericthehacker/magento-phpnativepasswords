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
        return $this->_getHelper()->getConfiguredCost();
    }

    /**
     * Auto generate salt even if salt provided?
     *
     * @return bool
     */
    protected function _autoGenerateSalt() {
        return true; //@todo: system config
    }

    /**
     * Convenience method to assemble options array
     *
     * @return array
     */
    protected function _getOptions() {
        $options = array(
            'cost' => $this->_getCost()
        );

        //don't know if salt available, so can't configure in options array yet.

        return $options;
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

        $options = $this->_getOptions();

        if(!$this->_autoGenerateSalt() && !empty($salt)) {
            $options['salt'] = $salt;
        }

        return password_hash($password, $this->_getHashAlgorithm(), $options);
    }

    /**
     * Hash a string
     *
     * @param string $data
     * @param $version -- can't use constant for community compatibility
     * @return string
     */
    public function hash($data, $version = 1)
    {
        if(!$this->_getHelper()->isEnabled()) { //bail if not enabled
            return parent::hash($data);
        }

        return parent::hash($data); //@todo: does this method need to be overridden at all?
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
            $this->_infiniteRecursionLock = false; //reset lock
            return parent::validateHashByVersion($password, $hash, $version);
        }

        return $this->validateHash($password, $hash);
    }
}