<?php

// make compat library available for 5.3.7 <= PHP version < 5.5
require_once(Mage::getBaseDir('lib') . '/password_compat/password.php');

class EW_NativePasswords_Model_Encryption extends Mage_Core_Model_Encryption
{
    /**
     * Convenience method to get helper instance.
     *
     * @return EW_NativePasswords_Helper_Data
     */
    protected function _getHelper() {
        return Mage::helper('ew_nativepasswords');
    }

    /**
     * Generate a [salted] hash.
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
    }

    /**
     * Hash a string
     *
     * @param string $data
     * @return string
     */
    public function hash($data)
    {
        if(!$this->_getHelper()->isEnabled()) { //bail if not enabled
            return parent::hash($data);
        }
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
            return parent::getHash($password, $hash);
        }
    }
}