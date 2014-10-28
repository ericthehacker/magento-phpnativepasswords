<?php

class EW_NativePasswords_Test_Model_Encryption extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Test native password hashing
     *
     * @test
     * @loadFixture
     * @dataProvider dataProvider
     * @loadExpectation
     */
    public function nativeHashTest($password, $salt)
    {
        $expectation = self::expected();

        $expectedHash = $expectation->getData($password);

        /* @var $encryptor EW_NativePasswords_Model_Encryption */
        $encryptor = Mage::helper('core')->getEncryptor();

        $actualHash = $encryptor->getHash($password, $salt);

        $this->assertEquals($actualHash, $expectedHash);
    }
}
