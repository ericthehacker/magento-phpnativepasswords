<?php

class EW_NativePasswords_Model_Customer_Customer extends Mage_Customer_Model_Customer
{
    /**
     * Get random password rehash salt.
     *
     * @return string
     */
    public function getRehashSalt() {
        return Mage::helper('core')->getRandomString(Mage_Admin_Model_User::HASH_SALT_LENGTH);
    }

    /**
     * Rehash password and save to db
     *
     * @param $password
     */
    public function rehashPassword($password) {
        /* @var $encryptor EW_NativePasswords_Model_Encryption */
        $encryptor = Mage::helper('core')->getEncryptor();

        //NOTE: although technically an implementation detail, be aware that
        //EW_NativePasswords_Model_Encryption::getHash() will ignore
        //supplied salts and generate its own depending on the return
        //of EW_NativePasswords_Helper_Data::forceNativeSalt()
        $newHash = $encryptor->getHash($password, $this->getRehashSalt());

        $this->setPasswordHash($newHash);
        $this->save();
    }

    public function passwordNeedsRehash() {
        /* @var $helper EW_NativePasswords_Helper_Data */
        $helper = Mage::helper('ew_nativepasswords');

        if(!$helper->isEnabled() || !$helper->rehashLegacyPasswords()) {
            return false;
        }

        //see if needs rehash

        $hash = $this->getPasswordHash();
        $needsRehash = password_needs_rehash(
            $hash,
            PASSWORD_BCRYPT,
            Mage::getModel('ew_nativepasswords/encryption')->getOptions()
        );

        return $needsRehash;
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

        if($valid && $this->passwordNeedsRehash()) {
            $this->rehashPassword($password);
        }

        return $valid;
    }
}