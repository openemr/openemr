<?php

namespace OpenEMR\RestControllers\Subscriber;

// TODO: Would it be better to call these route guards?
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\RestControllers\Authorization\IAuthorizationStrategy;
use OpenEMR\RestControllers\AuthorizationController;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class OAuth2AuthorizationListener implements EventSubscriberInterface
{
    private SystemLogger $logger;
    public function __construct()
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 50]]
        ];
    }

    public function setSystemLogger(SystemLogger $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @return SystemLogger
     */
    public function getLogger(): SystemLogger
    {
        if (!isset($this->logger)) {
            $this->logger = new SystemLogger();
        }
        return $this->logger;
    }

    public function shouldProcessRequest(Request $request): bool
    {
        return str_ends_with($request->getBasePath(), '/oauth2');
    }

    private function convertPsrResponse(ResponseInterface $response): Response
    {
        $httpFoundationFactory = new HttpFoundationFactory();
        return $httpFoundationFactory->createResponse($response);
    }

    public function onKernelRequest(RequestEvent $event) {
        // only if this is an oauth2 request are we going to process it.
        if (!$event->hasResponse() && $this->shouldProcessRequest($event->getRequest())) {
            $response = $this->authorizeRequest($event->getRequest());
            $event->setResponse($response);
        }
        return $event;
    }

    public function authorizeRequest(Request $request): Response
    {
        $logger = $this->getLogger();
        if (!($request instanceof HttpRestRequest)) {
            throw new HttpException(500, "OpenEMR Error: OAuth2AuthorizationStrategy requires HttpRestRequest");
        }
        // exit if api is not turned on
        if (empty($GLOBALS['rest_api']) && empty($GLOBALS['rest_fhir_api']) && empty($GLOBALS['rest_portal_api'])) {
            $logger->debug("api disabled exiting call");
            SessionUtil::oauthSessionCookieDestroy();
            throw HttpException::fromStatusCode(404, "OpenEMR Error: API is disabled");
        }
        // site is already valid from previous listener

        // set up csrf
        //  used to prevent csrf in the 2 different types of submissions by oauth2/provider/login.php
        if (empty($_SESSION['csrf_private_key'])) {
            CsrfUtils::setupCsrfKey();
        }
        $logger->debug("oauth2 request received", ["endpoint" => $request->getRequestPathWithoutSite()]);

        $authServer = new AuthorizationController();

        $end_point = $request->getRequestPathWithoutSite();
        if (false !== stripos($end_point, '/token')) {
            // session is destroyed within below function
            return $this->convertPsrResponse($authServer->oauthAuthorizeToken());
        }

        if (false !== stripos($end_point, '/openid-configuration')) {
            $oauthdisc = true;
            $base_url = $authServer->authBaseFullUrl;
            // TODO: @adunsulag refactor this
            require_once("provider/.well-known/discovery.php");
            exit;
        }

        if (false !== stripos($end_point, '/authorize')) {
            // session is destroyed (when throws exception) within below function
            return $this->convertPsrResponse($authServer->oauthAuthorizationFlow());
        }

        if (false !== stripos($end_point, '/device/code')) {
            // session is destroyed within below function
            return $this->convertPsrResponse($authServer->authorizeUser());
        }

        if (false !== stripos($end_point, '/jwk')) {
            $oauthjwk = true;
            // TODO: @adunsulag refactor this
            require_once(__DIR__ . "/provider/jwk.php");
            exit;
        }

        if (false !== stripos($end_point, '/login')) {
            // session is maintained
            return $this->convertPsrResponse($authServer->userLogin());
        }
        if ($authServer->isSMARTAuthorizationEndPoint($end_point)) {
            return $this->convertPsrResponse($authServer->dispatchSMARTAuthorizationEndpoint($end_point));
        }

        if (false !== stripos($end_point, '/scope-authorize-confirm')) {
            // session is maintained
            return $this->convertPsrResponse($authServer->scopeAuthorizeConfirm());
        }

        if (false !== stripos($end_point, '/registration')) {
            // session is destroyed within below function
            return $this->convertPsrResponse($authServer->clientRegistration());
        }

        if (false !== stripos($end_point, '/client')) {
            // session is destroyed within below function
            return $this->convertPsrResponse($authServer->clientRegisteredDetails());
        }

        if (false !== stripos($end_point, '/logout')) {
            // session is destroyed within below function
            return $this->convertPsrResponse($authServer->userSessionLogout());
        }

        if (false !== stripos($end_point, '/introspect')) {
            // session is destroyed within below function
            return $this->convertPsrResponse($authServer->tokenIntrospection());
        }
    }
}
