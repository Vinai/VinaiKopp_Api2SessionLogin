<?php

/**
 * Class VinaiKopp_Api2SessionLogin_Model_Api2_Customer_SessionTest
 *
 * Tests for VinaiKopp_Api2SessionLogin_Model_Api2_Customer_Session
 *
 * Only implement tests for this class, since the classes extending
 * from it don't implement any methods.
 */
class VinaiKopp_Api2SessionLogin_Model_Api2_Customer_SessionTest
    extends VinaiKopp_Framework_TestCase
{
    protected $_class = 'VinaiKopp_Api2SessionLogin_Model_Api2_Customer_Session';

    /**
     * @param bool $isLoggedIn
     * @return VinaiKopp_Api2SessionLogin_Model_Api2_Customer_Session
     */
    protected function _getInstance($isLoggedIn = true)
    {
        $mockCustomer = $this->getMock('Mage_Customer_Model_Customer', array(
            'getId', 'getFirstname', 'getLastname', 'load',
        ));
        $mockCustomer->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($isLoggedIn ? 1 : null));
        $mockCustomer->expects($this->any())
            ->method('getFirstname')
            ->will($this->returnValue($isLoggedIn ? 'Test' : null));
        $mockCustomer->expects($this->any())
            ->method('getLastname')
            ->will($this->returnValue($isLoggedIn ? 'Name' : null));
        Mage::getConfig()->setModelMock('customer/customer', $mockCustomer);
        
        $mockCustomerSession = $this->getMockBuilder('Mage_Customer_Model_Session')
            ->disableOriginalConstructor()
            ->getMock();
        $mockCustomerSession->expects($this->any())
            ->method('getCustomer')
            ->with()
            ->will($this->returnValue($mockCustomer));

        $mockRequest = $this->getMockBuilder('Mage_Api2_Model_Request')
            ->disableOriginalConstructor()
            ->getMock();
        $mockRequest->expects($this->once())
            ->method('getResourceType')
            ->with()
            ->will($this->returnValue('session_customer'));
        $mockRequest->expects($this->atLeastOnce())
            ->method('getApiType')
            ->with()
            ->will($this->returnValue('rest'));
        $mockRequest->expects($this->any())
            ->method('getAcceptTypes')
            ->will($this->returnValue('application/json'));

        $mockResponse = $this->getMockBuilder('Mage_Api2_Model_Response')
            ->disableOriginalConstructor()
            ->getMock();
        $mockResponse->expects($this->any())
            ->method('setMimeType')
            ->will($this->returnSelf());
        
        $mockHelper = $this->getMock('VinaiKopp_Api2SessionAuthAdapter_Helper_Frontend_Session');

        $instance = new VinaiKopp_Api2SessionLogin_Model_Api2_Customer_Session(
            $mockCustomerSession, $mockRequest, $mockResponse, $mockHelper
        );
        $instance->setFilter($this->_getMockFilter());
        
        return $instance;
    }
    
    protected function _getMockFilter($allowAttributes = true)
    {
        if ($allowAttributes) {
            $returns = $this->returnArgument(0); // filter nothing
        } else {
            $returns = $this->returnValue(array()); // no attributes allowed
        }
        
        $mockFilter = $this->getMockBuilder('Mage_Api2_Model_Acl_Filter')
            ->disableOriginalConstructor()
            ->getMock();
        $mockFilter->expects($this->any())
            ->method('out')
            ->will($returns);
        $mockFilter->expects($this->any())
            ->method('in')
            ->will($returns);
        
        return $mockFilter;
    }

    public function testClassConfiguration()
    {
        $factoryName = 'vinaikopp_api2sessionlogin/api2_customer_session';
        $class = Mage::getConfig()->getModelClassName($factoryName);
        $this->assertEquals($this->_class, $class);
        $this->assertTrue(class_exists($this->_class));
    }

    /**
     * @test
     * @depends testClassConfiguration
     */
    public function itHasAMethodGetSession()
    {
        $this->assertTrue(is_callable(array($this->_class, 'getSession')));
    }

    /**
     * @test
     * @depends itHasAMethodGetSession
     */
    public function itReturnsACustomerSessionModel()
    {
        $model = $this->_getInstance();

        $this->assertInstanceOf('Mage_Customer_Model_Session', $model->getSession());
    }

    /**
     * @test
     * @depends testClassConfiguration
     */
    public function itHasAMethodDispatch()
    {
        $this->assertTrue(is_callable(array($this->_class, 'dispatch')));
    }

    /**
     * @depends itHasAMethodDispatch
     */
    public function testDispatchCustomerRetrieveEntity()
    {
        $model = $this->_getInstance();
        $model->setActionType('entity')
            ->setOperation('retrieve')
            ->setUserType('customer');
        $model->getResponse()
            ->expects($this->once())
            ->method('setBody')
            ->with('{"isloggedin":true,"firstname":"Test","lastname":"Name"}');
        $model->dispatch();
    }

    /**
     * @depends itHasAMethodDispatch
     */
    public function testDispatchGuestRetrieveEntity()
    {
        $model = $this->_getInstance(false);
        $model->setActionType('entity')
            ->setOperation('retrieve')
            ->setUserType('guest');
        $model->getResponse()
            ->expects($this->once())
            ->method('setBody')
            ->with('{"isloggedin":false,"firstname":null,"lastname":null}');
        $model->dispatch();
    }

    /**
     * @depends itHasAMethodDispatch
     */
    public function testDispatchAdminRetrieveEntity()
    {
        $model = $this->_getInstance(false);
        
        $model->setFilter($this->_getMockFilter(false));

        $model->setActionType('entity')
            ->setOperation('retrieve')
            ->setUserType('admin');
        $model->getResponse()
            ->expects($this->once())
            ->method('setBody')
            ->with('[]');
        $model->dispatch();
    }

    /**
     * @depends itHasAMethodDispatch
     * @expectedException Mage_Api2_Exception
     * @expectedExceptionMessage Resource method not implemented yet.
     */
    public function testDispatchCustomerRetrieveCollection()
    {
        $model = $this->_getInstance();
        $model->setActionType('collection')
            ->setOperation('retrieve')
            ->setUserType('customer');
        $model->dispatch();
    }

    /**
     * @depends itHasAMethodDispatch
     * @expectedException Mage_Api2_Exception
     * @expectedExceptionMessage Resource method not implemented yet.
     */
    public function testDispatchGuestRetrieveCollection()
    {
        $model = $this->_getInstance(false);
        $model->setActionType('collection')
            ->setOperation('retrieve')
            ->setUserType('guest');
        $model->dispatch();
    }

    /**
     * @depends itHasAMethodDispatch
     * @expectedException Mage_Api2_Exception
     * @expectedExceptionMessage Resource method not implemented yet.
     */
    public function testDispatchAdminRetrieveCollection()
    {
        $model = $this->_getInstance(false);

        $model->setFilter($this->_getMockFilter(false));
        
        $model->setActionType('collection')
            ->setOperation('retrieve')
            ->setUserType('admin');
        $model->dispatch();
    }

    /**
     * @return array
     */
    public function actionTypeProvider()
    {
        return array(
            array('entity'),
            array('collection'),
        );
    }

    /**
     * @param string $actionType 'entity' or 'collection'
     * 
     * @depends itHasAMethodDispatch
     * @dataProvider actionTypeProvider
     */
    public function testDispatchCustomerCreate($actionType)
    {
        $model = $this->_getInstance();
        $model->setActionType($actionType)
            ->setOperation('create')
            ->setUserType('customer');
        $model->getRequest()
            ->expects($this->once())
            ->method('getBodyParams')
            ->will($this->returnValue(array(
                'login' => 'test@example.com',
                'password' => 'password123'
            )));
        $model->getRequest()
            ->expects($this->once())
            ->method('isAssocArrayInRequestBody')
            ->with()
            ->will($this->returnValue(true));
        $model->getResponse()
            ->expects($this->once())
            ->method('setBody')
            ->with(json_encode(array('isloggedin' => true, 'firstname' => 'Test', 'lastname' => 'Name')));
        $model->dispatch();
    }

    /**
     * @param string $actionType 'entity' or 'collection'
     *
     * @depends itHasAMethodDispatch
     * @dataProvider actionTypeProvider
     * @expectedException Mage_Api2_Exception
     * @expectedExceptionMessage Resource data pre-validation error.
     */
    public function testDispatchCustomerCreateInvalidData($actionType)
    {
        $model = $this->_getInstance();
        $model->setActionType($actionType)
            ->setOperation('create')
            ->setUserType('customer');
        $model->getRequest()
            ->expects($this->once())
            ->method('getBodyParams')
            ->will($this->returnValue(array(
                'login' => 'test@examplecom',
                'password' => 'password123'
            )));
        $model->getRequest()
            ->expects($this->once())
            ->method('isAssocArrayInRequestBody')
            ->with()
            ->will($this->returnValue(true));
        $model->dispatch();
    }

    /**
     * @param string $actionType 'entity' or 'collection'
     *
     * @depends itHasAMethodDispatch
     * @dataProvider actionTypeProvider
     */
    public function testDispatchCustomerLoginFail($actionType)
    {
        $model = $this->_getInstance(false);
        $model->setActionType($actionType)
            ->setOperation('create')
            ->setUserType('customer');
        $model->getRequest()
            ->expects($this->once())
            ->method('getBodyParams')
            ->will($this->returnValue(array(
                'login' => 'test@example.com',
                'password' => 'password123'
            )));
        $model->getRequest()
            ->expects($this->once())
            ->method('isAssocArrayInRequestBody')
            ->with()
            ->will($this->returnValue(true));
        
        $exception = new Mage_Core_Exception(
            'Invalid login or password.',
            Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD
        );
        $model->getSession()
            ->expects($this->once())
            ->method('login')
            ->will($this->throwException($exception));

        $model->getResponse()
            ->expects($this->once())
            ->method('setException');
        
        $model->getResponse()
            ->expects($this->once())
            ->method('isException')
            ->will($this->returnValue(true));

        $model->getResponse()
            ->expects($this->never())
            ->method('setBody');
        
        $model->dispatch();
    }

    /**
     * @param string $actionType 'entity' or 'collection'
     *
     * @depends itHasAMethodDispatch
     * @dataProvider actionTypeProvider
     * @expectedException Mage_Api2_Exception
     * @expectedExceptionMessage Resource internal error.
     */
    public function testDispatchCustomerLoginException($actionType)
    {
        $model = $this->_getInstance();
        $model->setActionType($actionType)
            ->setOperation('create')
            ->setUserType('customer');
        $model->getRequest()
            ->expects($this->once())
            ->method('getBodyParams')
            ->will($this->returnValue(array(
                'login' => 'test@example.com',
                'password' => 'password123'
            )));
        $model->getRequest()
            ->expects($this->once())
            ->method('isAssocArrayInRequestBody')
            ->with()
            ->will($this->returnValue(true));
        
        $exception = new Exception('Something went wrong.');
        $model->getSession()
            ->expects($this->once())
            ->method('login')
            ->will($this->throwException($exception));

        $model->getResponse()
            ->expects($this->never()) // critical exceptions are handled by api2 server
            ->method('setException');

        $model->getResponse()
            ->expects($this->never()) // critical exceptions are handled by api2 server
            ->method('isException');

        $model->getResponse()
            ->expects($this->never())
            ->method('setBody');
        
        $model->dispatch();
    }

    /**
     * @param string $actionType 'entity' or 'collection'
     * 
     * @depends itHasAMethodDispatch
     * @dataProvider actionTypeProvider
     */
    public function testDispatchGuestCreate($actionType)
    {
        $model = $this->_getInstance(true);
        $model->setActionType($actionType)
            ->setOperation('create')
            ->setUserType('guest');
        $model->getRequest()
            ->expects($this->once())
            ->method('getBodyParams')
            ->will($this->returnValue(array(
                'login' => 'test@example.com',
                'password' => 'password123'
            )));
        $model->getRequest()
            ->expects($this->once())
            ->method('isAssocArrayInRequestBody')
            ->with()
            ->will($this->returnValue(true));

        $model->getResponse()
            ->expects($this->once())
            ->method('setBody')
            ->with(json_encode(array('isloggedin' => true, 'firstname' => 'Test', 'lastname' => 'Name')));

        $model->getResponse()
            ->expects($this->never())
            ->method('setException');
        
        $model->dispatch();
    }

    /**
     * @param string $actionType 'entity' or 'collection'
     * 
     * @depends itHasAMethodDispatch
     * @dataProvider actionTypeProvider
     * @expectedException Mage_Api2_Exception
     * @expectedExceptionMessage The request data is invalid.
     */
    public function testDispatchAdminCreate($actionType)
    {
        $model = $this->_getInstance(true);

        $model->setFilter($this->_getMockFilter(false));

        $model->setActionType($actionType)
            ->setOperation('create')
            ->setUserType('admin');
        $model->getRequest()
            ->expects($this->once())
            ->method('getBodyParams')
            ->will($this->returnValue(array(
                'login' => 'test@example.com',
                'password' => 'password123'
            )));
        $model->getRequest()
            ->expects($this->once())
            ->method('isAssocArrayInRequestBody')
            ->with()
            ->will($this->returnValue(true));

        $model->getResponse()
            ->expects($this->never())
            ->method('setException');

        $model->getResponse()
            ->expects($this->never())
            ->method('setBody');
        
        $model->dispatch();
    }

    /**
     * @depends itHasAMethodDispatch
     */
    public function testDispatchCustomerDeleteEntity()
    {
        $model = $this->_getInstance();
        $model->setActionType('entity')
            ->setOperation('delete')
            ->setUserType('customer');
        $model->getResponse()
            ->expects($this->never())
            ->method('setBody');
        $model->getResponse()
            ->expects($this->never())
            ->method('setHeader');
        $model->getSession()
            ->expects($this->once())
            ->method('logout');
        
        $model->dispatch();
    }
    
    public function userTypeProvider()
    {
        return array(
            array('customer'),
            array('guest'),
            array('admin')
        );
    }

    /**
     * @param string $userType
     * 
     * @depends itHasAMethodDispatch
     * @expectedException Mage_Api2_Exception
     * @expectedExceptionMessage Resource method not implemented yet.
     * @dataProvider userTypeProvider
     */
    public function testDispatchCustomerDeleteCollection($userType)
    {
        $model = $this->_getInstance();
        $model->setActionType('collection')
            ->setOperation('delete')
            ->setUserType($userType);
        $model->dispatch();
    }

    /**
     * @depends itHasAMethodDispatch
     */
    public function testDispatchGuestDeleteEntity()
    {
        $model = $this->_getInstance(false);
        $model->setActionType('entity')
            ->setOperation('delete')
            ->setUserType('guest');
        $model->getSession()
            ->expects($this->never())
            ->method('logout');
        $model->dispatch();
    }

    /**
     * @depends itHasAMethodDispatch
     */
    public function testDispatchAdminDeleteEntity()
    {
        $model = $this->_getInstance(false);

        $model->setFilter($this->_getMockFilter(false));

        $model->setActionType('entity')
            ->setOperation('delete')
            ->setUserType('admin');
        $model->getSession()
            ->expects($this->never())
            ->method('logout');
        $model->dispatch();
    }
}