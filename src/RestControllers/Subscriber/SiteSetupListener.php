<?php

namespace OpenEMR\RestControllers\Subscriber;

use OpenEMR\Common\Auth\OAuth2KeyConfig;
use OpenEMR\Common\Auth\OAuth2KeyException;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\FHIR\Config\ServerConfig;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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
    public function __construct()
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 50]]
        ];
    }

    public static function getValidSiteFromPath(string $pathInfo): ?string
    {
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

    public function onKernelRequest(RequestEvent $event)
    {
        // we need to identify the site id for the request
        $pathInfo = $event->getRequest()->getPathInfo();
        $siteId = self::getValidSiteFromPath($pathInfo);
        if (empty($siteId)) {
            // we don't use system logger here because we don't have access to our database that configures the logging
            error_log("OpenEMR Error - api site error, so forced exit " . "siteId: $siteId, pathInfo: $pathInfo");
            throw new HttpException(400, "OpenEMR Error: api site error, so forced exit.  Please ensure that the site is set up correctly in the OpenEMR configuration.");
        }
        $event->getRequest()->attributes->set('siteId', $siteId);

        // set the site
        $_GET['site'] = $siteId; // for legacy purposes

        // make sure the API keys are setup.  It would be better if this was all pre-generated at the time of installation
        // but we'll do this for now until we can set that up
        // TODO: figure out a way to generate oauth keys at time of installation

        if ($event->getRequest()->headers->get('APICSRFTOKEN')) {
            $ignoreAuth = false;
        } else {
            $ignoreAuth = true;
            // Will start the api OpenEMR session/cookie.
            // TODO: we need to figure out where we can get web root from
            SessionUtil::apiSessionStart('');
        }

        // Set $sessionAllowWrite to true here for following reasons:
        //  1. !$isLocalApi - not applicable since use the SessionUtil::apiSessionStart session, which was set above
        //  2. $isLocalApi - in this case, basically setting this to true downstream after some session sets via session_write_close() call
        $sessionAllowWrite = true;

        // setup the globals... would be nice to not have to do this, but we need the globals for the rest of OpenEMR
        if ($event->getKernel() instanceof OEHttpKernel) {
            $eventDispatcher = $event->getKernel()->getEventDispatcher();
        }
        require_once(__DIR__ . "./../../../interface/globals.php");
        // now that globals are setup, setup our centralized logger that will respect the global settings
        if ($event->getKernel() instanceof OEHttpKernel) {
            $event->getKernel()->setSystemLogger(new SystemLogger());
        }
        // need to make sure the keys are always created
        try {
            $serverConfig = new ServerConfig();
            // check for key existence, if public key not there, we want to make sure we create it
            if (!file_exists($serverConfig->getPublicRestKey())) {
                $oauth2KeyConfig = new OAuth2KeyConfig($GLOBALS['OE_SITE_DIR']);
                $oauth2KeyConfig->configKeyPairs();
            }
        }
        catch (\OAuth2KeyException $e) {
            throw new HttpException(500, $e->getMessage(), $e);
        }
    }
}
