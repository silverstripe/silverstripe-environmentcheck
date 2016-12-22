<?php

namespace SilverStripe\EnvironmentCheck;














use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Security\Member;
use SilverStripe\Security\BasicAuth;
use SilverStripe\Control\Director;
use SilverStripe\Security\Permission;
use SilverStripe\EnvironmentCheck\EnvironmentCheckSuite;
use SilverStripe\Control\Email\Email;
use SilverStripe\EnvironmentCheck\EnvironmentCheck;
use SilverStripe\Logging\Log;
use SilverStripe\Dev\Deprecation;
use SilverStripe\Core\Config\Config;
use SilverStripe\Control\RequestHandler;



/**
 * Provides an interface for checking the given EnvironmentCheckSuite.
 */
class EnvironmentChecker extends RequestHandler
{
    /**
     * @var array
     */
    private static $url_handlers = array(
        '' => 'index',
    );

    /**
     * @var string
     */
    protected $checkSuiteName;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var int
     */
    protected $errorCode = 500;

    /**
     * @var null|string
     */
    private static $to_email_address = null;

    /**
     * @var null|string
     */
    private static $from_email_address = null;

    /**
     * @var bool
     */
    private static $email_results = false;

    /**
     * @var bool Log results via {@link SS_Log}
     */
    private static $log_results_warning = false;

    /**
     * @var int Maps to {@link Zend_Log} levels. Defaults to Zend_Log::WARN
     */
    private static $log_results_warning_level = 4;

    /**
     * @var bool Log results via {@link SS_Log}
     */
    private static $log_results_error = false;

    /**
     * @var int Maps to {@link Zend_Log} levels. Defaults to Zend_Log::ALERT
     */
    private static $log_results_error_level = 1;

    /**
     * @param string $checkSuiteName
     * @param string $title
     */
    public function __construct($checkSuiteName, $title)
    {
        parent::__construct();
        
        $this->checkSuiteName = $checkSuiteName;
        $this->title = $title;
    }

    /**
     * @param string $permission
     *
     * @throws SS_HTTPResponse_Exception
     */
    public function init($permission = 'ADMIN')
    {
        // if the environment supports it, provide a basic auth challenge and see if it matches configured credentials
        if (defined('ENVCHECK_BASICAUTH_USERNAME') && defined('ENVCHECK_BASICAUTH_PASSWORD')) {
            if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
                // authenticate the input user/pass with the configured credentials
                if (
                    !(
                        $_SERVER['PHP_AUTH_USER'] == ENVCHECK_BASICAUTH_USERNAME
                        && $_SERVER['PHP_AUTH_PW'] == ENVCHECK_BASICAUTH_PASSWORD
                    )
                ) {
                    $response = new HTTPResponse(null, 401);
                    $response->addHeader('WWW-Authenticate', "Basic realm=\"Environment check\"");
                    // Exception is caught by RequestHandler->handleRequest() and will halt further execution
                    $e = new HTTPResponse_Exception(null, 401);
                    $e->setResponse($response);
                    throw $e;
                }
            } else {
                $response = new HTTPResponse(null, 401);
                $response->addHeader('WWW-Authenticate', "Basic realm=\"Environment check\"");
                // Exception is caught by RequestHandler->handleRequest() and will halt further execution
                $e = new HTTPResponse_Exception(null, 401);
                $e->setResponse($response);
                throw $e;
            }
        } else {
            if (!$this->canAccess(null, $permission)) {
                return $this->httpError(403);
            }
        }
    }

    /**
     * @param null|int|Member $member
     * @param string $permission
     *
     * @return bool
     *
     * @throws SS_HTTPResponse_Exception
     */
    public function canAccess($member = null, $permission = "ADMIN")
    {
        if (!$member) {
            $member = Member::currentUser();
        }

        if (!$member) {
            $member = BasicAuth::requireLogin('Environment Checker', $permission, false);
        }

        // We allow access to this controller regardless of live-status or ADMIN permission only
        // if on CLI.  Access to this controller is always allowed in "dev-mode", or of the user is ADMIN.
        if (
            Director::isDev()
            || Director::is_cli()
            || empty($permission)
            || Permission::checkMember($member, $permission)
        ) {
            return true;
        }

        // Extended access checks.
        // "Veto" style, return NULL to abstain vote.
        $canExtended = null;
        $results = $this->extend('canAccess', $member);
        if ($results && is_array($results)) {
            if (!min($results)) {
                return false;
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * @return SS_HTTPResponse
     */
    public function index()
    {
        $response = new HTTPResponse;
        $result = EnvironmentCheckSuite::inst($this->checkSuiteName)->run();

        if (!$result->ShouldPass()) {
            $response->setStatusCode($this->errorCode);
        }

        $resultText = $result->customise(array(
            "URL" => Director::absoluteBaseURL(),
            "Title" => $this->title,
            "Name" => $this->checkSuiteName,
            "ErrorCode" => $this->errorCode,
        ))->renderWith("SilverStripe\\EnvironmentCheck\\EnvironmentChecker");

        if ($this->config()->email_results && !$result->ShouldPass()) {
            $email = new Email($this->config()->from_email_address, $this->config()->to_email_address, $this->title, $resultText);
            $email->send();
        }

        // Optionally log errors and warnings individually
        foreach ($result->Details() as $detail) {
            if ($this->config()->log_results_warning && $detail->StatusCode == EnvironmentCheck::WARNING) {
                $this->log(
                    sprintf('EnvironmentChecker warning at "%s" check. Message: %s', $detail->Check, $detail->Message),
                    $this->config()->log_results_warning_level
                );
            } elseif ($this->config()->log_results_error && $detail->StatusCode == EnvironmentCheck::ERROR) {
                $this->log(
                    sprintf('EnvironmentChecker error at "%s" check. Message: %s', $detail->Check, $detail->Message),
                    $this->config()->log_results_error_level
                );
            }
        }

        // output the result as JSON if requested
        if (
            $this->getRequest()->getExtension() == 'json'
            || strpos($this->getRequest()->getHeader('Accept'), 'application/json') !== false
        ) {
            $response->setBody($result->toJSON());
            $response->addHeader('Content-Type', 'application/json');
            return $response;
        }

        $response->setBody($resultText);
        
        return $response;
    }

    /**
     * @param string $message
     * @param int $level
     */
    public function log($message, $level)
    {
        Log::log($message, $level);
    }

    /**
     * Set the HTTP status code that should be returned when there's an error.
     *
     * @param int $errorCode
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
    }

    /**
     * @deprecated
     * @param string $from
     */
    public static function set_from_email_address($from)
    {
        Deprecation::notice('2.0', 'Use config API instead');
        Config::inst()->update('SilverStripe\\EnvironmentCheck\\EnvironmentChecker', 'from_email_address', $from);
    }

    /**
     * @deprecated
     * @return null|string
     */
    public static function get_from_email_address()
    {
        Deprecation::notice('2.0', 'Use config API instead');
        return Config::inst()->get('SilverStripe\\EnvironmentCheck\\EnvironmentChecker', 'from_email_address');
    }

    /**
     * @deprecated
     * @param string $to
     */
    public static function set_to_email_address($to)
    {
        Deprecation::notice('2.0', 'Use config API instead');
        Config::inst()->update('SilverStripe\\EnvironmentCheck\\EnvironmentChecker', 'to_email_address',  $to);
    }

    /**
     * @deprecated
     * @return null|string
     */
    public static function get_to_email_address()
    {
        Deprecation::notice('2.0', 'Use config API instead');
        return Config::inst()->get('SilverStripe\\EnvironmentCheck\\EnvironmentChecker', 'to_email_address');
    }

    /**
     * @deprecated
     * @param bool $results
     */
    public static function set_email_results($results)
    {
        Deprecation::notice('2.0', 'Use config API instead');
        Config::inst()->update('SilverStripe\\EnvironmentCheck\\EnvironmentChecker', 'email_results', $results);
    }

    /**
     * @deprecated
     * @return bool
     */
    public static function get_email_results()
    {
        Deprecation::notice('2.0', 'Use config API instead');
        return Config::inst()->get('SilverStripe\\EnvironmentCheck\\EnvironmentChecker', 'email_results');
    }
}
