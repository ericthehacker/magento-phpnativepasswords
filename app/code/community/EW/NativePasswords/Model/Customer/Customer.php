<?php

class EW_NativePasswords_Model_Customer_Customer extends Mage_Customer_Model_Customer
{
    /**
     * Rehash password and save to db
     *
     * @param $password
     */
    public function rehashPassword($password) {
        /* @var $encryptor Mage_Core_Model_Encryption */
        $encryptor = Mage::helper('core')->getEncryptor();

        $newHash = $encryptor->getHash($password);

        $this->setPasswordHash($newHash);
        $this->save();
    }

    /**
     * Rehash password if necessary
     *
     * @param string $password
     * @return boolean
     */
    public function validatePassword($password)
    {
        $valid = parent::validatePassword($password);

        /* @var $helper EW_NativePasswords_Helper_Data */
        $helper = Mage::helper('ew_nativepasswords');

        if($valid && $helper->isEnabled() && $helper->rehashLegacyPasswords()) {
            //see if needs rehash

            $hash = $this->getPasswordHash();
            $needsRehash = password_needs_rehash(
                $hash,
                PASSWORD_BCRYPT,
                Mage::getModel('ew_nativepasswords/encryption')->getOptions()
            );

            if($needsRehash) {
                $this->rehashPassword($password);
            }
        }

        return $valid;
    }
}