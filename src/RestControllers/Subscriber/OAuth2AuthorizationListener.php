<?php

namespace OpenEMR\RestControllers\Subscriber;

// TODO: Would it be better to call these route guards?
use League\OAuth2\Server\Exception\OAuthServerException;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClaimRepository;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\RestControllers\Authorization\OAuth2DiscoveryController;
use OpenEMR\RestControllers\Authorization\OAuth2PublicJsonWebKeyController;
use OpenEMR\RestControllers\AuthorizationController;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class OAuth2AuthorizationListener implements EventSubscriberInterface
{
    private SystemLogger $logger;

    public function __construct()
    {
    }

    public static function getSubscribedEvents(): array
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

    /**
     * @param RequestEvent $event
     * @return RequestEvent
     * @throws OAuthServerException
     */
    public function onKernelRequest(RequestEvent $event): RequestEvent
    {
        $kernel = $event->getKernel();
        if (!$kernel instanceof OEHttpKernel) {
            return $event; // we only want to process this if the kernel is an OEHttpKernel
        }
        // only if this is an oauth2 request are we going to process it.
        if (!$event->hasResponse() && $this->shouldProcessRequest($event->getRequest())) {
            $response = $this->authorizeRequest($event->getRequest(), $kernel);
            $event->setResponse($response);
        }
        return $event;
    }

    /**
     * @param Request $request
     * @param OEHttpKernel $kernel
     * @return Response
     * @throws OAuthServerException
     */
    public function authorizeRequest(Request $request, OEHttpKernel $kernel): Response
    {
        $globalsBag = $kernel->getGlobalsBag();
        $logger = $this->getLogger();
        if (!($request instanceof HttpRestRequest)) {
            throw new HttpException(500, "OpenEMR Error: OAuth2AuthorizationStrategy requires HttpRestRequest");
        }
        $session = $request->getSession();
        // exit if api is not turned on
        if (
            empty($globalsBag->get('rest_api')) && empty($globalsBag->get('rest_fhir_api'))
            && empty($globalsBag->get('rest_portal_api'))
        ) {
            $logger->debug("api disabled exiting call");
            $session->invalidate();
            throw new NotFoundHttpException("OpenEMR Error: API is disabled");
        }
        // site is already valid from previous listener

        // set up csrf
        //  used to prevent csrf in the 2 different types of submissions by oauth2/provider/login.php
        if (empty($session->get('csrf_private_key'))) {
            CsrfUtils::setupCsrfKey($session);
        }
        $logger->debug("oauth2 request received", ["endpoint" => $request->getRequestPathWithoutSite()]);

        $authServer = new AuthorizationController($session, $kernel);
        $authServer->setSystemLogger($logger);


        $end_point = $request->getRequestPathWithoutSite();
        if (false !== stripos((string) $end_point, '/token')) {
            // session is destroyed within below function
            return $this->convertPsrResponse($authServer->oauthAuthorizeToken($request));
        }

        if (false !== stripos((string) $end_point, '/openid-configuration')) {
            $oauth2DiscoverController = new OAuth2DiscoveryController(
                new ClaimRepository(),
                $authServer->getScopeRepository($session),
                $globalsBag,
                $authServer->authBaseFullUrl
            );
            return $oauth2DiscoverController->getDiscoveryResponse($request);
        }

        if (false !== stripos((string) $end_point, '/authorize')) {
            // session is destroyed (when throws exception) within below function
            return $this->convertPsrResponse($authServer->oauthAuthorizationFlow($request));
        }

        if (false !== stripos((string) $end_point, AuthorizationController::DEVICE_CODE_ENDPOINT)) {
            // session is destroyed within below function
            return $this->convertPsrResponse($authServer->authorizeUser($request));
        }

        if (false !== stripos((string) $end_point, '/jwk')) {
            $oauth2JWKController = new OAuth2PublicJsonWebKeyController($authServer->getPublicKeyLocation());
            return $oauth2JWKController->getJsonWebKeyResponse($request);
        }

        if (false !== stripos((string) $end_point, '/login')) {
            // session is maintained
            return $this->convertPsrResponse($authServer->userLogin($request));
        }
        if ($authServer->isSMARTAuthorizationEndPoint($end_point)) {
            return $this->convertPsrResponse($authServer->dispatchSMARTAuthorizationEndpoint($end_point, $request));
        }

        if (false !== stripos((string) $end_point, '/scope-authorize-confirm')) {
            // session is maintained
            return $this->convertPsrResponse($authServer->scopeAuthorizeConfirm($request));
        }

        if (false !== stripos((string) $end_point, '/registration')) {
            // session is destroyed within below function
            return $this->convertPsrResponse($authServer->clientRegistration($request));
        }

        if (false !== stripos((string) $end_point, '/client')) {
            // session is destroyed within below function
            return $this->convertPsrResponse($authServer->clientRegisteredDetails($request));
        }

        if (false !== stripos((string) $end_point, '/logout')) {
            // session is destroyed within below function
            return $this->convertPsrResponse($authServer->userSessionLogout($request));
        }

        if (false !== stripos((string) $end_point, '/introspect')) {
            // session is destroyed within below function
            return $this->convertPsrResponse($authServer->tokenIntrospection($request));
        }
        return new Response('', Response::HTTP_NOT_FOUND);
    }
}
