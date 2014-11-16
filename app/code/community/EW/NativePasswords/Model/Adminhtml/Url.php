<?php

class EW_NativePasswords_Model_Adminhtml_Url extends Mage_Adminhtml_Model_Url
{
    /**
     * Generate secret key for controller and action based on form key
     *
     * @param string $controller Controller name
     * @param string $action Action name
     * @return string
     */
    public function getSecretKey($controller = null, $action = null)
    {
        // ## BEGIN EDIT: bail if native passwords not enabled ##
        if(!Mage::helper('ew_nativepasswords')->isEnabled()) {
            return parent::getSecretKey($controller, $action);
        }
        // ## END EDIT ##

        $salt = Mage::getSingleton('core/session')->getFormKey();

        $p = explode('/', trim($this->getRequest()->getOriginalPathInfo(), '/'));
        if (!$controller) {
            $controller = !empty($p[1]) ? $p[1] : $this->getRequest()->getControllerName();
        }
        if (!$action) {
            $action = !empty($p[2]) ? $p[2] : $this->getRequest()->getActionName();
        }

        $secret = $controller . $action . $salt;

        // ## BEGIN EDIT: use fast hash instead of expensive native hash ##
        return Mage::helper('core')->getEncryptor()->fastHash($secret);
        // ## END EDIT ##
    }
}