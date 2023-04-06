<?php

/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Controller;

use Document;
use MyMailer;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Session\SessionUtil;

/**
 * Class AppDispatch
 *
 * @package OpenEMR\Modules\FaxSMS\Controller
 */
abstract class AppDispatch
{
    const ACTION_DEFAULT = 'index';
    static $_apiService;
    static $_apiModule;
    public string $authErrorDefault;
    public static $timeZone;
    protected $crypto;
    protected $_currentAction;
    private $_request, $_response, $_query, $_post, $_server, $_cookies, $_session;
    private $authUser;

    /**
     * @throws \Exception
     */
    public function __construct($type = null)
    {
        $this->_request = &$_REQUEST;
        $this->_query = &$_GET;
        $this->_post = &$_POST;
        $this->_server = &$_SERVER;
        $this->_cookies = &$_COOKIE;
        $this->_session = &$_SESSION;
        $this->authErrorDefault = xlt('Error: Authentication Service Denies Access or Not Authorised. Lacking valid credentials or User permissions.');
        $this->authUser = (int)$this->getSession('authUserID');
        if (empty(self::$_apiModule)) {
            self::$_apiModule = $_REQUEST['type'] ?? $_SESSION["oefax_current_module_type"] ?? null;
        }
        $this->dispatchActions();
        $this->render();
    }

    /**
     * @return void
     */
    private function dispatchActions(): void
    {
        $action = $this->getQuery('_ACTION_COMMAND');
        $route = explode('/', ($action ?? ''));
        $serviceType = $this->getQuery('type');
        if (count($route ?? []) === 2) {
            $serviceType = $route[0];
            $action = $route[1] ?: $action;
        }
        if (empty($serviceType)) {
            $serviceType = $_REQUEST['type'] ?? $_SESSION["oefax_current_module_type"] ?? null;
        }
        if (!empty($serviceType)) {
            self::setModuleType($serviceType);
        }
        $this->_currentAction = $action;
        if ($action) {
            // route it if direct call
            if (method_exists($this, $action)) {
                $this->setResponse(
                    call_user_func(array($this, $action), array())
                );
            } else {
                $this->setHeader("HTTP/1.0 404 Not Found");
                die(xlt("Requested") . ' ' . text($action) . ' ' . xlt("or service is not found.") . '<br />' . xlt("Install or turn service on!"));
            }
        } else {
            // Not an internal route so pass on to current service index action..
            $this->setResponse(
                call_user_func(array($this, self::ACTION_DEFAULT), array())
            );
        }
    }

    /**
     * @param $param
     * @param $default
     * @return mixed|null
     */
    public function getSession($param = null, $default = null): mixed
    {
        if ($param) {
            return $_SESSION[$param] ?? $default;
        }

        return $this->_session;
    }

    /**
     * @param $param
     * @param $default
     * @return mixed|null
     */
    public function getQuery($param = null, $default = null): mixed
    {
        if ($param) {
            return $this->_query[$param] ?? $default;
        }

        return $this->_query;
    }

    /**
     * @param $content
     * @return void
     */
    public function setResponse($content): void
    {
        $this->_response = $content;
    }

    /**
     * @param $params
     * @return $this
     */
    public function setHeader($params): static
    {
        if (!headers_sent()) {
            if (is_scalar($params)) {
                header($params);
            } else {
                foreach ($params as $key => $value) {
                    header(sprintf('%s: %s', $key, $value));
                }
            }
        }

        return $this;
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function render(): void
    {
        if ($this->_response) {
            if (is_scalar($this->_response)) {
                echo $this->_response;
            } else {
                throw new \Exception(xlt('Response content must be scalar'));
            }

            exit;
        }
    }

    /**
     * This is where we decide which Api to use.
     *
     * @param string $type
     * @return EtherFaxActions|RCFaxClient|TwilioSMSClient|void|null
     */
    static function getApiService(string $type)
    {
        try {
            if (empty($type)) {
                $type = $_REQUEST['type'] ?? $_SESSION["oefax_current_module_type"] ?? null;
            }
            self::setModuleType($type);
            self::$_apiService = self::getServiceInstance($type);
            return self::$_apiService;
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }

    /**
     * This is where we decide which Api to use.
     *
     * @param string $type
     * @return void
     */
    static function setApiService(string $type)
    {
        try {
            if (empty($type)) {
                $type = $_REQUEST['type'] ?? $_SESSION["oefax_current_module_type"] ?? null;
            }
            self::setModuleType($type);
            self::$_apiService = self::getServiceInstance($type);
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }

    /**
     * @param $type
     * @return void
     */
    static function setModuleType($type): void
    {
        $_SESSION['oefax_current_module_type'] = $type;
        self::$_apiModule = $type;
    }

    /**
     * @param $type
     * @return EtherFaxActions|RCFaxClient|TwilioSMSClient|void
     */
    private static function getServiceInstance($type)
    {
        $s = self::getServiceType();
        if ($type == 'sms') {
            switch ($s) {
                case 0:
                    break;
                case 1:
                    // for new service in future
                    break;
                case 2:
                    return new TwilioSMSClient();
            }
        } elseif ($type == 'fax') {
            switch ($s) {
                case 0:
                    break;
                case 1:
                    // for new service in future
                    break;
                case 3:
                    return new EtherFaxActions();
            }
        }

        http_response_code(404);
        die(xlt("Requested") . ' ' . text($type) . ' ' . xlt("service is not found.") . '<br />' . xlt("Install or turn service on!"));
    }

    /**
     * @return int|mixed
     */
    static function getServiceType(): mixed
    {
        if (empty(self::$_apiModule ?? null)) {
            self::$_apiModule = $_SESSION['oefax_current_module_type'] ?? null;
            if (empty(self::$_apiModule)) {
                self::$_apiModule = $_REQUEST['type'];
            }
        }
        if (self::$_apiModule == 'sms') {
            return $GLOBALS['oefax_enable_sms'] ?? null;
        }
        if (self::$_apiModule == 'fax') {
            return $GLOBALS['oefax_enable_fax'] ?? null;
        }

        http_response_code(404);
        die(xlt("Requested") . ' ' . text(self::$_apiModule) . ' ' . xlt("service is not found.") . '<br />' . xlt("Install or turn service on!") . '<br />');
    }

    /**
     * @return mixed
     */
    static function getModuleType(): mixed
    {
        return self::$_apiModule;
    }

    /**
     * @return string|bool
     */
    abstract function sendFax(): string|bool;

    /**
     * @return mixed
     */
    abstract function sendSMS(): mixed;

    /**
     * @return string|bool
     */
    abstract function fetchReminderCount(): string|bool;

    /**
     * @param $param
     * @param $default
     * @return mixed|null
     */
    public function getPost($param = null, $default = null): mixed
    {
        if ($param) {
            return $this->_post[$param] ?? $default;
        }

        return $this->_post;
    }

    /**
     * @param $param
     * @param $default
     * @return mixed|null
     */
    public function getServer($param = null, $default = null): mixed
    {
        if ($param) {
            return $this->_server[$param] ?? $default;
        }

        return $this->_server;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setSession($key, $value): static
    {
        // ensure write is allowed by using utility.
        SessionUtil::setSession($key, $value);
        return $this;
    }

    /**
     * @param array $setup
     * @return string
     */
    protected function saveSetup(array $setup = []): string
    {
        if (empty($setup)) {
            $username = $this->getRequest('username');
            $ext = $this->getRequest('extension');
            $account = $this->getRequest('account');
            $phone = $this->getRequest('phone');
            $password = $this->getRequest('password');
            $appkey = $this->getRequest('key');
            $appsecret = $this->getRequest('secret');
            $production = $this->getRequest('production');
            $smsNumber = $this->getRequest('smsnumber');
            $smsMessage = $this->getRequest('smsmessage');
            $smsHours = $this->getRequest('smshours');
            $setup = array(
                'username' => "$username",
                'extension' => "$ext",
                'account' => $account,
                'phone' => $phone,
                'password' => "$password",
                'appKey' => "$appkey",
                'appSecret' => "$appsecret",
                'server' => "",
                'portal' => "",
                'smsNumber' => "$smsNumber",
                'production' => $production,
                'redirect_url' => "",
                'smsHours' => $smsHours,
                'smsMessage' => $smsMessage
            );
        }

        $vendor = self::getModuleVendor();
        $this->authUser = (int)$this->getSession('authUserID') ?? 0;
        if (!($GLOBALS['oerestrict_users'] ?? null)) {
            $this->authUser = 0;
        }
        // encrypt for safety.
        $content = $this->crypto->encryptStandard(json_encode($setup));
        if (empty($vendor) || empty($setup)) {
            return xlt('Error: Missing vendor, user or credential items');
        }
        $sql = "INSERT INTO `module_faxsms_credentials` (`id`, `auth_user`, `vendor`, `credentials`) 
            VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `auth_user`= ?, `vendor` = ?, `credentials`= ?, `updated` = NOW()";
        sqlStatement($sql, array('', $this->authUser, $vendor, $content, $this->authUser, $vendor, $content));

        return xlt('Save Success');
    }

    /**
     * @param $param
     * @param $default
     * @return mixed|null
     */
    public function getRequest($param = null, $default = null): mixed
    {
        if ($param) {
            return $this->_request[$param] ?? $default;
        }

        return $this->_request;
    }

    /**
     * @return mixed
     */
    static function getModuleVendor(): mixed
    {
        switch ((string)self::getServiceType()) {
            case '1':
                break;
            case '2':
                return '_twilio';
            case '3':
                return '_etherfax';
        }
        return null;
    }

    /**
     * Common credentials storage between services
     * the service class will set specific credential.
     *
     * @return array|mixed
     */
    protected function getSetup(): mixed
    {
        $vendor = self::getModuleVendor();
        $this->authUser = (int)$this->getSession('authUserID');
        if (!($GLOBALS['oerestrict_users'] ?? null)) {
            $this->authUser = 0;
        }
        $credentials = sqlQuery("SELECT * FROM `module_faxsms_credentials` WHERE `auth_user` = ? AND `vendor` = ?", array($this->authUser, $vendor));

        if (empty($credentials)) {
            $credentials = array(
                'username' => '',
                'extension' => '',
                'password' => '',
                'account' => '',
                'phone' => '',
                'appKey' => '',
                'appSecret' => '',
                'server' => '',
                'portal' => '',
                'smsNumber' => '',
                'production' => '',
                'redirect_url' => '',
                'smsHours' => "50",
                'smsMessage' => "A courtesy reminder for ***NAME*** \r\nFor the appointment scheduled on: ***DATE*** At: ***STARTTIME*** Until: ***ENDTIME*** \r\nWith: ***PROVIDER*** Of: ***ORG***\r\nPlease call if unable to attend.",
            );
            return $credentials;
        } else {
            $credentials = $credentials['credentials'];
        }

        return json_decode($this->crypto->decryptStandard($credentials), true);
    }

    /**
     * @return array
     */
    public static function getLoggedInUser(): array
    {
        $id = $_SESSION['authUserID'] ?? 1;
        $query = "SELECT fname, lname, fax, facility, username FROM users WHERE id = ?";
        $result = sqlQuery($query, array($id));

        return $result;
    }

    /**
     * @return string|false
     */
    public function getPatientDetails(): bool|string
    {
        $id = $this->getRequest('pid');
        $query = "SELECT fname, lname, phone_cell FROM Patient_data WHERE pid = ?";
        $result = sqlQuery($query, array($id));

        return json_encode($result);
    }

    /**
     * @param       $email
     * @param       $body
     * @param       $file
     * @param array $user
     * @return string
     */
    public function emailDocument($email, $body, $file, $user = []): string
    {
        $from_name = ($user['fname'] ?? '') . ' ' . ($user['lname'] ?? '');
        $desc = xlt("Comment") . ":\n" . text($body) . "\n" . xlt("This email has an attached fax document.");
        $mail = new MyMailer();
        $from_name = text($from_name);
        $from =  $GLOBALS["practice_return_email_path"];
        $mail->AddReplyTo($from, $from_name);
        $mail->SetFrom($from, $from);
        $to = $email ;
        $to_name = $email;
        $mail->AddAddress($to, $to_name);
        $subject = xlt("Forwarded Fax Document");
        $mail->Subject = $subject;
        $mail->Body = $desc;
        $mail->AddAttachment($file);
        if ($mail->Send()) {
            $status = xlt("Email successfully sent.");
        } else {
            $status =  xlt("Error: Email failed") . text($mail->ErrorInfo);
        }
        return $status;
    }

    /**
     * @param $sect
     * @param $v
     * @param $u
     * @return bool
     */
    public function verifyAcl($sect = 'admin', $v = 'docs', $u = ''): bool
    {
        return AclMain::aclCheckCore($sect, $v, $u);
    }

    /**
     * @return null
     */
    private function indexAction()
    {
        return null;
    }
}
