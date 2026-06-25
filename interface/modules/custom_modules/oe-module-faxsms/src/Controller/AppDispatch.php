<?php

/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General public License 3
 */

namespace OpenEMR\Modules\FaxSMS\Controller;

use Exception;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use MyMailer;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Utils\ValidationUtils;
use OpenEMR\Common\ValueObjects\PhoneNumber;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\FaxSMS\BootstrapService;
use OpenEMR\Modules\FaxSMS\Enums\ServiceType;
use OpenEMR\Modules\FaxSMS\Service\CredentialsRepository;
use OpenEMR\Modules\FaxSMS\Service\ServiceFactory;
use OpenEMR\Services\PatientPortalService;
use RuntimeException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Throwable;

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
    protected CryptoInterface $crypto;
    protected $_currentAction;
    protected $credentials;
    private $_request, $_response, $_query, $_post, $_server, $_cookies;
    private ?SessionInterface $_session = null;
    private ?CredentialsRepository $_credentialsRepository = null;
    protected $authUser;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->_request = &$_REQUEST;
        $this->_query = &$_GET;
        $this->_post = &$_POST;
        $this->_server = &$_SERVER;
        $this->_cookies = &$_COOKIE;
        $this->_session ??= SessionWrapperFactory::getInstance()->getActiveSession();
        $this->authErrorDefault = xlt('Error: Authentication Service Denies Access or Not Authorised. Lacking valid credentials or User permissions.');
        $this->authUser = (int)$this->getSession('authUserID');
        if (empty(self::$_apiModule)) {
            self::$_apiModule = $_REQUEST['type'] ?? $this->session()->get('oefax_current_module_type') ?? null;
        }
        $this->crypto = ServiceContainer::getCrypto();
        // Background-service workers (bin/console background:services run,
        // ajax/execute_background_services.php under cron) bootstrap with
        // $ignoreAuth = true because there is no interactive session. In
        // that context there is no authUser for AclMain to check against,
        // so verifyAcl() would always fail and AccessDeniedHelper::deny()
        // would exit(1) before the reminder job could run — the silent
        // background-service failure reported in issue #11827.
        $ignoreAuth = OEGlobalsBag::getInstance()->getBoolean('ignoreAuth');
        if (!$ignoreAuth && !$this->verifyAcl()) {
            AccessDeniedHelper::deny('FaxSMS module access denied');
        }
        // Construction no longer routes a request. Action routing + rendering
        // is an explicit step (dispatch()), invoked only by the front
        // controller (index.php). This keeps the module ACL gate above as the
        // single protective side effect of construction while preventing a
        // request-specified action from executing merely because a client was
        // instantiated in a non-request context (event listeners, background
        // jobs), and gives us one chokepoint for the action allowlist and CSRF.
    }

    /**
     * Allowlist of externally-routable actions.
     *
     * Routing previously accepted any method that existed on the resolved
     * client (method_exists), which made internal helpers
     * (saveSetup/getSetup/getCredentials/mailEmail/setSession/...) an
     * HTTP-reachable surface. Only the names below may be dispatched; anything
     * else is a 404. 'csrf' => true additionally requires a valid
     * 'contact-form' CSRF token (state-changing actions).
     *
     * Keys match exactly what the UI sends. PHP resolves method names
     * case-insensitively, so 'makeRingoutCall' reaches makeRingOutCall().
     *
     * @var array<string, array{csrf: bool}>
     */
    private const ROUTABLE_ACTIONS = [
        // Default action (most clients render nothing here).
        'index'                  => ['csrf' => false],
        // Read-only / idempotent endpoints: module ACL only.
        'apiFetchPatientDetails' => ['csrf' => false],
        'getUser'                => ['csrf' => false],
        'getPending'             => ['csrf' => false],
        'fetchSMSList'           => ['csrf' => false],
        'fetchEmailList'         => ['csrf' => false],
        'fetchTextMessage'       => ['csrf' => false],
        'getCallLogs'            => ['csrf' => false],
        'getNotificationLog'     => ['csrf' => false],
        // State-changing endpoints whose emitter (the contact dialog) already
        // posts the 'contact-form' token: CSRF enforced now.
        'sendFax'                => ['csrf' => true],
        'sendSMS'                => ['csrf' => true],
        'sendEmail'              => ['csrf' => true],
        'forwardFax'             => ['csrf' => true],
        // State-changing endpoints. Their emitters (the setup pages and
        // messageUI) now post the 'contact-form' token as 'csrf_token_form',
        // so CSRF is enforced here. disposeDocument's GET download branch
        // appends the same token as a query parameter.
        'saveSetup'              => ['csrf' => true],
        // viewFax has a delete/download branch (getDocument), so it is
        // state-changing; messageUI posts the token as 'csrf_token_form'.
        'viewFax'                => ['csrf' => true],
        'assignFax'              => ['csrf' => true],
        'disposeDocument'        => ['csrf' => true],
        'faxProcessUploads'      => ['csrf' => true],
        'makeRingoutCall'        => ['csrf' => true],
        'install'                => ['csrf' => true],
    ];

    /**
     * Front-controller entry point. Resolves the route, selects the active
     * service module, enforces the action allowlist and (for state-changing
     * actions) CSRF, invokes the action, and renders the scalar response.
     * Called by index.php after the service has been constructed. Construction
     * itself no longer routes, so instantiating a client in a non-request
     * context cannot execute a request-specified action.
     *
     * @return void
     */
    public function dispatch(): void
    {
        [$serviceType, $action] = $this->resolveRoute();
        if (!empty($serviceType)) {
            self::setModuleType($serviceType);
        }
        $this->_currentAction = $action ?: self::ACTION_DEFAULT;
        $this->routeAction($this->_currentAction);
        $this->render();
    }

    /**
     * Derive [serviceType, action] from the request exactly as the legacy
     * dispatcher did: an optional "service/action" slash form in
     * _ACTION_COMMAND, otherwise the action verb plus ?type=, falling back to
     * the session's current module type. Behavior is intentionally unchanged
     * so the upstream URL rewriting that feeds _ACTION_COMMAND keeps working.
     *
     * @return array{0: string|null, 1: string|null}
     */
    private function resolveRoute(): array
    {
        $action = $this->getQuery('_ACTION_COMMAND');
        $route = explode('/', ($action ?? ''));
        $serviceType = $this->getQuery('type');
        if (count($route) === 2) {
            $serviceType = $route[0];
            $action = $route[1] ?: $action;
        }
        if (empty($serviceType)) {
            $serviceType = $_REQUEST['type'] ?? $this->session()->get('oefax_current_module_type') ?? null;
        }

        $serviceType = is_scalar($serviceType) ? (string) $serviceType : null;
        $action = is_scalar($action) ? (string) $action : null;

        return [$serviceType, $action];
    }

    /**
     * Invoke a single allowlisted action. The action must be present in
     * ROUTABLE_ACTIONS AND implemented on the resolved client; anything else
     * is a 404, so internal helpers are never HTTP-reachable. State-changing
     * actions additionally require a valid CSRF token.
     *
     * @param string $action
     * @return void
     */
    private function routeAction(string $action): void
    {
        $spec = self::ROUTABLE_ACTIONS[$action] ?? null;
        if ($spec === null || !method_exists($this, $action)) {
            $this->setHeader("HTTP/1.0 404 Not Found");
            throw new RuntimeException(
                xlt("Requested") . ' ' . text($action) . ' '
                . xlt("or service is not found.") . ' ' . xlt("Install or turn service on!")
            );
        }
        if ($spec['csrf']) {
            $this->assertCsrf();
        }
        $this->setResponse(
            $this->$action()
        );
    }

    /**
     * Enforce the module CSRF token for state-changing actions. The contact
     * dialog posts this token as 'csrf_token_form' bound to the 'contact-form'
     * id; csrfNotVerified() emits the standard error and exits on mismatch.
     *
     * @return void
     */
    private function assertCsrf(): void
    {
        $tokenRaw = $this->getRequest('csrf_token_form', '');
        $token = is_string($tokenRaw) ? $tokenRaw : '';
        if (!CsrfUtils::verifyCsrfToken($token, $this->session(), 'contact-form')) {
            CsrfUtils::csrfNotVerified();
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
     * Channel send operations are no longer part of the base contract. A client
     * declares the channels it actually supports through the capability
     * interfaces — FaxChannelInterface (sendFax), SmsChannelInterface (sendSMS)
     * and EmailChannelInterface (sendEmail) — and implements only those, so a
     * new vendor is never forced to stub verbs it cannot perform.
     *
     * fetchReminderCount() defaults to a no-op so it is likewise optional;
     * channel clients that surface a pending-reminder count override it.
     *
     * @return string|bool
     */
    public function fetchReminderCount(): string|bool
    {
        return false;
    }

    /**
     * @param string|null $param
     * @param mixed|null  $default
     * @return mixed|null
     */
    public function getSession(?string $param = null, mixed $default = null): mixed
    {
        $session = $this->session();
        if ($param) {
            return $session->get($param) ?? $default;
        }

        return $session;
    }

    /**
     * Lazily resolve the active session wrapper.
     *
     * Service clients reach this method via the static factory path
     * (AppDispatch::getApiService → new RCFaxClient → getCredentials → getSession)
     * *before* parent::__construct() has run, so the property is not yet set.
     */
    private function session(): SessionInterface
    {
        return $this->_session ??= SessionWrapperFactory::getInstance()->getActiveSession();
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
     * @throws Exception
     */
    private function render(): void
    {
        if ($this->_response) {
            if (is_scalar($this->_response)) {
                echo $this->_response;
            } else {
                throw new Exception(xlt('Response content must be scalar'));
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
        if ($type === '') {
            $session = SessionWrapperFactory::getInstance()->getActiveSession();
            $type = $_REQUEST['type'] ?? $session->get('oefax_current_module_type') ?? null;
        }
        self::setModuleType($type);
        self::$_apiService = self::getServiceInstance($type);
        return self::$_apiService;
    }

    /**
     * This is where we decide which Api to use.
     *
     * @param string $type
     * @return void
     */
    static function setApiService(string $type): void
    {
        if ($type === '') {
            $session = SessionWrapperFactory::getInstance()->getActiveSession();
            $type = $_REQUEST['type'] ?? $session->get('oefax_current_module_type') ?? null;
        }
        self::setModuleType($type);
        self::$_apiService = self::getServiceInstance($type);
    }

    /**
     * @param $type
     * @return void
     */
    static function setModuleType($type): void
    {
        SessionUtil::setSession('oefax_current_module_type', $type);
        self::$_apiModule = $type;
    }

    static function getServiceInstance($type)
    {
        $moduleType = is_scalar($type) ? (string) $type : '';
        return ServiceFactory::create($moduleType, self::getServiceType());
    }

    /**
     * @return int|mixed
     */
    static function getServiceType(): mixed
    {
        if (empty(self::$_apiModule ?? null)) {
            $session = SessionWrapperFactory::getInstance()->getActiveSession();
            self::$_apiModule = $session->get('oefax_current_module_type') ?? null;
            if (empty(self::$_apiModule)) {
                self::$_apiModule = $_REQUEST['type'];
            }
        }
        if (self::$_apiModule === 'sms') {
            return OEGlobalsBag::getInstance()->get('oefax_enable_sms') ?? null;
        }
        if (self::$_apiModule === 'fax') {
            return OEGlobalsBag::getInstance()->get('oefax_enable_fax') ?? null;
        }
        if (self::$_apiModule === 'email') {
            return OEGlobalsBag::getInstance()->get('oe_enable_email') ?? null;
        }
        if (self::$_apiModule === 'voice') {
            return OEGlobalsBag::getInstance()->get('oe_enable_voice') ?? null;
        }

        throw new RuntimeException(
            xlt("Requested") . ' ' . text(self::$_apiModule) . ' '
            . xlt("service is not found.") . ' ' . xlt("Install or turn service on!")
        );
    }

    /**
     * @return mixed
     */
    static function getModuleType(): mixed
    {
        if (empty(self::$_apiModule)) {
            $session = SessionWrapperFactory::getInstance()->getActiveSession();
            self::$_apiModule = $session->get('oefax_current_module_type') ?? null;
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
     * @param string $key
     * @param mixed  $value
     * @return $this
     */
    public function setSession(string $key, $value): static
    {
        // ensure write is allowed by using utility.
        SessionUtil::setSession($key, $value);
        return $this;
    }

    /**
     * Resolve the credential owner (auth_user) used for credential reads and
     * writes. This consolidates logic that was previously duplicated across
     * saveSetup(), getSetup(), getEmailSetup() and saveEmailSetup(), preserving
     * the original behavior exactly:
     *  - Start from the session authUserID.
     *  - usePrimaryAccount() is evaluated against that original id *before* the
     *    oerestrict reset (ordering is significant).
     *  - When per-user restriction (oerestrict_users) is off, ownership
     *    collapses to the shared account (0).
     *  - The full resolution then promotes to the configured primary user and
     *    lets an explicit editingUser override. The email vendor used the
     *    simpler id-or-shared resolution only; pass false for that path.
     *
     * @param bool $resolvePrimaryAndEditing
     * @return int
     */
    private function resolveCredentialOwner(bool $resolvePrimaryAndEditing = true): int
    {
        $authUser = (int)$this->getSession('authUserID');
        $usePrimary = $resolvePrimaryAndEditing && BootstrapService::usePrimaryAccount($authUser);
        if (!(OEGlobalsBag::getInstance()->get('oerestrict_users') ?? null)) {
            $authUser = 0;
        }
        if ($usePrimary) {
            $authUser = (int) BootstrapService::getPrimaryUser();
        }
        if ($resolvePrimaryAndEditing && (int)$this->getSession('editingUser') > 0) {
            $authUser = (int)$this->getSession('editingUser');
        }

        return $authUser;
    }

    /**
     * @param array $setup
     * @return string
     */
    protected function saveSetup(array $setup = []): string
    {
        if (empty($setup)) {
            $setup = $this->buildSetupFromRequest();
        }
        $this->authUser = $this->resolveCredentialOwner();

        return $this->credentialsRepository()->storeSetup(self::getModuleVendor(), $this->authUser, $setup);
    }

    /**
     * Assemble a service-credential set from the current request. Kept on the
     * controller because it reads request input; persistence is delegated to
     * CredentialsRepository.
     *
     * @return array<string, mixed>
     */
    private function buildSetupFromRequest(): array
    {
        $username = $this->getRequest('username');
        $ext = $this->getRequest('extension');
        $account = $this->getRequest('account');
        $phone = $this->formatPhone($this->getRequest('phone') ?? '');
        $password = $this->getRequest('password');
        $appkey = $this->getRequest('key');
        $appsecret = $this->getRequest('secret');
        $production = $this->getRequest('production');
        // Voice-only: opt-in flag for RingCentral call-event tracking (webhook).
        // Harmless null for every other vendor whose form does not post it.
        $enableEvents = $this->getRequest('enable_events');
        $smsNumber = $this->formatPhone($this->getRequest('smsnumber') ?? '');
        $smsMessage = $this->getRequest('smsmessage');
        $smsHours = $this->getRequest('smshours');
        $jwt = $this->getRequest('jwt');
        // SignalWire specific fields
        $spaceUrl = $this->getRequest('space_url');
        $projectId = $this->getRequest('project_id');
        $apiToken = $this->getRequest('api_token');
        $faxNumber = $this->formatPhone($this->getRequest('fax_number') ?? '');

        return [
            'username' => "$username",
            'extension' => "$ext",
            'account' => $account,
            'phone' => $phone,
            'password' => "$password",
            'appKey' => "$appkey",
            'appSecret' => "$appsecret",
            // RingCentral retired the devtest sandbox at the end of 2024;
            // production is the only valid host, and RCFaxClient hardcodes
            // it regardless of what is stored here.
            'server' => "https://platform.ringcentral.com",
            'portal' => "https://service.ringcentral.com/",
            'smsNumber' => "$smsNumber",
            'production' => $production,
            'enable_events' => $enableEvents,
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

    /**
     * REST endpoint for the module contact dialog.
     *
     * Returns a JSON-encoded object — either the patient details (when ?pid
     * resolves to a real patient) or an empty object {} (when the id is
     * missing, non-numeric, or doesn't resolve). The contract is "always a
     * JSON object string" so the JS caller can `JSON.parse` and access
     * fields without first defending against false/null.
     */
    public function apiFetchPatientDetails(): string
    {
        $idRaw = $this->getRequest('pid');
        $id = is_numeric($idRaw) ? (int)$idRaw : 0;
        $result = $id > 0
            ? (new PatientPortalService())->getPatientDetails($id)
            : null;

        try {
            return json_encode($result ?: new \stdClass(), JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            ServiceContainer::getLogger()->error(
                'apiFetchPatientDetails: failed to encode response',
                ['exception' => $e]
            );
            return '{}';
        }
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
        $service = ServiceType::fromValue(self::getServiceType());
        return $service->getVendorKey() ?: null;
    }

    public function getEmailSetup(): mixed
    {
        $this->authUser = $this->resolveCredentialOwner(false);

        return $this->credentialsRepository()->loadEmailSetup($this->authUser);
    }

    public function saveEmailSetup($credentials): void
    {
        $this->authUser = $this->resolveCredentialOwner(false);

        $this->credentialsRepository()->storeEmailSetup($this->authUser, $credentials);
    }

    /**
     * Common credentials storage between services
     * the service class will set specific credential.
     *
     * @return array|mixed
     */
    protected function getSetup(): mixed
    {
        $this->authUser = $this->resolveCredentialOwner();

        return $this->credentialsRepository()->loadSetup(self::getModuleVendor(), $this->authUser);
    }

    /**
     * Lazily resolve the credentials repository. One instance per client is
     * sufficient; it resolves its own crypto from the service container.
     *
     * @return CredentialsRepository
     */
    private function credentialsRepository(): CredentialsRepository
    {
        return $this->_credentialsRepository ??= new CredentialsRepository();
    }

    /**
     * @return array
     */
    public static function getLoggedInUser(): array
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $id = $session->get('authUserID') ?? 1;
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
     *
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
            $from = OEGlobalsBag::getInstance()->getString("practice_return_email_path");
            $mail->addReplyTo($from, $from_name);
            $mail->setFrom($from, $from);
            $to = $email;
            $to_name = $email;
            $mail->addAddress($to, $to_name);
            $subject = text($subject);
            $mail->Subject = $subject;
            $mail->Body = $content;
            if (!empty($htmlContent)) {
                $mail->msgHTML(text($htmlContent));
                $mail->isHTML(true);
            }
            $status = $mail->send() ? xlt("Email successfully sent.") : xlt("Error: Email failed") . ' ' . text($mail->ErrorInfo);
        } catch (Throwable $e) {
            ServiceContainer::getLogger()->error(
                'mailEmail: send failed',
                ['exception' => $e]
            );
            $status = xlt('Error: Unable to send email at this time.');
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

    /**
     * Format a phone number to E.164 format for API calls.
     *
     * @param string $number The phone number to format
     * @return string E.164 formatted number (e.g., +12125551234) or empty string if invalid
     */
    public function formatPhone(string $number): string
    {
        $parsed = PhoneNumber::tryParse($number, $this->defaultPhoneRegion());
        return $parsed ? $parsed->toE164() : '';
    }

    /**
     * Resolve the default phone region for parsing bare national numbers.
     *
     * A number that already carries a "+CC" prefix is self-describing and the
     * region is ignored; this only governs how a number entered without a
     * country code is interpreted. The value is sourced from OpenEMR's Locale
     * global "Telephone Country Code" ($GLOBALS['phone_country_code']), which
     * historically stores a numeric dialing code (1, 44, ...). That is mapped
     * to the ISO 3166-1 alpha-2 region libphonenumber expects ("US", "GB").
     * An already-ISO value is accepted as-is, and anything unset or unmappable
     * falls back to "US" so existing US installs are unaffected.
     *
     * @return string ISO 3166-1 alpha-2 region code
     */
    public function defaultPhoneRegion(): string
    {
        $configured = trim((string) ($GLOBALS['phone_country_code'] ?? ''));
        if ($configured === '') {
            return 'US';
        }
        // Already an ISO 3166-1 alpha-2 region code (e.g. "US", "GB").
        if (preg_match('/^[A-Za-z]{2}$/', $configured) === 1) {
            return strtoupper($configured);
        }
        // Otherwise treat it as a numeric dialing code (e.g. "1", "+44").
        $callingCode = (int) preg_replace('/\D/', '', $configured);
        if ($callingCode > 0) {
            $region = PhoneNumberUtil::getInstance()->getRegionCodeForCountryCode($callingCode);
            if (is_string($region) && $region !== '' && $region !== 'ZZ') {
                return $region;
            }
        }
        return 'US';
    }

    /**
     * Dialing-code prefix for the site's default region, derived (never
     * hard-coded): "+1" for US, "+44" for GB, and so on. Used as the empty
     * default for phone inputs so the field nudges toward the right country
     * without presuming North America.
     */
    public function defaultPhonePrefix(): string
    {
        $code = PhoneNumberUtil::getInstance()->getCountryCodeForRegion($this->defaultPhoneRegion());
        return $code > 0 ? '+' . $code : '';
    }

    /**
     * A real example phone number for the site's default region, in E.164
     * (e.g. "+12015550123" for US). Used as input placeholder text so the
     * expected format is obvious for the configured country.
     */
    public function defaultPhoneExample(): string
    {
        $util = PhoneNumberUtil::getInstance();
        $example = $util->getExampleNumber($this->defaultPhoneRegion());
        return $example !== null ? $util->format($example, PhoneNumberFormat::E164) : '';
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
        } catch (Throwable $e) {
            ServiceContainer::getLogger()->error(
                'getNotificationLog: query failed',
                ['exception' => $e]
            );
            return xlt('Error: Unable to load notification log.') . PHP_EOL;
        }

        return $responseMsgs;
    }

    /**
     * @return array|mixed
     */
    public function getCredentials(): mixed
    {
        return $this->getSetup();
    }

    /**
     * Resolve and return the decrypted bytes of a stored document for the
     * fax/email send path, enforcing access control first.
     *
     * The send path accepts a request-supplied document id; without a gate any
     * user holding the coarse patients/demo ACL could fax or email an arbitrary
     * document by iterating ids. This requires the patients/docs ACL and that
     * the document resolves to a real patient via its foreign_id, blocking
     * orphaned or cross-patient ids from reaching this path.
     *
     * @param int $docId
     * @return string Decrypted document bytes.
     * @throws RuntimeException When the id is invalid, unauthorized, or unresolved.
     */
    protected function readAuthorizedFaxDocument(int $docId): string
    {
        if ($docId <= 0) {
            throw new RuntimeException(xlt('Error: Invalid document reference'));
        }
        if (!AclMain::aclCheckCore('patients', 'docs')) {
            throw new RuntimeException(xlt('Error: Not authorised to access documents'));
        }
        // Resolve the owning patient straight from the row so the check does
        // not depend on a particular Document accessor name.
        $row = sqlQuery("SELECT foreign_id FROM documents WHERE id = ?", [$docId]);
        $pid = (int)($row['foreign_id'] ?? 0);
        if ($pid <= 0) {
            throw new RuntimeException(xlt('Error: Document is not associated with a patient'));
        }
        $patient = sqlQuery("SELECT pid FROM patient_data WHERE pid = ?", [$pid]);
        if (empty($patient)) {
            throw new RuntimeException(xlt('Error: Document patient not found'));
        }
        $data = (new \Document($docId))->get_data();
        if (!is_string($data) || $data === '') {
            throw new RuntimeException(xlt('Error: No content to send.'));
        }

        return $data;
    }
}
