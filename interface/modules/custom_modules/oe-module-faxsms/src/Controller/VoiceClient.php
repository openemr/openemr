<?php

namespace OpenEMR\Modules\FaxSMS\Controller;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\FaxSMS\Controller\AppDispatch;

/**
 * Server-side credential provider for the in-browser RingCentral softphone.
 *
 * **Do not remove this class because the `sendFax/sendSMS/sendEmail/fetchReminderCount`
 * methods look like unimplemented stubs.** They are stubs, and they are not the
 * execution path. Voice does not use them.
 *
 * The voice feature is a sponsored, production RingCentral integration. The
 * server's only job is to mint credentials; the actual softphone runs entirely
 * in the browser. The execution path is:
 *
 *   1. `VoiceClient::getCredentials()` / `getVoiceCredentials()` decrypt the
 *      RingCentral appKey / appSecret / JWT from `module_faxsms_credentials`.
 *   2. `NotificationEventListener::renderPhoneWidget()` injects those
 *      credentials into `templates/phone_widget.html.twig` on every page.
 *   3. The twig template loads RingCentral Embeddable
 *      (apps.ringcentral.com/integration/ringcentral-embeddable/3.x/adapter.js)
 *      and handles login, dialing, and call control client-side.
 *
 * Removing any of {this class, `setup_voice.php`, `phone_widget.html.twig`,
 * `ServiceType::VOICE`, the voice subscriptions in `NotificationEventListener`}
 * breaks the integration for every deployment that uses it. There is no CI
 * coverage of this path yet — see #12230. Until that exists, treat removal as
 * load-bearing and confirm with @sjpadgett.
 *
 * History: PR #12020 deleted this on the (incorrect) read that the stub
 * methods meant the feature was unimplemented. PR #12229 reverted it.
 */
class VoiceClient extends AppDispatch
{
    use AuthenticateTrait;

    public static $timeZone;
    public $baseDir;
    public $uriDir;
    public $serverUrl;
    public $redirectUrl;
    public $portalUrl;
    public $credentials;
    public $cacheDir;
    public $apiBase;
    protected $platform;
    protected $rcsdk;
    protected CryptoInterface $crypto;
    public function __construct()
    {
        if (empty(OEGlobalsBag::getInstance()->get('oe_enable_voice') ?? null)) {
            throw new \RuntimeException(xlt("Access denied! Module not enabled"));
        }
        $this->crypto = ServiceContainer::getCrypto();
        $this->baseDir = OEGlobalsBag::getInstance()->getString('temporary_files_dir');
        $this->uriDir = OEGlobalsBag::getInstance()->get('OE_SITE_WEBROOT');
        $this->cacheDir = OEGlobalsBag::getInstance()->get('OE_SITE_DIR') . '/documents/logs_and_misc/_cache';
        $this->credentials = $this->getCredentials();
        $this->portalUrl = $this->credentials['production'] ?? null ? "https://service.ringcentral.com/" : "https://service.devtest.ringcentral.com/";
        $this->serverUrl = $this->credentials['production'] ?? null ? "https://platform.ringcentral.com" : "https://platform.devtest.ringcentral.com";
        $this->redirectUrl = $this->credentials['redirect_url'] ?? null;
        $this->initializeSDK();
        //$this->initVoice($this->platform);
        parent::__construct();
    }

    public function getVoiceCredentials(): mixed
    {
        $vendor = '_voice';
        $this->authUser = (int)$this->getSession('authUserID');
        if (!(OEGlobalsBag::getInstance()->get('oerestrict_users') ?? null)) {
            $this->authUser = 0;
        }
        $credentials = sqlQuery("SELECT * FROM `module_faxsms_credentials` WHERE `auth_user` = ? AND `vendor` = ?", [$this->authUser, $vendor]);

        if (empty($credentials)) {
            return [
                'extension' => '',
                'phone' => '',
                'smsNumber' => '',
                'appKey' => '',
                'appSecret' => '',
                'server' => '',
                'portal' => '',
                'production' => '',
                'jwt' => ''
            ];
        } else {
            $credentials = $credentials['credentials'];
        }

        $decrypt = $this->crypto->decryptStandard(is_string($credentials) ? $credentials : null);
        return json_decode($decrypt, true);
    }

    /**
     * @return array
     */
    public function getCredentials(): array
    {
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
        return $this->getSetup();
    }

    function sendFax(): string|bool
    {
        return '';
        // TODO: Implement sendFax() method.
    }

    function sendSMS(): mixed
    {
        return '';
        // TODO: Implement sendSMS() method.
    }

    function sendEmail(): mixed
    {
        return '';
        // TODO: Implement sendEmail() method.
    }

    function fetchReminderCount(): string|bool
    {
        return '';
        // TODO: Implement fetchReminderCount() method.
    }
}
