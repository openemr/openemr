<?php

/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Controller;

use MyMailer;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Utils\ValidationUtils;
use OpenEMR\Modules\FaxSMS\BootstrapService;

/**
 * Class AppDispatch
 *
 * @package OpenEMR\Modules\FaxSMS\Controller
 */
abstract class AppDispatch
{
    const ACTION_DEFAULT = 'index';
    static $_apiService;
    static mixed $_apiModule;
    public string $authErrorDefault;
    public static $timeZone;
    protected CryptoGen $crypto;
    protected $_currentAction;
    protected $credentials;
    private $_request, $_response, $_query, $_post, $_server, $_cookies, $_session;
    protected $authUser;

    /**
     * @throws \Exception
     */
    public function __construct()
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
        $this->crypto = new CryptoGen();
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
                    $this->$action()
                );
            } else {
                $this->setHeader("HTTP/1.0 404 Not Found");
                die(xlt("Requested") . ' ' . text($action) . ' ' . xlt("or service is not found.") . '<br />' . xlt("Install or turn service on!"));
            }
        } else {
            // Not an internal route so pass on to current service index action.
            $this->setResponse(
                $this->{self::ACTION_DEFAULT}()
            );
        }
    }

    /**
     * Each service client must implement its own authenticate() method.
     * This is where we decide if the user has the right to use the client
     * service based on specific criteria required for its type, vendor and API.
     * At minimum, a specific ACL should be checked with
     * verifyAcl($sect = 'patients', $v = 'docs', $u = ''): bool.
     *
     * @return string|int|bool
     */
    abstract function authenticate(): string|int|bool;

    /**
     * @return string|bool
     */
    abstract function sendFax(): string|bool;

    /**
     * @return mixed
     */
    abstract function sendSMS(): mixed;

    /**
     * @return mixed
     */
    abstract function sendEmail(): mixed;

    /**
     * @return string|bool
     */
    abstract function fetchReminderCount(): string|bool;

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
     * @return EtherFaxActions|TwilioSMSClient|RCFaxClient|ClickatellSMSClient|EmailClient|SignalWireClient|void|null
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
    static function setApiService(string $type): void
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

    static function getServiceInstance($type)
    {
        $s = self::getServiceType();

        $factoryMap = [
            'sms' => [
                1 => fn() => new RCFaxClient(),
                2 => fn() => new TwilioSMSClient(),
                5 => fn() => new ClickatellSMSClient(),
            ],
            'fax' => [
                1 => fn() => new RCFaxClient(),
                3 => fn() => new EtherFaxActions(),
                6 => fn() => new SignalWireClient(),
            ],
            'email' => [
                4 => fn() => new EmailClient(),
            ],
            'voice' => [
                6 => fn() => new VoiceClient(),
            ],
        ];

        $factory = $factoryMap[$type][$s] ?? null;
        if (is_callable($factory)) {
            return $factory();
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
        if (self::$_apiModule == 'email') {
            return $GLOBALS['oe_enable_email'] ?? null;
        }
        if (self::$_apiModule == 'voice') {
            return $GLOBALS['oe_enable_voice'] ?? null;
        }

        http_response_code(404);
        die(xlt("Requested") . ' ' . text(self::$_apiModule) . ' ' . xlt("service is not found.") . '<br />' . xlt("Install or turn service on!") . '<br />');
    }

    /**
     * @return mixed
     */
    static function getModuleType(): mixed
    {
        if (empty(self::$_apiModule)) {
            self::$_apiModule = $_SESSION['oefax_current_module_type'] ?? null;
            if (empty(self::$_apiModule)) {
                self::$_apiModule = $_REQUEST['type'];
            }
        }

        return self::$_apiModule;
    }

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
            $phone = $this->formatPhoneForSave($this->getRequest('phone'));
            $password = $this->getRequest('password');
            $appkey = $this->getRequest('key');
            $appsecret = $this->getRequest('secret');
            $production = $this->getRequest('production');
            $smsNumber = $this->formatPhoneForSave($this->getRequest('smsnumber'));
            $smsMessage = $this->getRequest('smsmessage');
            $smsHours = $this->getRequest('smshours');
            $jwt = $this->getRequest('jwt');
            // SignalWire specific fields
            $spaceUrl = $this->getRequest('space_url');
            $projectId = $this->getRequest('project_id');
            $apiToken = $this->getRequest('api_token');
            $faxNumberRaw = $this->getRequest('fax_number');
            $faxNumber = !empty($faxNumberRaw) ? $this->formatPhoneForSave($faxNumberRaw) : '';

            $setup = [
                'username' => "$username",
                'extension' => "$ext",
                'account' => $account,
                'phone' => $phone,
                'password' => "$password",
                'appKey' => "$appkey",
                'appSecret' => "$appsecret",
                'server' => !$production ? 'https://platform.devtest.ringcentral.com' : "https://platform.ringcentral.com",
                'portal' => !$production ? "https://service.devtest.ringcentral.com/" : "https://service.ringcentral.com/",
                'smsNumber' => "$smsNumber",
                'production' => $production,
                'redirect_url' => $this->getRequest('redirect_url'),
                'smsHours' => $smsHours,
                'smsMessage' => $smsMessage,
                'jwt' => $jwt ?? '',
                // SignalWire credentials
                'space_url' => $spaceUrl ?? '',
                'project_id' => $projectId ?? '',
                'api_token' => $apiToken ?? '',
                'fax_number' => $faxNumber,
            ];
        }

        $vendor = self::getModuleVendor();
        $this->authUser = (int)$this->getSession('authUserID');
        $use = BootstrapService::usePrimaryAccount($this->authUser);
        if (!($GLOBALS['oerestrict_users'] ?? null)) {
            $this->authUser = 0;
        }
        if ($use) {
            $this->authUser = BootstrapService::getPrimaryUser();
        }
        if ((int)$this->getSession('editingUser') > 0) {
            $this->authUser = (int)$this->getSession('editingUser');
        }

        // encrypt for safety.
        $content = $this->crypto->encryptStandard(json_encode($setup));
        if (empty($vendor) || empty($setup)) {
            return xlt('Error: Missing vendor, user or credential items');
        }
        $sql = "INSERT INTO `module_faxsms_credentials` (`id`, `auth_user`, `vendor`, `credentials`)
            VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `auth_user`= ?, `vendor` = ?, `credentials`= ?, `updated` = NOW()";
        sqlStatement($sql, ['', $this->authUser, $vendor, $content, $this->authUser, $vendor, $content]);

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
     * @return string|null
     */
    static function getModuleVendor(): ?string
    {
        return match ((string)self::getServiceType()) {
            '1' => '_ringcentral',
            '2' => '_twilio',
            '3' => '_etherfax',
            '4' => '_email',
            '5' => '_clickatell',
            '6' => '_voice',
            default => null,
        };
    }

    public function getEmailSetup(): mixed
    {
        $vendor = '_email';
        $this->authUser = (int)$this->getSession('authUserID');
        if (!($GLOBALS['oerestrict_users'] ?? null)) {
            $this->authUser = 0;
        }
        $credentials = sqlQuery("SELECT * FROM `module_faxsms_credentials` WHERE `auth_user` = ? AND `vendor` = ?", [$this->authUser, $vendor]);

        if (empty($credentials)) {
            $credentials = [
                'sender_name' => $GLOBALS['patient_reminder_sender_name'],
                'sender_email' => $GLOBALS['patient_reminder_sender_email'],
                'notification_email' => $GLOBALS['practice_return_email_path'],
                'email_transport' => $GLOBALS['EMAIL_METHOD'],
                'smtp_host' => $GLOBALS['SMTP_HOST'],
                'smtp_port' => $GLOBALS['SMTP_PORT'],
                'smtp_user' => $GLOBALS['SMTP_USER'],
                'smtp_password' => $GLOBALS['SMTP_PASS'],
                'smtp_security' => $GLOBALS['SMTP_SECURE'],
                'notification_hours' => $GLOBALS['EMAIL_NOTIFICATION_HOUR'],
                'email_message' => $GLOBALS['EMAIL_MESSAGE'] ?? '',
            ];
            if (empty($credentials['email_message'] ?? '')) {
                $credentials['email_message'] = "A courtesy reminder for ***NAME*** \r\nFor the appointment scheduled on: ***DATE*** At: ***STARTTIME*** Until: ***ENDTIME*** \r\nWith: ***PROVIDER*** Of: ***ORG***\r\nPlease call if unable to attend.";
            }
            return $credentials;
        } else {
            $credentials = $credentials['credentials'];
        }

        $decrypt = $this->crypto->decryptStandard($credentials);
        $credentials = json_decode($decrypt, true);
        if (empty($credentials['email_message'] ?? '')) {
            $credentials['email_message'] = "A courtesy reminder for ***NAME*** \r\nFor the appointment scheduled on: ***DATE*** At: ***STARTTIME*** Until: ***ENDTIME*** \r\nWith: ***PROVIDER*** Of: ***ORG***\r\nPlease call if unable to attend.";
        }
        return $credentials;
    }

    public function saveEmailSetup($credentials): void
    {
        $vendor = '_email';
        $this->authUser = (int)$this->getSession('authUserID');
        if (!($GLOBALS['oerestrict_users'] ?? null)) {
            $this->authUser = 0;
        }
        $encoded = json_encode($credentials);
        $encrypted = $this->crypto->encryptStandard($encoded);
        sqlStatement(
            "INSERT INTO `module_faxsms_credentials` (auth_user, vendor, credentials, updated) VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE credentials = VALUES(credentials), updated = VALUES(updated)",
            [$this->authUser, $vendor, $encrypted]
        );
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
        $use = BootstrapService::usePrimaryAccount($this->authUser);
        if (!($GLOBALS['oerestrict_users'] ?? null)) {
            $this->authUser = 0;
        }
        if ($use) {
            $this->authUser = BootstrapService::getPrimaryUser();
        }
        if ((int)$this->getSession('editingUser') > 0) {
            $this->authUser = (int)$this->getSession('editingUser');
        }

        $credentials = sqlQuery("SELECT * FROM `module_faxsms_credentials` WHERE `auth_user` = ? AND `vendor` = ?", [$this->authUser, $vendor]);

        if (empty($credentials)) {
            return [
                'username' => '',
                'extension' => '',
                'password' => '',
                'account' => '',
                'phone' => '+1',
                'appKey' => '',
                'appSecret' => '',
                'server' => '',
                'portal' => '',
                'smsNumber' => '+1',
                'production' => '',
                'redirect_url' => '',
                'smsHours' => "50",
                'smsMessage' => "A courtesy reminder for ***NAME*** \r\nFor the appointment scheduled on: ***DATE*** At: ***STARTTIME*** Until: ***ENDTIME*** \r\nWith: ***PROVIDER*** Of: ***ORG***\r\nPlease call if unable to attend.",
                'jwt' => '',
                // SignalWire fields
                'space_url' => '',
                'project_id' => '',
                'api_token' => '',
                'fax_number' => ''
            ];
            return $credentials;
        } else {
            $credentials = $credentials['credentials'];
        }

        $decrypt = $this->crypto->decryptStandard($credentials);
        $decode = json_decode($decrypt, true);
        if (empty($decode['smsMessage'])) {
            $decode['smsMessage'] = "A courtesy reminder for ***NAME*** \r\nFor the appointment scheduled on: ***DATE*** At: ***STARTTIME*** Until: ***ENDTIME*** \r\nWith: ***PROVIDER*** Of: ***ORG***\r\nPlease call if unable to attend.";
        }
        return $decode;
    }

    /**
     * @return array
     */
    public static function getLoggedInUser(): array
    {
        $id = $_SESSION['authUserID'] ?? 1;
        $query = "SELECT fname, lname, fax, facility, username FROM users WHERE id = ?";
        $result = sqlQuery($query, [$id]);

        return $result;
    }

    /**
     *
     * @param $email
     * @return bool
     */
    public function validEmail($email): bool
    {
        return ValidationUtils::isValidEmail($email);
    }

    /**
     * This is available to all services
     * regardless if EmailClient is enabled.
     * @param        $email
     * @param        $from_name
     * @param        $body
     * @param string $subject
     * @param string $htmlContent
     * @return string
     */
    public function mailEmail($email, $from_name, $body, $subject = '', $htmlContent = ''): string
    {
        $status = 'Error: ' . xlt('Unknown error occurred');
        try {
            $mail = new MyMailer();
            $smtpEnabled = $mail::isConfigured();
            if (!$smtpEnabled) {
                $statusMsg = 'Error: ' . xlt("Mail was not sent. A SMTP client is not set up in Config Notifications!.");
                return js_escape($statusMsg);
            }
            $content = text($body) . "\n";
            $from_name = text($from_name);
            $from = $GLOBALS["practice_return_email_path"];
            $mail->AddReplyTo($from, $from_name);
            $mail->SetFrom($from, $from);
            $to = $email;
            $to_name = $email;
            $mail->AddAddress($to, $to_name);
            $subject = text($subject);
            $mail->Subject = $subject;
            $mail->Body = $content;
            if (!empty($htmlContent)) {
                $mail->MsgHTML(text($htmlContent));
                $mail->IsHTML(true);
            }
            if ($mail->Send()) {
                $status = $mail->Send() ? xlt("Email successfully sent.") : xlt("Error: Email failed") . text($mail->ErrorInfo);
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $status = 'Error: ' . $message;
        }
        return $status;
    }

    /**
     * @param $sect
     * @param $v
     * @param $u
     * @return bool
     */
    public function verifyAcl($sect = 'patients', $v = 'demo', $u = ''): bool
    {
        $ret = AclMain::aclCheckCore($sect, $v, $u);
        return $ret;
    }

    public function formatPhoneForSave($number): string
    {
        // this is U.S. only. need E-164
        $n = preg_replace('/[^0-9]/', '', (string) $number);
        $n = stripos((string) $n, '1') === 0 ? '+' . $n : '+1' . $n;
        return $n;
    }

    /**
     * @return null
     */
    protected function index()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getNotificationLog(): string
    {
        $type = $this->getRequest('type');
        $fromDate = $this->getRequest('datefrom');
        $toDate = $this->getRequest('dateto');

        try {
            $query = "SELECT notification_log.* FROM notification_log " .
                "WHERE UPPER(notification_log.type) = UPPER(?) " .
                "AND notification_log.dSentDateTime > ? AND notification_log.dSentDateTime < ? " .
                "ORDER BY notification_log.dSentDateTime DESC";
            $res = sqlStatement($query, [$type, $fromDate, $toDate]);

            $row = [];
            $cnt = 0;
            while ($nrow = sqlFetchArray($res)) {
                $row[] = $nrow;
                $cnt++;
            }

            $responseMsgs = '';
            foreach ($row as $value) {
                $adate = ($value['pc_eventDate'] . '::' . $value['pc_startTime']);
                $pinfo = str_replace("|||", " ", $value['patient_info']);
                $responseMsgs .= "<tr><td>" . text($value["pc_eid"]) . "</td><td>" . text($value["dSentDateTime"]) .
                    "</td><td>" . text($adate) . "</td><td>" . text($pinfo) . "</td><td>" . text($value["message"]) . "</td></tr>";
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            return 'Error: ' . text($message) . PHP_EOL;
        }

        return $responseMsgs;
    }

    /**
     * @return array|mixed
     */
    public function getCredentials(): mixed
    {
        return AppDispatch::getSetup();
    }
}
