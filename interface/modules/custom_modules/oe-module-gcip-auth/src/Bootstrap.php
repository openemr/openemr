<?php

/**
 * Main bootstrap class for the GCIP Auth module.
 *
 * Subscribes to events to:
 * - Replace the login form with Firebase JS SDK when OIDC is active
 * - Handle OIDC token POST-back via OidcLoginRequestEvent
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Modules\GcipAuth;

use GuzzleHttp\Client;
use Lcobucci\Clock\SystemClock;
use OpenEMR\Common\Auth\Oidc\Audit\DatabaseOidcLoginAuditLogger;
use OpenEMR\Common\Auth\Oidc\Cache\FilesystemCache;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcDiscoveryClient;
use OpenEMR\Common\Auth\Oidc\Event\OidcLoginRequestEvent;
use OpenEMR\Common\Auth\Oidc\Identity\DatabaseLocalUserDirectory;
use OpenEMR\Common\Auth\Oidc\Identity\ExternalIdentityRepository;
use OpenEMR\Common\Auth\Oidc\Session\OidcSessionHelper;
use OpenEMR\Common\Auth\Oidc\Token\OidcTokenValidator;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\JWTRepository;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Core\TemplatePageEvent;
use OpenEMR\Events\Main\Tabs\RenderEvent;
use OpenEMR\Modules\GcipAuth\Auth\GcipAuthHandler;
use OpenEMR\Modules\GcipAuth\Auth\GcipClaimMapper;
use OpenEMR\Modules\GcipAuth\Config\GcipConfigService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class Bootstrap
{
    private const MODULE_PATH = '/interface/modules/custom_modules/oe-module-gcip-auth';

    private string $templatePath;

    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
    ) {
        $this->templatePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
    }

    public function subscribeToEvents(): void
    {
        if (!OEGlobalsBag::getInstance()->getBoolean('oidc_enabled')) {
            return;
        }

        // Replace login template when OIDC is active
        $this->eventDispatcher->addListener(
            TemplatePageEvent::RENDER_EVENT,
            $this->onTemplatePageEvent(...),
        );

        // Handle OIDC token POST-back
        $this->eventDispatcher->addListener(
            OidcLoginRequestEvent::EVENT_NAME,
            $this->onLoginRequest(...),
        );

        // Inject session refresh script into authenticated pages (outer frame)
        $this->eventDispatcher->addListener(
            RenderEvent::EVENT_BODY_RENDER_POST,
            $this->onBodyRenderPost(...),
        );
    }

    public function onTemplatePageEvent(TemplatePageEvent $event): TemplatePageEvent
    {
        $pageName = $event->getPageName();

        // Only intercept login pages
        if ($pageName !== 'login/login.php' && $pageName !== 'oauth2/authorize/login') {
            return $event;
        }

        $configService = new GcipConfigService();
        $firebaseApiKey = $configService->getFirebaseApiKey();
        $firebaseAuthDomain = $configService->getFirebaseAuthDomain();
        $firebaseProjectId = $configService->getFirebaseProjectId();

        if ($firebaseApiKey === '' || $firebaseAuthDomain === '' || $firebaseProjectId === '') {
            return $event; // Module not configured — show standard login
        }

        $globals = OEGlobalsBag::getInstance();
        $webRoot = $globals->getString('webroot');

        // Ensure CSRF key exists in the pre-auth session so we can protect
        // the OIDC token POST. Only creates the key if one doesn't already
        // exist — does not regenerate an existing key.
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        if ($session->get('csrf_private_key', null) === null) {
            CsrfUtils::setupCsrfKey($session);
        }
        $oidcCsrfToken = CsrfUtils::collectCsrfToken($session, 'oidc_login');

        $variables = $event->getTwigVariables();
        $variables['gcipFirebaseApiKey'] = $firebaseApiKey;
        $variables['gcipFirebaseAuthDomain'] = $firebaseAuthDomain;
        $variables['gcipFirebaseProjectId'] = $firebaseProjectId;
        $variables['gcipAllowedTenantIds'] = $configService->getAllowedTenantIds();
        $variables['gcipModulePath'] = $webRoot . self::MODULE_PATH;
        $variables['gcipLocalLoginDisabled'] = $globals->getBoolean('oidc_local_login_disabled');
        $variables['gcipCsrfToken'] = $oidcCsrfToken;

        // Render with our own Twig environment since the module's template
        // directory is not registered with the global TwigContainer loader.
        $twig = new TwigContainer($this->templatePath, $globals->getKernel());
        echo $twig->getTwig()->render('gcip-login.html.twig', $variables);
        exit;
    }

    public function onBodyRenderPost(RenderEvent $event): void
    {
        if (!OidcSessionHelper::isOidcSession()) {
            return;
        }

        $expiresAt = OidcSessionHelper::getTokenExpiry();
        if ($expiresAt === null) {
            return;
        }

        $configService = new GcipConfigService();
        $firebaseApiKey = $configService->getFirebaseApiKey();
        $firebaseAuthDomain = $configService->getFirebaseAuthDomain();
        $firebaseProjectId = $configService->getFirebaseProjectId();

        if ($firebaseApiKey === '' || $firebaseAuthDomain === '' || $firebaseProjectId === '') {
            return;
        }

        $globals = OEGlobalsBag::getInstance();
        $webRoot = $globals->getString('webroot');
        $refreshMargin = $globals->getInt('oidc_refresh_margin_minutes');
        if ($refreshMargin <= 0) {
            $refreshMargin = 5;
        }

        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $csrfToken = CsrfUtils::collectCsrfToken($session);

        $tenantIds = $configService->getAllowedTenantIds();
        $tenantId = $tenantIds !== [] ? $tenantIds[0] : '';

        $siteId = $session->get('site_id', 'default');
        $siteId = is_string($siteId) ? $siteId : 'default';

        // Security note: Firebase API keys are designed to be public — they
        // identify the project but do not grant access (access is controlled
        // by Firebase Security Rules). The CSRF token is session-scoped and
        // HMAC-derived, safe to embed in page source.
        $configJson = json_encode([
            'firebaseApiKey' => $firebaseApiKey,
            'firebaseAuthDomain' => $firebaseAuthDomain,
            'firebaseProjectId' => $firebaseProjectId,
            'tenantId' => $tenantId,
            'expiresAt' => $expiresAt,
            'refreshMarginMinutes' => $refreshMargin,
            'csrfToken' => $csrfToken,
            'webRoot' => $webRoot,
            'siteId' => $siteId,
        ], JSON_THROW_ON_ERROR);

        $scriptPath = $webRoot . self::MODULE_PATH . '/public/js/gcip-session-refresh.js';

        echo '<script>window.__gcipSessionRefresh = ' . $configJson . ';</script>' . "\n";
        echo '<script src="' . htmlspecialchars($scriptPath, ENT_QUOTES) . '"></script>' . "\n";
    }

    public function onLoginRequest(OidcLoginRequestEvent $event): void
    {
        if ($event->isHandled()) {
            return; // Another listener already handled this
        }

        $idToken = $event->getPostParam('oidc_id_token');
        if ($idToken === null || $idToken === '') {
            return;
        }

        $handler = $this->createAuthHandler();
        $handler->onLoginRequest($event);
    }

    private function createAuthHandler(): GcipAuthHandler
    {
        $configService = new GcipConfigService();
        $httpClient = new Client(['timeout' => 10]);
        $cacheDir = $this->resolveCacheDirectory();
        $cache = new FilesystemCache($cacheDir);

        $discoveryClient = new OidcDiscoveryClient($httpClient, $cache);
        $clock = new SystemClock(new \DateTimeZone('UTC'));

        $tokenValidator = new OidcTokenValidator(
            $httpClient,
            new GcipClaimMapper(),
            $clock,
            new JWTRepository(),
            $cache,
        );

        return new GcipAuthHandler(
            $tokenValidator,
            $discoveryClient,
            new ExternalIdentityRepository(),
            new DatabaseLocalUserDirectory(),
            $configService,
            $this->eventDispatcher,
            new DatabaseOidcLoginAuditLogger(),
        );
    }

    private function resolveCacheDirectory(): string
    {
        $tempDir = OEGlobalsBag::getInstance()->getString('temporary_files_dir');
        $cacheDir = $tempDir . DIRECTORY_SEPARATOR . 'oidc_cache';

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0o755, true);
        }

        return $cacheDir;
    }
}
