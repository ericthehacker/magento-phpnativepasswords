<?php

class EW_NativePasswords_Test_Model_Encryption extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Configure helper to return mock values
     *
     * @param $enabled
     * @param $cost
     */
    protected function _mockHelper($enabled, $backwardsEnabled, $cost) {
        $mockHelper = $this->getHelperMockBuilder('ew_nativepasswords')
            ->setMethods(
                array(
                    'isEnabled',
                    'getConfiguredCost',
                    'allowBackwardsCompatibleVerification'
                )
            )
            ->getMock();

        $mockHelper->method('isEnabled')->will($this->returnValue($enabled));
        $mockHelper->method('getConfiguredCost')->will($this->returnValue($cost));
        $mockHelper->method('allowBackwardsCompatibleVerification')->will($this->returnValue($backwardsEnabled));

        $this->replaceByMock('helper', 'ew_nativepasswords', $mockHelper);
    }

    /**
     * Test native password hashing
     *
     * @test
     * @loadFixture
     * @dataProvider dataProvider
     * @loadExpectation
     */
    public function nativeHashTest($password, $salt, $cost)
    {
        $expectation = self::expected()->getData($password);

        $expectedPair = $expectation[$cost];
        $expectedError = (bool)$expectedPair['error'];
        $expectedHash = isset($expectedPair['hash']) ? $expectedPair['hash'] : '';

        $this->_mockHelper(true, true, $cost);

        /* @var $encryptor EW_NativePasswords_Model_Encryption */
        $encryptor = Mage::helper('core')->getEncryptor();

        try {
            $actualHash = $encryptor->getHash($password, $salt);

            $this->assertEquals($actualHash, $expectedHash);
        } catch(EW_NativePasswords_Exception_InvalidCostException $icex) {
            if(!$expectedError) {
                $this->fail('Invalid cost did not throw exception.');
            }
        }
    }

    /**
     * Test disabled mode falling back to Magento
     *
     * @test
     * @dataProvider dataProvider
     * @loadExpectation
     * @param $password
     * @param $salt
     */
    public function magentoHashTest($password, $salt) {
        $expectation = self::expected()->getData($password);
        $expectedHash = $expectation[$salt];

        $this->_mockHelper(false, true, null);

        /* @var $encryptor EW_NativePasswords_Model_Encryption */
        $encryptor = Mage::helper('core')->getEncryptor();

        $actualHash = $encryptor->getHash($password, $salt);

        $this->assertEquals($actualHash, $expectedHash);
    }

    /**
     * Test validation of PHP native hashes
     *
     * @test
     * @dataProvider dataProvider
     * @loadExpectation
     * @param $password
     * @param $hash
     * @param $expectationIndex
     */
    public function validateNativeHashTest($password, $hash, $backwardsAllowed, $expectationIndex) {
        $expectation = self::expected()->getData($expectationIndex);

        $this->_mockHelper(true, $backwardsAllowed, 10);

        /* @var $encryptor EW_NativePasswords_Model_Encryption */
        $encryptor = Mage::helper('core')->getEncryptor();

        $valid = $encryptor->validateHash($password, $hash);

        $this->assertEquals($valid, $expectation);
    }
}
