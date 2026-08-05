<?php

namespace OpenEMR\RestControllers\Subscriber;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Auth\OAuth2KeyConfig;
use OpenEMR\Common\Auth\OAuth2KeyException;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\HttpSessionFactory;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\FHIR\Config\ServerConfig;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * This listener is responsible for setting up the site ID based on the request path.
 * It checks if the site directory exists and validates the site ID format.
 * If the site ID is invalid or the directory does not exist, it throws an HttpException.
 */
class SiteSetupListener implements EventSubscriberInterface
{
    use SystemLoggerAwareTrait;

    public function __construct()
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            // this needs to happen fairly early on before anything else in the lifecycle
            KernelEvents::REQUEST => [['onKernelRequest', 100]]
        ];
    }

    public static function getValidSiteFromPath(string $pathInfo): ?string
    {
        // TODO: we need to figure out the web root from the request
        if (empty($pathInfo)) {
            $pathInfo = "/default/"; // default to "default" site if path is empty
        }
        $endOfPath = strpos($pathInfo, '/', 1);
        $siteId = $endOfPath !== false ? substr($pathInfo, 1, $endOfPath - 1) : "default";
        if (empty($siteId) || preg_match('/[^A-Za-z0-9\\-.]/', $siteId) || !file_exists(__DIR__ . '/../../../sites/' . $siteId)) {
            $siteId = null;
        }
        return $siteId;
    }

    public static function getWebroot()
    {

        // TODO: this is copied from globals.php, we need to figure out how to share this code even though we don't have the autoload defined yet...
        // Is this windows or non-windows? Create a boolean definition.
        if (!defined('IS_WINDOWS')) {
            define('IS_WINDOWS', (stripos(PHP_OS, 'WIN') === 0));
        }

// The webserver_root and web_root are now automatically collected.
// If not working, can set manually below.
// Auto collect the full absolute directory path for openemr.
        $webserver_root = dirname(__FILE__, 4);
        if (IS_WINDOWS) {
            //convert windows path separators
            $webserver_root = str_replace("\\", "/", $webserver_root);
        }

// Collect the apache server document root (and convert to windows slashes, if needed)
        $server_document_root = realpath($_SERVER['DOCUMENT_ROOT']);
        if (IS_WINDOWS) {
            //convert windows path separators
            $server_document_root = str_replace("\\", "/", $server_document_root);
        }

// Auto collect the relative html path, i.e. what you would type into the web
// browser after the server address to get to OpenEMR.
// This removes the leading portion of $webserver_root that it has in common with the web server's document
// root and assigns the result to $web_root. In addition to the common case where $webserver_root is
// /var/www/openemr and document root is /var/www, this also handles the case where document root is
// /var/www/html and there is an Apache "Alias" command that directs /openemr to /var/www/openemr.
        $web_root = substr($webserver_root, strspn($webserver_root ^ $server_document_root, "\0"));
// Ensure web_root starts with a path separator
        if (preg_match("/^[^\/]/", $web_root)) {
            $web_root = "/" . $web_root;
        }
        return $web_root;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (!($request instanceof HttpRestRequest)) {
            // we only want to process this if the request is an HttpRestRequest
            error_log("SiteSetupListener::onKernelRequest was not a valid HttpRestRequest, so exiting");
            return;
        }

        // we need to identify the site id for the request
        $pathInfo = $request->getPathInfo();
        $siteId = self::getValidSiteFromPath($pathInfo);
        if (empty($siteId)) {
            // TODO: @adunsulag do we need to do a 401 when its an oauth2 request?
            // we don't use system logger here because we don't have access to our database that configures the logging
            error_log("OpenEMR Error - api site error, so forced exit " . "siteId: $siteId, pathInfo: $pathInfo");
            throw new HttpException(Response::HTTP_BAD_REQUEST, "OpenEMR Error: api site error, so forced exit.  Please ensure that the site is set up correctly in the OpenEMR configuration.");
        }
        $request->attributes->set('siteId', $siteId);
        $webroot = self::getWebroot();
        $request->attributes->set('webroot', $webroot);

        // set the site
        $_GET['site'] = $siteId; // for legacy purposes
        $isOauth2Request = $this->checkForOauth2Request($request);
        // TODO: @adunsulag couldn't this just be stored in the attributes instead of subclass checking this?
        $request->setRequestSite($siteId);
        // make sure the API keys are setup.  It would be better if this was all pre-generated at the time of installation
        // but we'll do this for now until we can set that up
        // TODO: figure out a way to generate oauth keys at time of installation

        if ($request->headers->get('APICSRFTOKEN')) {
            $ignoreAuth = false;
        } else {
            $ignoreAuth = true;
            $this->setupApiSession($request, $webroot, $siteId, $isOauth2Request);
        }

        $globalsBag = $this->loadApplicationGlobals($event, $ignoreAuth);

        // need to do a bridge session
        if ($request->headers->get('APICSRFTOKEN')) {
            $this->setupCoreSessionBridge($request, $webroot);
        }

        // need to make sure the keys are always created
        try {
            $this->setupOAuthKeys($globalsBag, $request);
        } catch (OAuth2KeyException $e) {
            throw new HttpException(500, $e->getMessage(), $e);
        }
        $this->getSystemLogger()->debug("SiteSetupListener::onKernelRequest site setup complete", [
            'siteId' => $siteId,
            'webroot' => $webroot,
            'isOauth2Request' => $isOauth2Request,
            'apiBaseUrl' => $request->getApiBaseFullUrl()
        ]);
    }

    /**
     * Start the API or OAuth session for non-local-API requests.
     * Override in tests to avoid starting real PHP sessions.
     *
     */
    protected function setupApiSession(
        HttpRestRequest $request,
        string $webroot,
        string $siteId,
        bool $isOauth2Request
    ): void {
        $sessionFactory = new HttpSessionFactory(
            $request,
            $webroot,
            $isOauth2Request ? HttpSessionFactory::SESSION_TYPE_OAUTH : HttpSessionFactory::SESSION_TYPE_API
        );
        if ($isOauth2Request) {
            $request->attributes->set('is_oauth2_request', true);
        }
        $session = $sessionFactory->createSession();
        $request->setSession($session);
        $session->set('site_id', $siteId);
        SessionWrapperFactory::getInstance()->setActiveSession($session); // set active session to be in use in standalone/shared files
    }

    /**
     * Load globals.php and configure the kernel logger.
     * Override in tests to avoid requiring the full OpenEMR environment.
     *
     * globals.php reads several variables from the caller's local scope via
     * PHP include semantics. Set them here so they remain visible to the
     * included file.
     *
     */
    protected function loadApplicationGlobals(RequestEvent $event, bool $ignoreAuth): mixed
    {
        // globals.php line 236: $read_only = empty($sessionAllowWrite)
        $sessionAllowWrite = true;

        // globals.php line 220: if (isset($globalsBag)) — reuse the kernel's bag
        // globals.php line 234: $globalsBag->set('eventDispatcher', $eventDispatcher ?? null)
        if ($event->getKernel() instanceof OEHttpKernel) {
            $eventDispatcher = $event->getKernel()->getEventDispatcher();
            $globalsBag = $event->getKernel()->getGlobalsBag();
        }

        // globals.php line 251: if (empty($ignoreAuth) && empty($ignoreAuth_onsite_portal))
        // $ignoreAuth is already a parameter in this scope.

        $globalsBag = require_once(__DIR__ . "/../../../interface/globals.php");
        if ($event->getKernel() instanceof OEHttpKernel) {
            $event->getKernel()->setSystemLogger(ServiceContainer::getLogger());
        }
        return $globalsBag;
    }

    /**
     * Create a core session bridge for local API requests (APICSRFTOKEN).
     * Override in tests to avoid starting real PHP sessions.
     *
     */
    protected function setupCoreSessionBridge(HttpRestRequest $request, string $webroot): void
    {
        $sessionFactory = new HttpSessionFactory(
            $request,
            $webroot,
            HttpSessionFactory::SESSION_TYPE_CORE
        );
        $session = $sessionFactory->createSession();
        $request->setSession($session);
        SessionWrapperFactory::getInstance()->setActiveSession($session, $sessionFactory->getLastCreatedStorage()); // set active session to be in use in standalone/shared files
    }

    /**
     * Ensure OAuth2 key pairs exist and set the API base URL.
     * Override in tests to avoid filesystem/config dependencies.
     *
     */
    protected function setupOAuthKeys(mixed $globalsBag, HttpRestRequest $request): void
    {
        $serverConfig = new ServerConfig();
        if (!file_exists($serverConfig->getPublicRestKey())) {
            $oauth2KeyConfig = new OAuth2KeyConfig($globalsBag->get('OE_SITE_DIR'));
            $oauth2KeyConfig->configKeyPairs();
        }
        $request->setApiBaseFullUrl($serverConfig->getBaseApiUrl());
    }

    private function checkForOauth2Request(HttpRestRequest $request): bool
    {
        $path = $request->getBasePath();
        return str_ends_with($path, "/oauth2");
    }
}
