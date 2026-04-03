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
use OpenEMR\Common\Auth\Oidc\Cache\FilesystemCache;
use OpenEMR\Common\Auth\Oidc\Discovery\OidcDiscoveryClient;
use OpenEMR\Common\Auth\Oidc\Event\OidcLoginRequestEvent;
use OpenEMR\Common\Auth\Oidc\Identity\ExternalIdentityRepository;
use OpenEMR\Common\Auth\Oidc\Token\JwksClient;
use OpenEMR\Common\Auth\Oidc\Token\OidcTokenValidator;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Core\TemplatePageEvent;
use OpenEMR\Modules\GcipAuth\Auth\GcipAuthHandler;
use OpenEMR\Modules\GcipAuth\Auth\GcipClaimMapper;
use OpenEMR\Modules\GcipAuth\Config\GcipConfigService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class Bootstrap
{
    private const MODULE_PATH = '/interface/modules/custom_modules/oe-module-gcip-auth';

    private readonly string $templatePath;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
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

        // Replace the template with our GCIP login form
        $event->setTwigTemplate($this->templatePath . 'gcip-login.html.twig');

        $globals = OEGlobalsBag::getInstance();
        $webRoot = $globals->getString('webroot');

        $existingVars = $event->getTwigVariables();
        $existingVars['gcipFirebaseApiKey'] = $firebaseApiKey;
        $existingVars['gcipFirebaseAuthDomain'] = $firebaseAuthDomain;
        $existingVars['gcipFirebaseProjectId'] = $firebaseProjectId;
        $existingVars['gcipAllowedTenantIds'] = $configService->getAllowedTenantIds();
        $existingVars['gcipModulePath'] = $webRoot . self::MODULE_PATH;
        $existingVars['gcipLocalLoginDisabled'] = $globals->getBoolean('oidc_local_login_disabled');

        $event->setTwigVariables($existingVars);

        return $event;
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
        $jwksClient = new JwksClient($httpClient, $cache);
        $clock = new SystemClock(new \DateTimeZone('UTC'));

        $tokenValidator = new OidcTokenValidator(
            $jwksClient,
            new GcipClaimMapper(),
            $clock,
        );

        return new GcipAuthHandler(
            $tokenValidator,
            $discoveryClient,
            new ExternalIdentityRepository(),
            $configService,
            $this->eventDispatcher,
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
