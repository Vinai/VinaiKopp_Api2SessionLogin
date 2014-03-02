<?php


class VinaiKopp_Api2SessionLogin_Model_Api2_Customer_Validator_Login
    extends Mage_Api2_Model_Resource_Validator
{
    /**
     * @var Zend_Validate_EmailAddress
     */
    protected $_validator;

    /**
     * @param Zend_Validate_EmailAddress $validator
     */
    public function __construct($validator)
    {
        if ($validator) {
            $this->_validator = $validator;
        }
    }

    /**
     * @return Zend_Validate_EmailAddress
     */
    public function getEmailValidator()
    {
        if (is_null($this->_validator)) {
            $this->_validator = new Zend_Validate_EmailAddress;
        }
        return $this->_validator;
    }
    
    /**
     * @param array $data
     * @return array
     */
    public function filter(array $data)
    {
        $result = array();
        foreach (array('login', 'password') as $key) {
            if (isset($data[$key])) {
                $result[$key] = $data[$key];
            }
        }
        return $result;
        
    }

    /**
     * @param array $data
     * @return bool
     */
    public function isValidData(array $data)
    {
        $errors = array();
        
        if (! $this->_validateEmail($data['login'])) {
            $errors[] = 'Login invalid';
        }
        
        $pass = $data['password'];
        if (! is_string($pass) || strlen($pass) < 4 || strlen($pass) > 512) {
            $errors[] = 'Password invalid';
        }
        
        $this->_setErrors($errors);
        return $errors ? false : true;
    }

    /**
     * @param string $email
     * @return bool
     */
    protected function _validateEmail($email)
    {
        return $this->getEmailValidator()->isValid($email);
    }
} 