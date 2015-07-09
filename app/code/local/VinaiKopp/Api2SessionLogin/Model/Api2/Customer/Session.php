<?php


class VinaiKopp_Api2SessionLogin_Model_Api2_Customer_Session
    extends Mage_Api2_Model_Resource
{
    // See Mage_Core_Controller_Front_Action::SESSION_NAMESPACE
    const SESSION_NAMESPACE = 'frontend';

    /**
     * @var Mage_Customer_Model_Session
     */
    protected $_session;

    /**
     * @var VinaiKopp_Api2SessionAuthAdapter_Helper_Frontend_Session
     */
    protected $_helper;

    /**
     * @param Mage_Customer_Model_Session $session
     * @param Mage_Api2_Model_Request $request
     * @param Mage_Api2_Model_Response $response
     * @param VinaiKopp_Api2SessionAuthAdapter_Helper_Frontend_Session $helper
     */
    public function __construct(
        $session = null, $request = null, $response = null, $helper = null
    )
    {
        if ($session) {
            $this->_session = $session;
        }

        if ($request) {
            $this->setRequest($request);
        }

        if ($response) {
            $this->setResponse($response);
        }

        if ($helper) {
            $this->_helper = $helper;
        }
    }

    /**
     * @return VinaiKopp_Api2SessionAuthAdapter_Helper_Frontend_Session
     */
    public function getHelper()
    {
        if (is_null($this->_helper)) {
            // @codeCoverageIgnoreStart
            $this->_helper = Mage::helper('vinaikopp_api2sessionauthadapter/frontend_session');
        }
        // @codeCoverageIgnoreEnd
        return $this->_helper;
    }

    /**
     * @return Mage_Customer_Model_Session
     */
    public function getSession()
    {
        if (is_null($this->_session)) {
            // @codeCoverageIgnoreStart
            $this->_session = Mage::getSingleton('customer/session');
        }
        // @codeCoverageIgnoreEnd
        return $this->_session;
    }

    /**
     * Work around limitation of parent which forbids create for
     * single entity resources.
     * For this use case collections don't make sense.
     */
    public function dispatch()
    {
        switch ($this->getActionType() . $this->getOperation()) {
            case self::ACTION_TYPE_ENTITY . self::OPERATION_CREATE:
            case self::ACTION_TYPE_COLLECTION . self::OPERATION_CREATE:
                $requestData = $this->getRequest()->getBodyParams();
                if (empty($requestData)) {
                    $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                }
                // The create action has the dynamic type which depends on data in the request body
                if ($this->getRequest()->isAssocArrayInRequestBody()) {
                    $filteredData = $this->getFilter()->in($requestData);
                    if (empty($filteredData)) {
                        $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                    }
                    $this->_create($filteredData);
                    if ($this->getResponse()->isException()) {
                        break;
                    }

                    // This fixes a 'bug' with calling in() and out() on the same filter in one request
                    /** @var $filter Mage_Api2_Model_Acl_Filter */
                    $filter = Mage::getModel('api2/acl_filter', $this);
                    $this->setFilter($filter);
                }
            // break statement left out intentionally!

            case self::ACTION_TYPE_ENTITY . self::OPERATION_RETRIEVE:
                $retrievedData = $this->_retrieve();
                $filteredData = $this->getFilter()->out($retrievedData);
                $this->_render($filteredData);
                break;

            case self::ACTION_TYPE_ENTITY . self::OPERATION_DELETE:
                $this->_delete();
                break;

            default:
                $this->_critical(self::RESOURCE_METHOD_NOT_IMPLEMENTED);
                break;
        }
    }

    /**
     * Ignore PHPStorm complaining about protected visibility.
     *
     * @return array
     */
    protected function _retrieve()
    {
        $session = $this->getSession();
        // Don't use isLoggedIn here because the customerIdCheck result
        // is cached as false if the session was just created.
        $customer = $session->getCustomer();
        return array(
            'isloggedin' => $customer && $customer->getId(),
            'firstname' => $customer ? $customer->getFirstname() : '',
            'lastname' => $customer ? $customer->getLastname() : ''
        );
    }

    /**
     * Ignore PHPStorm complaining about protected visibility.
     *
     * @param array $data
     * @return array
     */
    protected function _create(array $data)
    {
        if (!isset($data['login']) || !isset($data['password'])) {
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }

        try {
            $helper = $this->getHelper();
            $helper->initFrontendStore();
            $helper->startFrontendSession();
            $session = $this->getSession();
            $session->login($data['login'], $data['password']);
        } catch (Exception $e) {
            if ($e instanceof Mage_Core_Exception) {
                if ($e->getCode() == Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD || $e->getCode() == Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED) {
                    throw new Mage_Api2_Exception($e->getMessage(), Mage_Api2_Model_Server::HTTP_UNAUTHORIZED);
                }
            }
            Mage::logException($e);
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
    }

    /**
     * Ignore PHPStorm complaining about protected visibility.
     *
     * @return array
     */
    protected function _delete()
    {
        $session = $this->getSession();
        if ($session->getCustomer() && $session->getCustomer()->getId()) {
            $session->logout();
        }
    }
} 