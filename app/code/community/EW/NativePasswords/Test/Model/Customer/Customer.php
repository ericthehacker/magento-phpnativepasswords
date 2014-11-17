<?php

class EW_NativePasswords_Test_Model_Customer_Customer extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Test native password hashing
     *
     * @test
     * @loadFixture
     * @dataProvider dataProvider
     * @loadExpectation
     * @param $password
     * @param $originalPasswordHash
     * @param $testHashSalt
     * @param $expectationIndex
     */
    public function customerPasswordRehashTest($password, $originalPasswordHash, $testHashSalt, $expectationIndex) {
        //setup
        //rig customer model to disable save functionality
        $mockCustomer = $this->getModelMockBuilder('customer/customer')
            ->setMethods(
                array(
                    'save',
                    'getRehashSalt'
                )
            )
            ->getMock();
        $mockCustomer->method('save')->will($this->returnValue(true));
        $mockCustomer->method('getRehashSalt')->will($this->returnValue($testHashSalt));
        $this->replaceByMock('model', 'customer/customer', $mockCustomer);

        //get expectations
        $expectation = self::expected()->getData($expectationIndex);
        $needsRehash = (bool)$expectation['needsRehash'];

        //configure customer model
        /* @var $customer EW_NativePasswords_Model_Customer_Customer */
        $customer = Mage::getModel('customer/customer');

        //check rewrite
        $this->assertInstanceOf('EW_NativePasswords_Model_Customer_Customer', $customer);

        $customer->setPasswordHash($originalPasswordHash);

        //check passwordNeedsRehash() value
        $this->assertEquals($needsRehash, $customer->passwordNeedsRehash());

        $expectedNewHash = $expectation['newHash'];

        //trigger validation (and therefore, possibly, rehash)
        $customer->validatePassword($password);

        $actualNewHash = $customer->getPasswordHash();

        //check rehashed password
        $this->assertEquals($expectedNewHash, $actualNewHash);
    }
}