<?php

namespace OpenEMR\RestControllers\Authorization;

use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\AccessTokenRepository;
use OpenEMR\Common\Auth\UuidUserAccount;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\System\System;
use OpenEMR\Services\TrustedUserService;
use OpenEMR\Services\UserService;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

use function collectIpAddresses;

class BearerTokenAuthorizationStrategy implements IAuthorizationStrategy
{
    private AccessTokenRepository $accessTokenRepository;

    private TrustedUserService $trustedUserService;

    private SystemLogger $logger;


    /**
     * @var callable|null
     */
    private $uuidUserAccountFactory = null;

    private CryptKey $publicKey;

    private UserService $userService;

    public function __construct(?SystemLogger $logger = null)
    {
        if ($logger) {
            $this->setLogger($logger);
        } else {
            $this->logger = new SystemLogger();
        }
    }

    public function setLogger(SystemLogger $logger): void
    {
        // This method is intended to set the logger for the authorization strategy.
        // Implementation details would depend on the specific requirements of the application.
        $this->logger = $logger;
    }

    public function getLogger(): SystemLogger
    {
        // This method is intended to return the logger for the authorization strategy.
        // Implementation details would depend on the specific requirements of the application.
        if (!isset($this->logger)) {
            // If the logger is not set, we can initialize it here.
            $this->logger = new SystemLogger();
        }
        return $this->logger;
    }

    public function getTrustedUserService(): TrustedUserService
    {
        if (!isset($this->trustedUserService)) {
            // Initialize the trusted user service if not already set.
            $this->trustedUserService = new TrustedUserService();
        }
        return $this->trustedUserService;
    }

    public function setTrustedUserService(TrustedUserService $trustedUserService): void
    {
        // This method is intended to set the trusted user service for the authorization strategy.
        // Implementation details would depend on the specific requirements of the application.
        $this->trustedUserService = $trustedUserService;
    }

    public function setPublicKey(CryptKey|string $publicKey): void
    {
        if (is_string($publicKey)) {
            // If the public key is a string, we can convert it to a CryptKey instance.
            $publicKey = new CryptKey($publicKey);
        }
        $this->publicKey = $publicKey;
    }

    public function getPublicKey(): CryptKey
    {
        return $this->publicKey;
    }

    public function getAccessTokenRepository(): AccessTokenRepository
    {
        if (!isset($this->accessTokenRepository)) {
            // Initialize the access token repository if not already set.
            $this->accessTokenRepository = $this->createAccessTokenRepository();
        }
        return $this->accessTokenRepository;
    }

    public function setAccessTokenRepository(AccessTokenRepository $accessTokenRepository): void
    {
        // This method is intended to set the access token repository for the authorization strategy.
        // Implementation details would depend on the specific requirements of the application.
        $this->accessTokenRepository = $accessTokenRepository;
    }

    /**
     * @param callable $factory (string) :=> UuidUserAccount
     * @return void
     */
    public function setUuidUserAccountFactory(callable $factory): void
    {
        // This method is intended to set the factory for creating UuidUserAccount instances.
        // Implementation details would depend on the specific requirements of the application.
        $this->uuidUserAccountFactory = $factory;
    }

    /**
     * @return callable (string) :=> UuidUserAccount
     */
    public function getUuidUserAccountFactory(): callable
    {
        if (!isset($this->uuidUserAccountFactory)) {
            // If the factory is not set, we can initialize it here.
            // This is a placeholder for the actual factory logic.
            $this->uuidUserAccountFactory = function ($userUuid) {
                return new UuidUserAccount($userUuid);
            };
        }
        return $this->uuidUserAccountFactory;
    }

    protected function createAccessTokenRepository(): AccessTokenRepository
    {
        // This method is intended to create and return an instance of AccessTokenRepository.
        // Implementation details would depend on the specific requirements of the application.
        return new AccessTokenRepository();
    }

    public function shouldProcessRequest(Request $request): bool
    {
        if (!$request instanceof HttpRestRequest) {
            // If the request is not an instance of HttpRestRequest, we do not process it.
            return false;
        }
        return true; // This strategy should process all requests, but you can add conditions if needed.
    }

    public function authorizeRequest(Request $request): bool
    {
        if (!$request instanceof HttpRestRequest) {
            // If the request is not an instance of HttpRestRequest, we do not process it.
            $this->getLogger()->error("OpenEMR Error - BearerTokenAuthorizationStrategy requires HttpRestRequest");
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, "OpenEMR Error: BearerTokenAuthorizationStrategy requires HttpRestRequest.");
        }

        // verify the access token
        $repository = $this->getAccessTokenRepository();
        $tokenRaw = $this->verifyAccessToken($repository, $request);
        // collect token attributes
        $attributes = $tokenRaw->getAttributes();
        // collect openemr user uuid
        $userId = $attributes['oauth_user_id'];
        // collect client id (will be empty for PKCE)
        $clientId = $attributes['oauth_client_id'] ?? null;
        // collect token id
        $tokenId = $attributes['oauth_access_token_id'];
        // ensure user uuid and token id are populated
        if (empty($userId) || empty($tokenId)) {
            $this->getLogger()->error("OpenEMR Error - userid or tokenid not available, so forced exit", ['attributes' => $attributes]);
            throw new HttpException(400, "OpenEMR Error: userid or tokenid not available, so forced exit. Please ensure that the access token is valid and contains the necessary attributes.");
        }
        // now verify the token has not been revoked in the database
        if ($repository->isAccessTokenRevokedInDatabase($tokenId)) {
            throw OAuthServerException::accessDenied('Access token has been revoked');
        }
        $this->logger->debug("BearerTokenAuthorizationStrategy->authorizeRequest() - Access token verified, authenticating user");

        // verify that user tokens haven't been revoked
        // this is done by verifying the user is trusted with active auth session.
        $trustedUserService = $this->getTrustedUserService();
        if (!$trustedUserService->isTrustedUser($clientId, $userId)) {
            // TODO: @adunsulag need to verify this logic
            // user is not logged on to server with an active session.
            // too me this is easier than revoking tokens or using phantom tokens.
            $this->logger->debug(
                "invalid Trusted User.  Refresh Token revoked or logged out",
                ['clientId' => $clientId, 'userId' => $userId]
            );
            throw new OAuthServerException('Refresh Token revoked or logged out', 0, 'invalid _request', 400);
        }

        // TODO: @adunsulag this seems redundant since the access token should already be verified on the expiration date
        // we should look at removing this and see if it causes any issues
        if (!$this->authenticateUserToken($request, $tokenId, $clientId, $userId)) {
            $this->logger->error("dispatch.php api call with invalid token");
            throw new UnauthorizedHttpException("Bearer", "OpenEMR Error: API call failed due to invalid token or expired token.");
        }
        $uuidToUser = $this->getUuidUserAccountFactory()($userId);
        $user = $uuidToUser->getUserAccount();
        $userRole = $uuidToUser->getUserRole();
        if (empty($user)) {
            // unable to identify the users user role
            $this->logger->error("OpenEMR Error - api user account could not be identified, so forced exit", [
                'userId' => $userId,
                'userRole' => $uuidToUser->getUserRole()]);
            // TODO: @adunsulag shouldn't this be 500? if token is valid but user isn't found, seems like a system error as it never should happen
            throw new HttpException(400);
        }
        if (empty($userRole)) {
            // unable to identify the users user role
            $this->logger->error("OpenEMR Error - api user role for user could not be identified, so forced exit");
            // TODO: @adunsulag shouldn't this be 500? if token is valid but user role isn't found, seems like a system error as it never should happen
            throw new HttpException(400);
        }

        if (!$this->isValidRequestForUserRole($request, $attributes['oauth_scopes'], $userRole)) {
            throw new HttpException(403, "User role does not have permission to access this resource.");
        }

        $this->setupSessionForUserRole($userRole, $user, $request);
        $request->attributes->set('userId', $userId);
        $request->attributes->set('user', $user);
        $request->attributes->set('userRole', $userRole);
        $request->attributes->set('clientId', $clientId);
        $request->attributes->set('tokenId', $tokenId);

        if ($request instanceof HttpRestRequest) {
            $request->setAccessTokenScopes($attributes['oauth_scopes']);
            $request->setAccessTokenId($tokenId);
            $request->setRequestUserRole($userRole);
            $request->setRequestUser($userId, $user);
            $request->setClientId($clientId);
        }
        return true;
    }


    private function setupSessionForUserRole(string $userRole, array $user, HttpRestRequest $request): void
    {
        $session = $request->getSession();

        // Set user ID in the session
        $session->set('userId', $user['uuid']);

        // Set user role in the session
        $session->set('userRole', $userRole);
        if ($userRole == 'users') {
            $session->set('authUser', $user["username"] ?? null);
            $session->set('authUserID', $user["id"] ?? null);
            $userService = $this->getUserService();
            $authProvider = $userService->getAuthGroupForUser($user['username']);
            $session->set('authProvider', $authProvider);
            if (empty($session->get('authUser')) || empty($session->get('authUserID')) || empty($session->get('authProvider'))) {
                // this should never happen
                $this->logger->error("OpenEMR Error: api failed because unable to set critical users session variables");
                throw new HttpException(401);
            }
            $this->logger->debug("dispatch.php request setup for user role", ['authUserID' => $user['id'], 'authUser' => $user['username']]);
        } elseif ($userRole == 'patient') {
            $session->set('pid', $user['pid'] ?? null);
            $this->logger->debug("dispatch.php request setup for patient role", ['patient' => $session->get('pid')]);
        } elseif ($userRole === 'system') {
            $session->set('authUser', $user["username"] ?? null);
            $session->set('authUserID', $user["id"] ?? null);
            if (
                empty($session->get('authUser'))
                // this should never happen as the system role depends on the system username... but we safety check it anyways
                || $session->get('authUser') != \OpenEMR\Services\UserService::SYSTEM_USER_USERNAME
                || empty($session->get('authUserID'))
            ) {
                $this->logger->error("OpenEMR Error: api failed because unable to set critical users session variables");
                throw new HttpException(401);
            }
        }
    }

    private function authenticateUserToken(HttpRestRequest $request, $tokenId, $clientId, $userId): bool
    {
        $ips = $request->getClientIps();
        $ip = [
            'ip_string' => implode(" ", $ips)
        ];

        // check for token
        $accessTokenRepo = $this->getAccessTokenRepository();
        $authTokenExpiration = $accessTokenRepo->getTokenExpiration($tokenId, $clientId, $userId);
        if (empty($authTokenExpiration)) {
            EventAuditLogger::instance()->newEvent('api', '', '', 0, "API failure: " . $ip['ip_string'] . ". Token not found for client[" . $clientId . "] and user " . $userId . ".");
            return false;
        }
        // Ensure token not expired (note an expired token should have already been caught by oauth2, however will also check here)
        $currentDateTime = date("Y-m-d H:i:s");
        $expiryDateTime = date("Y-m-d H:i:s", strtotime($authTokenExpiration));
        if ($expiryDateTime <= $currentDateTime) {
            EventAuditLogger::instance()->newEvent('api', '', '', 0, "API failure: " . $ip['ip_string'] . ". Token expired for client[" . $clientId . "] and user " . $userId . ".");
            return false;
        }

        // Token authentication passed
        EventAuditLogger::instance()->newEvent('api', '', '', 1, "API success: " . $ip['ip_string'] . ". Token successfully used for client[" . $clientId . "] and user " . $userId . ".");
        return true;
    }



    private function verifyAccessToken(AccessTokenRepository $accessTokenRepository, Request $request)
    {
        $publicKey = $this->getPublicKey();
        try {
            // TODO: @adunsulag not sure about the performance of this, but we need to ensure that the access token is verified
            // if we there's a key problem need to catch the exception
            $server = new ResourceServer(
                $accessTokenRepository,
                $publicKey
            );
            $psr17Factory = new Psr17Factory();
            $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
            $psrRequest = $psrHttpFactory->createRequest($request);

            $raw = $server->validateAuthenticatedRequest($psrRequest);
        } catch (OAuthServerException $exception) {
            $this->getLogger()->error("RestConfig->verifyAccessToken() OAuthServerException", ["message" => $exception->getMessage()]);
            throw new HttpException(401, $exception->getMessage(), $exception);
        } catch (\Exception $exception) {
            if ($exception instanceof LogicException) {
                $this->getLogger()->error(
                    "BearerTokenAuthorizationStrategy::verifyAccessToken() LogicException, likely oauth2 public key is missing, corrupted, or misconfigured",
                    ["message" => $exception->getMessage()]
                );
                throw new HttpException(500, "Server Error", $exception);
            } else {
                $this->getLogger()->error(
                    "BearerTokenAuthorizationStrategy::verifyAccessToken() Exception",
                    ["message" => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]
                );
                // do NOT reveal what happened at the server level if we have a server exception
                throw new HttpException(500, "Server Error", $exception);
            }
        }

        return $raw;
    }

    private function isValidRequestForUserRole(Request $request, array $oauthScopes, string $userRole)
    {
        $resource = $request->getPathInfo();
        if (
            // fhir routes are the default and can send openid/fhirUser w/ authorization_code, or no scopes at all
            // with Client Credentials, so we only reject requests for standard or portal if the correct scope is not
            // sent.
            // TODO: @adunsulag can replace a bunch of these methods with methods in the request object.
            (self::is_api_request($resource) && !in_array('api:oemr', $oauthScopes)) ||
            (self::is_portal_request($resource) && !in_array('api:port', $oauthScopes))
        ) {
            $this->getLogger()->errorLogCaller("api call with token that does not cover the requested route");
            throw new HttpException(403, "OpenEMR Error: API call failed due to insufficient permissions for the requested resource.");
        }
        // ensure user role has access to the resource
        //  for now assuming:
        //   users has access to oemr and fhir
        //   patient has access to port and fhir
        if ($userRole == 'users' && (self::is_api_request($resource) || self::is_fhir_request($resource))) {
            $this->getLogger()->debug("dispatch.php valid role and user has access to api/fhir resource", ['resource' => $resource]);
            // good to go
        } elseif ($userRole == 'patient' && (self::is_portal_request($resource) || self::is_fhir_request($resource))) {
            $this->getLogger()->debug("dispatch.php valid role and patient has access portal resource", ['resource' => $resource]);
            // good to go
        } elseif ($userRole === 'system' && (self::is_fhir_request($resource))) {
            $this->getLogger()->debug("dispatch.php valid role and system has access to api/fhir resource", ['resource' => $resource]);
        } else {
            $this->getLogger()->error("OpenEMR Error: api failed because user role does not have access to the resource", ['resource' => $resource, 'userRole' => $userRole]);
            throw new HttpException(403, "OpenEMR Error: API call failed due to insufficient permissions for the requested resource.");
        }
        return true;
    }

    public static function is_portal_request($resource): bool
    {
        return stripos(strtolower($resource), "/portal/") !== false;
    }

    public static function is_api_request($resource): bool
    {
        return stripos(strtolower($resource), "/api/") !== false;
    }

    public function setUserService(UserService $userService)
    {
        // This method is intended to set the user service for the authorization strategy.
        $this->userService = $userService;
    }

    public function getUserService(): UserService
    {
        if (!isset($this->userService)) {
            // Initialize the user service if not already set.
            $this->userService = new UserService();
        }
        return $this->userService;
    }
}
