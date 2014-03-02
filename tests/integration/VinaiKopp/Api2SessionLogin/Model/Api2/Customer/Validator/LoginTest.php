<?php


class VinaiKopp_Api2SessionLogin_Model_Api2_Customer_Validator_LoginTest 
    extends VinaiKopp_Framework_TestCase
{
    protected $_class = 'VinaiKopp_Api2SessionLogin_Model_Api2_Customer_Validator_Login';

    /**
     * @param PHPUnit_Framework_MockObject_MockObject|bool $mockValidator
     * @return VinaiKopp_Api2SessionLogin_Model_Api2_Customer_Validator_Login
     */
    protected function _getInstance($mockValidator = true)
    {
        if ($mockValidator === true) {
            $mockValidator = $this->getMockBuilder('Zend_Validate_EmailAddress')
                ->disableOriginalConstructor()
                ->getMock();
        }
        
        return new VinaiKopp_Api2SessionLogin_Model_Api2_Customer_Validator_Login($mockValidator);
    }

    public function testClassConfiguration()
    {
        $factoryName = 'vinaikopp_api2sessionlogin/api2_customer_validator_login';
        $class = Mage::getConfig()->getModelClassName($factoryName);
        $this->assertEquals($this->_class, $class);
        $this->assertTrue(class_exists($this->_class));
    }

    /**
     * @test
     * @depends testClassConfiguration
     */
    public function itHasAMethodFilter()
    {
        $this->assertTrue(is_callable(array($this->_class, 'filter')));
    }

    /**
     * @test
     * @depends itHasAMethodFilter
     */
    public function itFiltersOutUnwantedArrayKeys()
    {
        $input = array('test' => true);
        $instance = $this->_getInstance();
        
        $result = $instance->filter($input);
        $this->assertArrayNotHasKey('test', $result);
    }

    /**
     * @test
     * @depends itHasAMethodFilter
     */
    public function itOnlyLeavesLoginAndPassword()
    {
        $input = array('login' => 'test@example.com', 'password' => 'password123', 'other' => 123);
        $instance = $this->_getInstance();
        
        $result = $instance->filter($input);
        $this->assertArrayHasKey('login', $result);
        $this->assertArrayHasKey('password', $result);
        $this->assertArrayNotHasKey('other', $result);
    }

    /**
     * @test
     * @depends testClassConfiguration
     */
    public function itHasAMethodIsValidData()
    {
        $this->assertTrue(is_callable(array($this->_class, 'isValidData')));
    }

    /**
     * @test
     * @depends itHasAMethodIsValidData
     */
    public function itReturnsFalseIfPasswordIsNotsAString()
    {
        $input = array(
            'login' => 'test@example.com',
            'password' => 123
        );
        $this->assertFalse($this->_getInstance()->isValidData($input));
    }

    /**
     * @test
     * @depends itHasAMethodIsValidData
     */
    public function itReturnsFalseIfEmailIsInvalid()
    {
        $input = array(
            'login' => 'test@example',
            'password' => 'password123'
        );
        $this->assertFalse($this->_getInstance(false)->isValidData($input));
    }

    /**
     * @test
     * @depends itHasAMethodIsValidData
     */
    public function itReturnsTrueIfEmailAndPasswordAreValid()
    {
        $input = array(
            'login' => 'test@example.com',
            'password' => 'password123'
        );
        $this->assertTrue($this->_getInstance(false)->isValidData($input));
    }
}