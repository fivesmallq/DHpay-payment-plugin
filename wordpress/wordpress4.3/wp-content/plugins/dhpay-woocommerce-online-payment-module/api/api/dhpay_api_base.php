<?php

/**
 *  DHPAY API
 *
 *  @version 2.5.2
 *  @author Olaf Abbenhuis
 *  @author Wouter van Tilburg
 *  @copyright Copyright (c) 2012, DHPAY
 *
 */

/**
 *  Interfaces
 *
 *  @author Olaf Abbenhuis
 *  @since 2.1.0
 */
interface Dhpay_PaymentObject_Interface_Abstract {

    public function setData($data);

    public function getData();

    public function setIssuer($issuer);

    public function getIssuer();

    public function setPaymentMethod($paymentmethod);

    public function getPaymentMethod();

    public function setCountry($country);

    public function getCountry();

    public function setCurrency($currency);

    public function getCurrency();

    public function setLanguage($lang);

    public function getLanguage();

    public function setAmount($amount);

    public function getAmount();

    public function setOrderID($id = "");

    public function getOrderID();

    public function setReference($reference = "");

    public function getReference();

    public function setDescription($info = "");

    public function getDescription();
}

interface Dhpay_Basic_Paymentmethod_Interface_Abstract {

    public function getCode();

    public function getReadableName();

    public function getSupportedIssuers();

    public function getSupportedCountries();

    public function getSupportedCurrency();

    public function getSupportedLanguages();

    public function getSupportedAmountRange();
}

interface Dhpay_WebserviceTransaction_Interface_Abstract {

    public function setData($data);

    public function getPaymentScreenURL();

    public function getPaymentID();

    public function getProviderTransactionID();

    public function getTestMode();

    public function getTimestamp();

    public function getEndUserIP();
}

/**
 *  The Transaction Object is returned when making a payment using the webservices
 * 
 *  @author Olaf Abbenhuis
 *  @since 2.1.0
 */
class Dhpay_TransactionObject implements Dhpay_WebserviceTransaction_Interface_Abstract {

    protected $data;

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getPaymentScreenURL()
    {
        return $this->data->PaymentScreenURL;
    }

    public function getPaymentID()
    {
        return $this->data->PaymentID;
    }

    public function getProviderTransactionID()
    {
        return $this->data->ProviderTransactionID;
    }

    public function getTestMode()
    {
        return $this->data->TestMode;
    }

    public function getTimestamp()
    {
        return $this->data->Timestamp;
    }

    public function getEndUserIP()
    {
        return $this->data->EndUserIP;
    }

}

/**
 *  The Payment Object is the class for a payment. Can be instanced if desired, although the instance isnt used within the API.
 * 
 *  @author Olaf Abbenhuis
 *  @since 2.1.1
 */
class Dhpay_PaymentObject implements Dhpay_PaymentObject_Interface_Abstract {

    protected $data;
    protected $api_type = "webservice";
    protected $pm_class;
    private static $instance;

    /**
     * Construct of Dhpay_PaymentObject
     * @since version 2.1.1
     * @access public 
     */
    public function __construct()
    {
        // Instantiate $this->data explicitely for PHP Strict error reporting
        $this->data = new stdClass();
    }

    public static function getInstance()
    {
        if (!self::$instance)
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * Set all fields in one method
     * @since version 2.1.0
     * @access public
     * @param object $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Get all data as an object
     * @since version 2.1.0
     * @access public
     * @return object 
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Load a paymentmethod class for Basic
     * @since version 2.1.0
     * @access protected
     */
    protected function loadBasicPaymentMethodClass()
    {

        if (!class_exists("Dhpay_Api_Basic"))
            return $this;

        $this->pm_class = Dhpay_Api_Basic::getInstance()
                ->readFolder()
                ->getClassByPaymentMethodCode($this->data->ic_paymentmethod);

        if (count($this->pm_class->getSupportedIssuers()) == 1) {
            $this->setIssuer(current($this->pm_class->getSupportedIssuers()));
        }
        return $this;
    }

    /**
     * Get all data as an object
     * @since version 2.1.0
     * @access public
     * @return Dhpay_Basic_Paymentmethod_Interface_Abstract
     */
    public function getBasicPaymentmethodClass()
    {
        return $this->pm_class;
    }

    /**
     * Set the country field
     * @since version 1.0.0
     * @access public
     * @param string $currency Country ISO 3166-1-alpha-2 code !Required
     * @example setCountry("NL") // Netherlands
     */
    public function setCountry($country)
    {
        $country = strtoupper($country);
        if (!Dhpay_Parameter_Validation::country($country))
            throw new Exception('Country not valid');
        $this->data->ic_country = $country;
        return $this;
    }

    /**
     * Set the currency field
     * @since version 1.0.0
     * @access public
     * @param string $currency Language ISO 4217 code !Required
     * @example setCurrency("EUR") // Euro
     */
    public function setCurrency($currency)
    {
        $this->data->ic_currency = $currency;
        return $this;
    }

    /**
     * Set the language field
     * @since version 1.0.0
     * @access public
     * @param string $lang Language ISO 639-1 code !Required
     * @example setLanguage("EN") // English
     */
    public function setLanguage($lang)
    {
        if (!Dhpay_Parameter_Validation::language($lang))
            throw new Exception('Language not valid');
        $this->data->ic_language = $lang;
        return $this;
    }

    /**
     * Set the amount field
     * @since version 1.0.0
     * @access public
     * @param int $amount !Required
     */
    public function setAmount($amount)
    {
        $amount = (int) (string) $amount;

        if (!Dhpay_Parameter_Validation::amount($amount))
            throw new Exception('Amount not valid');
        $this->data->ic_amount = $amount;
        return $this;
    }

    /**
     * Set the order ID field (optional)
     * @since version 1.0.0
     * @access public
     * @param string $id
     */
    public function setOrderID($id = "")
    {
        $this->data->ic_orderid = $id;
        return $this;
    }

    /**
     * Set the reference field (optional)
     * @since version 1.0.0
     * @access public
     * @param string $reference
     */
    public function setReference($reference = "")
    {
        $this->data->ic_reference = $reference;
        return $this;
    }

    /**
     * Set the description field (optional)
     * @since version 1.0.0
     * @access public
     * @param string $info
     */
    public function setDescription($info = "")
    {
        $this->data->ic_description = $info;
        return $this;
    }

    /**
     * Sets the issuer and checks if the issuer exists within the paymentmethod
     * @since version 1.0.0
     * @access public
     * @param string $issuer DHPAY Issuer code
     */
    public function setIssuer($issuer)
    {
        $this->data->ic_issuer = $issuer;
        return $this;
    }

    public function setXML($xml)
    {
        $this->data->ic_xml = $xml;
        return $this;
    }

    /**
     * Sets the payment method and checks if the method exists within the class
     * @since version 1.0.0
     * @access public
     * @param string $paymentMethod DHPAY Payment method code
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->data->ic_paymentmethod = $paymentMethod;
        $this->loadBasicPaymentMethodClass();
        return $this;
    }

    public function getCountry()
    {
        return $this->data->ic_country;
    }

    public function getCurrency()
    {
        return $this->data->ic_currency;
    }

    public function getLanguage()
    {
        return $this->data->ic_language;
    }

    public function getAmount()
    {
        return $this->data->ic_amount;
    }

    public function getOrderID()
    {
        return $this->data->ic_orderid;
    }

    public function getReference()
    {
        return (isset($this->data->ic_reference) ? $this->data->ic_reference : null);
    }

    public function getDescription()
    {
        return (isset($this->data->ic_description) ? $this->data->ic_description : null);
    }

    public function getIssuer()
    {
        return (isset($this->data->ic_issuer) ? $this->data->ic_issuer : null);
    }

    public function getPaymentMethod()
    {
        return (isset($this->data->ic_paymentmethod) ? $this->data->ic_paymentmethod : null);
    }

    public function getXML()
    {
        return $this->data->ic_xml;
    }

}

/**
 *  The Dhpay_Paymentmethod is the base class for all payment method subclasses
 * 
 *  @author Olaf Abbenhuis
 *  @since 2.1.0
 */
class Dhpay_Paymentmethod implements Dhpay_Basic_Paymentmethod_Interface_Abstract {

    public $_version = null;
    public $_method = null;
    public $_readable_name = null;
    public $_issuer = null;
    public $_country = null;
    public $_language = null;
    public $_currency = null;
    public $_amount = null;

    /**
     * Get the version of the API or the loaded payment method class
     * @since version 1.0.1
     * @access public
     * @return string
     */
    public function getCode()
    {
        return $this->_method;
    }

    /**
     * Get the version of the API or the loaded payment method class
     * @since version 1.0.1
     * @access public
     * @return string
     */
    public function getReadableName()
    {
        return $this->_readable_name;
    }

    /**
     * Get the supported issuers of the loaded paymentmethod
     * @since version 1.0.0
     * @access public
     * @return array The issuer codes of the paymentmethod
     */
    public function getSupportedIssuers()
    {
        return $this->_issuer;
    }

    /**
     * Get the supported countries of the loaded paymentmethod
     * @since version 1.0.0
     * @access public
     * @return array The country codes of the paymentmethod
     */
    public function getSupportedCountries()
    {
        return $this->_country;
    }

    /**
     * Get the supported currencies of the loaded paymentmethod
     * @since version 1.0.0
     * @access public
     * @return array The currency codes of the paymentmethod
     */
    public function getSupportedCurrency()
    {
        return $this->_currency;
    }

    /**
     * Get the supported languages of the loaded paymentmethod
     * @since version 1.0.0
     * @access public
     * @return array The Language codes of the paymentmethod
     */
    public function getSupportedLanguages()
    {
        return $this->_language;
    }

    /**
     * Get the general amount range of the loaded paymentmethod
     * @since version 1.0.0
     * @access public
     * @return array [minimum(uint), maximum(uint)]
     */
    public function getSupportedAmountRange()
    {
        return $this->_amount;
    }

}

/**
 *  Dhpay_StatusCode static class
 *  Contains the payment statuscode constants
 * 
 *  @author Olaf Abbenhuis
 *  @since 1.0.0
 */
class Dhpay_StatusCode {

    const OPEN = "OPEN";
    const AUTHORIZED = "AUTHORIZED";
    const ERROR = "ERR";
    const SUCCESS = "OK";
    const REFUND = "REFUND";
    const CHARGEBACK = "CBACK";

}

/**
 *  Dhpay_Project_Helper class
 *  A helper for all-in-one solutions
 * 
 *  @author Olaf Abbenhuis
 *  @since 1.0.0
 *
 */
class Dhpay_Project_Helper {

    private static $instance;
    private $_release = "2.5.2";
    private $_basic;
    private $_result;
    private $_postback;
    private $_validate;

    /**
     * Returns the Dhpay_Basicmode class or creates it
     * 
     * @since 1.0.0
     * @access public
     * @return \Dhpay_Basicmode
     */
    public function basic()
    {
        if (!isset($this->_basic))
            $this->_basic = new Dhpay_Basicmode();
        return $this->_basic;
    }

    /**
     * Returns the Dhpay_Result class or creates it
     * 
     * @since 1.0.0
     * @access public
     * @return \Dhpay_Result
     */
    public function result()
    {
        if (!isset($this->_result))
            $this->_result = new Dhpay_Result();
        return $this->_result;
    }

    /**
     * Returns the Dhpay_Postback class or creates it
     * 
     * @since 1.0.0
     * @access public
     * @return \Dhpay_Postback
     */
    public function postback()
    {
        if (!isset($this->_postback))
            $this->_postback = new Dhpay_Postback();
        return $this->_postback;
    }

    /**
     * Returns the Dhpay_Paramater_Validation class or creates it
     * 
     * @since 1.1.0
     * @access public
     * @return \Dhpay_Parameter_Validation
     */
    public function validate()
    {
        if (!isset($this->_validate))
            $this->_postback = new Dhpay_Parameter_Validation();
        return $this->_validate;
    }

    /**
     * Returns the current release version
     * 
     * @since 1.1.0
     * @access public
     * @return string 
     */
    public function getReleaseVersion()
    {
        return $this->_release;
    }

    /**
     * Create an instance
     * @since version 1.0.2
     * @access public
     * @return instance of self
     */
    public static function getInstance()
    {
        if (!self::$instance)
            self::$instance = new self();
        return self::$instance;
    }

}

/**
 *  Dhpay_Api_Base class
 *  Basic Setters and Getters required in most API
 * 
 *  @author Olaf Abbenhuis
 *  @author Wouter van Tilburg
 *  @package API_Base
 *  @since 1.0.0
 *  @version 1.0.2
 *
 */
class Dhpay_Api_Base {

    private $_pinCode;
    protected $_merchantID;
    protected $_secretCode;
    protected $_method = null;
    protected $_issuer = null;
    protected $_country = null;
    protected $_language = null;
    protected $_currency = null;
    protected $_version = "1.0.2";
    protected $_doIPCheck = array();
    protected $_whiteList = array();
    protected $data;
    protected $_logger;

    public function __construct()
    {
        $this->_logger = Dhpay_Api_Logger::getInstance();
        $this->data = new stdClass();
    }

    /**
     * Validate data
     * @since version 1.0.0
     * @access public
     * @param string $needle
     * @param array $haystack
     * @return boolean
     */
    public function exists($needle, $haystack = null)
    {
        $result = true;
        if ($haystack && $result && $haystack[0] != "00")
            $result = in_array($needle, $haystack);
        return $result;
    }

    /**
     * Get the version of the API or the loaded payment method class
     * @since 1.0.0
     * @access public
     * @return string Version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the Merchant ID field
     * @since 1.0.0
     * @access public
     * @param (int) $merchantID
     */
    public function setMerchantID($merchantID)
    {
        $this->_merchantID = (int) $merchantID;

        return $this;
    }

    /**
     * Get the Merchant ID field
     * @since 1.0.0
     * @access public
     * @return (int) MerchantID
     */
    public function getMerchantID()
    {
        return $this->_merchantID;
    }

    /**
     * Set the Secret Code field
     * @since 1.0.0
     * @access public
     * @param (string) $secretCode
     */
    public function setSecretCode($secretCode)
    {
        $this->_secretCode = (string) $secretCode;
        return $this;
    }

    /**
     * Get the Secret Code field
     * @since 1.0.0
     * @access protected
     * @return (string) Secret Code
     */
    protected function getSecretCode()
    {
        return $this->_secretCode;
    }

    /**
     * Set the Pin Code field
     * @since 1.0.1
     * @access public
     * @param (int) $pinCode 
     */
    public function setPinCode($pinCode)
    {
        $this->_pinCode = (string) $pinCode;

        return $this;
    }

    /**
     * Get the Pin Code field
     * @since 1.0.0
     * @access protected
     * @return (int) PinCode
     */
    protected function getPinCode()
    {
        return $this->_pinCode;
    }

    /**
     * Set the success url field (optional)
     * @since version 1.0.1
     * @access public
     * @param string $url
     */
    public function setSuccessURL($url = "")
    {
        if (!isset($this->data))
            $this->data = new stdClass();

        $this->data->ic_urlcompleted = $url;

        return $this;
    }

    /**
     * Set the error url field (optional)
     * @since version 1.0.1
     * @access public
     * @param string $url
     */
    public function setErrorURL($url = "")
    {
        if (!isset($this->data))
            $this->data = new stdClass();

        $this->data->ic_urlerror = $url;
        return $this;
    }

    /**
     * Get the success URL
     * @since version 2.1.0
     * @access public
     * @return string $url
     */
    public function getSuccessURL()
    {
        return (isset($this->data->ic_urlcompleted)) ? $this->data->ic_urlcompleted : "";
    }

    /**
     * Get the error URL
     * @since version 2.1.0
     * @access public
     * @return string $url
     */
    public function getErrorURL()
    {
        return (isset($this->data->ic_urlerror)) ? $this->data->ic_urlerror : "";
    }

    public function getTimestamp()
    {
        return gmdate("Y-m-d\TH:i:s\Z");
    }

}

/**
 * Dhpay_Parameter_Validation class
 * Validates parameters
 * 
 * @author Olaf Abbenhuis
 * @since 2.1.0
 */
class Dhpay_Parameter_Validation {

    protected $_version = "1.0.0";

    /**
     * Check if Country is valid
     * 
     * @since 2.1.0
     * @access public
     * @param string $string
     * @return bool 
     */
    public static function country($string)
    {
        return (strlen($string) == 2);
    }

    /**
     * Check if Language is valid
     * 
     * @since 2.1.0
     * @access public
     * @param string $string
     * @return bool 
     */
    public static function language($string)
    {
        return (strlen($string) == 2 && !is_numeric($string));
    }

    /**
     * Check if Currency is valid
     * 
     * @since 2.1.0
     * @access public
     * @param string $string
     * @return bool
     */
    public static function currency($string)
    {
        return (strlen($string) == 3 && !is_numeric($string));
    }

    /**
     * Check if Amount is valid
     * 
     * @since 2.1.0
     * @access public
     * @param int $number
     * @return bool
     */
    public static function amount($number)
    {
        return (is_numeric($number));
    }

    public static function orderID($orderID)
    {
        return (strlen($orderID) < 11);
    }

}

/**
 * Dhpay_Api_Logger
 * Handles all the logging
 * 
 * @author Olaf Abbenhuis
 * @author Wouter van Tilburg
 * @since 2.1.0
 */
class Dhpay_Api_Logger {

    private static $instance;

    const NOTICE = 1;
    const TRANSACTION = 2;
    const ERROR = 4;
    const LEVEL_ALL = 1;
    const LEVEL_TRANSACTION = 2;
    const LEVEL_ERRORS = 4;
    const LEVEL_ERRORS_AND_TRANSACTION = 8;

    private $version = '1.0.0';
    protected $_loggingDirectory = 'logs';
    protected $_loggingFile = 'log.txt';
    protected $_loggingEnabled = false;
    protected $_logToFile = false;
    protected $_logToScreen = false;
    protected $_logToHook = false;
    protected $_logHookClass = null;
    protected $_logHookFunc = null;
    protected $_logLevel = 14; // Log errors and transactions

    /**
     * Enables logging 
     *  
     * @since 2.1.0
     * @access public  
     * @return \Dhpay_Basicmode
     */

    public function enableLogging($bool = true)
    {
        $this->_loggingEnabled = $bool;

        return $this;
    }

    /**
     * Enables logging to file
     * 
     * @since 2.1.0
     * @access public 
     * @param bool $bool
     * @return \Dhpay_Basicmode
     */
    public function logToFile($bool = true)
    {
        $this->_logToFile = $bool;

        return $this;
    }

    /**
     * Enables logging to screen
     * 
     * @since 2.1.0
     * @access public
     * @param bool $bool
     * @return \Dhpay_Basicmode
     */
    public function logToScreen($bool = true)
    {
        $this->_logToScreen = $bool;

        return $this;
    }

    /**
     * Enable or disable logging to a hooked class
     * 
     * @since 2.1.0
     * @access public
     * @param string $className
     * @param string $logFunction 
     * @param bool $bool
     * @return \Dhpay_Basicmode
     */
    public function logToFunction($className = null, $logFunction = null, $bool = true)
    {
        $this->_logToHook = $bool;

        if (class_exists($className))
            $this->_logHookClass = new $className;

        if (is_callable($logFunction))
            $this->_logHookFunc = $logFunction;

        return $this;
    }

    /**
     * Set the directory of the logging file
     * 
     * @since 2.1.0
     * @access public 
     * @param type $dirName 
     * @return \Dhpay_Basicmode
     */
    public function setLoggingDirectory($dirName = null)
    {
        if ($dirName)
            $this->_loggingDirectory = $dirName;

        return $this;
    }

    /**
     * Set the logging file
     * 
     * @since 2.1.0
     * @access public
     * @param string $fileName 
     * @return \Dhpay_Basicmode
     */
    public function setLoggingFile($fileName = null)
    {
        if ($fileName)
            $this->_loggingFile = $fileName;

        return $this;
    }

    /**
     * Set the logging level
     * 
     * @since 2.1.0
     * @access public
     * @param int $level 
     */
    public function setLoggingLevel($level)
    {
        switch ($level) {
            case Dhpay_Api_Logger::LEVEL_ALL:
                $this->_setLoggingFlag(Dhpay_Api_Logger::NOTICE);
                $this->_setLoggingFlag(Dhpay_Api_Logger::TRANSACTION);
                $this->_setLoggingFlag(Dhpay_Api_Logger::ERROR);
                break;
            case Dhpay_Api_Logger::LEVEL_ERRORS:
                $this->_setLoggingFlag(Dhpay_Api_Logger::NOTICE, false);
                $this->_setLoggingFlag(Dhpay_Api_Logger::TRANSACTION, false);
                $this->_setLoggingFlag(Dhpay_Api_Logger::ERROR);
                break;
            case Dhpay_Api_Logger::LEVEL_TRANSACTION:
                $this->_setLoggingFlag(Dhpay_Api_Logger::NOTICE, false);
                $this->_setLoggingFlag(Dhpay_Api_Logger::TRANSACTION);
                $this->_setLoggingFlag(Dhpay_Api_Logger::ERROR, false);
                break;
            case Dhpay_Api_Logger::LEVEL_ERRORS_AND_TRANSACTION:
                $this->_setLoggingFlag(Dhpay_Api_Logger::NOTICE, false);
                $this->_setLoggingFlag(Dhpay_Api_Logger::TRANSACTION);
                $this->_setLoggingFlag(Dhpay_Api_Logger::ERROR);
                break;
        }

        return $this;
    }

    /*
     * Set the logging flag
     * 
     * @since 2.1.0
     * @access private
     * @param int $flag
     * @param bool $boolean
     */

    private function _setLoggingFlag($flag, $boolean = true)
    {
        if ($boolean) {
            $this->_logLevel |= $flag;
        } else {
            $this->_logLevel &= ~$flag;
        }
    }

    /*
     * Check if type is exists 
     * 
     * @since 2.1.0
     * @access private
     * @param int $type
     * @return bool
     */

    private function _isLoggingSet($type)
    {
        return (($this->_logLevel & $type) == $type);
    }

    /**
     * Log given line
     * 
     * @since 2.1.0
     * @access public
     * @param string $line
     * @param int $level
     * @return boolean
     * @throws Exception 
     */
    public function log($line, $level = 1)
    {
        // Check if logging is enabled
        if (!$this->_loggingEnabled)
            return false;

        // Check if the level is within the required level
        if (!$this->_isLoggingSet($level))
            return false;

        $dateTime = date("H:i:s", time());
        $line = "{$dateTime} [DHPAY]: {$line}" . PHP_EOL;

        // Log to Screen
        if ($this->_logToScreen)
            echo "{$line} <br />";

        // Log to Hooked Class
        if ($this->_logToHook && $this->_logHookClass && $this->_logHookFunc) {
            $function = $this->_logHookFunc;
            $this->_logHookClass->$function($line);
        }


        // Log to Default File
        if ($this->_logToFile) {
            $file = $this->_loggingDirectory . DS . $this->_loggingFile;

            try {
                $fp = fopen($file, "a");
                fwrite($fp, $line);
                fclose($fp);
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            };
        }
    }

    /**
     * Get version of API Logger
     * 
     * @since 2.1.0
     * @access public
     * @return version 
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Create an instance
     * 
     * @since 2.1.0
     * @access public
     * @return instance of self
     */
    public static function getInstance()
    {
        if (!self::$instance)
            self::$instance = new self();
        return self::$instance;
    }

}

/**
 *  Dhpay_Postback class
 *  To handle the postback
 * 
 *  @author Olaf Abbenhuis
 *  @author Wouter van Tilburg
 * 
 *  @since 1.0.0
 */
class Dhpay_Postback extends Dhpay_Api_Base {

    public function __construct()
    {
        parent::__construct();
        $this->data = new stdClass();
    }

    /**
     * Return minimized transactional data
     * @since version 1.0.0
     * @access public
     * @return string
     */
    public function getTransactionString()
    {
        return sprintf(
                "Paymentmethod: %s \n| OrderID: %s \n| Status: %s \n| StatusCode: %s \n| PaymentID: %s \n| TransactionID: %s \n| Amount: %s", isset($this->data->paymentMethod) ? $this->data->paymentMethod : "", isset($this->data->orderID) ? $this->data->orderID : "", isset($this->data->status) ? $this->data->status : "", isset($this->data->statusCode) ? $this->data->statusCode : "", isset($this->data->paymentID) ? $this->data->paymentID : "", isset($this->data->transactionID) ? $this->data->transactionID : "", isset($this->data->amount) ? $this->data->amount : ""
        );
    }

    /**
     * Return the statuscode field
     * @since version 1.0.0
     * @access public
     * @return string
     */
    public function getStatus()
    {
        $statusMapping = array('00'=>Dhpay_StatusCode::OPEN, '01'=>Dhpay_StatusCode::SUCCESS, '02'=>Dhpay_StatusCode::ERROR);
        return (isset($statusMapping[$this->data->status])) ? $statusMapping[$this->data->status] : null;
    }

    /**
     * Return the orderID field
     * @since version 1.0.0
     * @access public
     * @return string
     */
    public function getOrderID()
    {
        return (isset($this->data->order_no)) ? $this->data->order_no : null;
    }

    /**
     * Return the postback checksum
     * @since version 1.0.0
     * @access protected
     * @return string SHA1 encoded
     */
    protected function generateChecksumForPostback()
    {
        $hash_src = '';
        $this->data->merchant_id = $this->_merchantID;

        $hash_key = array('amount','currency', 'invoice_id', 'merchant_id',
            'trans_time', 'trans_date', 'status', 'ref_no', 'order_no');
        if ($this->data->status == '02') {
            $hash_key[] = 'failure_reason';
        }
        // 按 key 名进行顺序排序
        sort($hash_key);
        foreach ($hash_key as $key) {
            $hash_src .= $this->data->$key;
        }
        // 密钥放最前面
        $hash_src = $this->getSecretCode() . $hash_src;
        // sha256 算法
        $hash = hash('sha256', $hash_src);
        return strtoupper($hash);
    }

    /**
     * Validate the postback data
     * @since version 1.0.0
     * @access public
     * @return boolean
     */
    public function validate()
    {
        $this->_logger->log(sprintf("Postback: %s", serialize($_GET)), Dhpay_Api_Logger::TRANSACTION);

        $this->data->status = (isset($_GET['status'])) ? $_GET['status'] : "";
        $this->data->order_no = (isset($_GET['order_no'])) ? $_GET['order_no'] : "";
        $this->data->ref_no = (isset($_GET['ref_no'])) ? $_GET['ref_no'] : "";
        $this->data->hash = (isset($_GET['hash'])) ? $_GET['hash'] : "";
        $this->data->amount = (isset($_GET['amount'])) ? $_GET['amount'] : "";
        $this->data->currency = (isset($_GET['currency'])) ? $_GET['currency'] : "";
        $this->data->invoice_id = (isset($_GET['invoice_id'])) ? $_GET['invoice_id'] : "";
        $this->data->trans_time = (isset($_GET['trans_time'])) ? $_GET['trans_time'] : "";
        $this->data->trans_date = (isset($_GET['trans_date'])) ? $_GET['trans_date'] : "";


        if ($this->generateChecksumForPostback() != $this->data->hash) {
            $this->_logger->log("Checksum does not match", Dhpay_Api_Logger::ERROR);
            return false;
        }
        return true;
    }

    /**
     * Return the postback data
     * @since version 1.0.0
     * @access public
     * @return object
     */
    public function getPostback()
    {
        return $this->data;
    }

    /**
     * Check between DHPAY statuscodes whether the status can be updated.
     * @since version 1.0.0
     * @access public
     * @param string $currentStatus The DHPAY statuscode of the order before a statuschange
     * @return boolean
     */
    public function canUpdateStatus($currentStatus)
    {
        if (!isset($this->data->statusCode)) {
            $this->_logger->log("Status not set", Dhpay_Api_Logger::ERROR);
            return false;
        }

        switch ($this->data->statusCode) {
            case Dhpay_StatusCode::SUCCESS: return ($currentStatus == Dhpay_StatusCode::ERROR || $currentStatus == Dhpay_StatusCode::AUTHORIZED || $currentStatus == Dhpay_StatusCode::OPEN);
            case Dhpay_StatusCode::OPEN: return ($currentStatus == Dhpay_StatusCode::OPEN);
            case Dhpay_StatusCode::AUTHORIZED: return ($currentStatus == Dhpay_StatusCode::OPEN);
            case Dhpay_StatusCode::ERROR: return ($currentStatus == Dhpay_StatusCode::OPEN || $currentStatus == Dhpay_StatusCode::AUTHORIZED);
            case Dhpay_StatusCode::CHARGEBACK: return ($currentStatus == Dhpay_StatusCode::SUCCESS);
            case Dhpay_StatusCode::REFUND: return ($currentStatus == Dhpay_StatusCode::SUCCESS);
            default:
                return false;
        }
    }

}

/**
 *  Dhpay_Result class
 *  To handle the success and error page
 * 
 *  @author Olaf Abbenhuis
 *  @since 1.0.0
 */
class Dhpay_Result extends Dhpay_Api_Base {

    public function __construct()
    {
        parent::__construct();
        $this->data = new stdClass();
    }

    /**
     * Validate the DHPAY GET data
     * @since version 1.0.0
     * @access public
     * @return boolean
     */
    public function validate()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            $this->_logger->log("Invalid request method", Dhpay_Api_Logger::ERROR);
            return false;
        }

        $this->_logger->log(sprintf("Page data: %s", serialize($_GET)), Dhpay_Api_Logger::NOTICE);

        $this->data->status = (isset($_GET['status'])) ? $_GET['status'] : "";
        $this->data->order_no = (isset($_GET['order_no'])) ? $_GET['order_no'] : "";
        $this->data->ref_no = (isset($_GET['ref_no'])) ? $_GET['ref_no'] : "";
        $this->data->hash = (isset($_GET['hash'])) ? $_GET['hash'] : "";
        $this->data->amount = (isset($_GET['amount'])) ? $_GET['amount'] : "";
        $this->data->currency = (isset($_GET['currency'])) ? $_GET['currency'] : "";
        $this->data->invoice_id = (isset($_GET['invoice_id'])) ? $_GET['invoice_id'] : "";
        $this->data->trans_time = (isset($_GET['trans_time'])) ? $_GET['trans_time'] : "";
        $this->data->trans_date = (isset($_GET['trans_date'])) ? $_GET['trans_date'] : "";
        $this->data->merchant_id = $this->getMerchantID();

        if ($this->generateChecksumForPage() != $this->data->hash) {
            $this->_logger->log("Hash does not match", Dhpay_Api_Logger::ERROR);
            return false;
        }

        return true;
    }

    /**
     * Get the DHPAY status
     * @since version 1.0.0
     * @access public
     * @param boolean $includeStatusCode Add the statuscode message to the returned string for display purposes
     * @return string DHPAY statuscode (and statuscode message)
     */
    public function getStatus()
    {
        $statusMapping = array('00'=>Dhpay_StatusCode::OPEN, '01'=>Dhpay_StatusCode::SUCCESS, '02'=>Dhpay_StatusCode::ERROR);
        return (isset($statusMapping[$this->data->status])) ? $statusMapping[$this->data->status] : null;
    }

    /**
     * Return the orderID field
     * @since version 1.0.2
     * @access public
     * @return string
     */
    public function getOrderID()
    {
        return (isset($this->data->order_no)) ? $this->data->order_no : null;
    }

    /**
     * Return the result page checksum
     * @since version 1.0.0
     * @access protected
     * @return string SHA1 hash
     */
    protected function generateChecksumForPage()
    {
        $hash_src = '';
        $this->data->merchant_id = $this->_merchantID;

        $hash_key = array('amount','currency', 'invoice_id', 'merchant_id',
            'trans_time', 'trans_date', 'status', 'ref_no', 'order_no');
        if ($this->data->status == '02') {
            $hash_key[] = 'failure_reason';
        }
        // 按 key 名进行顺序排序
        sort($hash_key);
        foreach ($hash_key as $key) {
            $hash_src .= $this->data->$key;
        }
        // 密钥放最前面
        $hash_src = $this->getSecretCode() . $hash_src;
        // sha256 算法
        $hash = hash('sha256', $hash_src);
        return strtoupper($hash);
    }

    /**
     * Return the get data
     * @since version 1.0.1
     * @access public
     * @return object
     */
    public function getResultData()
    {
        return $this->data;
    }

}

?>