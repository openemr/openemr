<?php

namespace OpenEMR\RestControllers\Authorization;

use OpenEMR\Services\UserService;
use Symfony\Component\HttpFoundation\Response;
use OpenEMR\Common\Auth\UuidUserAccount;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Core\OEGlobalsBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

// TODO: Rename this to LocalApiAuthorizationStrategy
class LocalApiAuthorizationController implements IAuthorizationStrategy
{
    private UserService $userService;

    public function __construct(private readonly SystemLogger $logger, private readonly OEGlobalsBag $globalsBag)
    {
    }

    public function shouldProcessRequest(Request $request): bool
    {
        if ($request->headers->has("APICSRFTOKEN")) {
            $request->attributes->set("_is_local_api", true);
            // this is a local api request, so we should process it
            return true;
        }
        return false;
    }

    /**
     * @param HttpRestRequest $request
     * @return bool
     * @throw UnauthorizedHttpException if the request is not authorized
     */
    public function authorizeRequest(HttpRestRequest $request): bool
    {
        $session = $request->getSession();
        // for legacy purposes
        $this->globalsBag->set('is_local_api', true);
        // need to check for csrf match when using api locally
        $csrfFail = false;
        $csrfToken = $request->headers->get("APICSRFTOKEN");

        if (empty($csrfToken)) {
            $this->logger->error("OpenEMR Error: internal api failed because csrf token not received");
            $csrfFail = true;
        }

        if ((!$csrfFail) && (!CsrfUtils::verifyCsrfToken($csrfToken, 'api', $session))) {
            $this->logger->error("OpenEMR Error: internal api failed because csrf token did not match");
            $csrfFail = true;
        }

        if ($csrfFail) {
            $this->logger->error(self::class . " CSRF failed", ["resource" => $request->getPathInfo()]);
            throw new UnauthorizedHttpException("APICSRFTOKEN", "OpenEMR Error: internal api failed because csrf token did not match");
        }
        $userId = $session->get('authUserID');
        if (empty($userId)) {
            // unable to identify the user
            $this->logger->error("OpenEMR Error - api user account could not be identified, so forced exit", ['userId' => $userId]);
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        // currently local api only supports users, so we will set the user role to users
        $userService = $this->getUserService();
        $user = $userService->getUser($userId);
        $userRole = UuidUserAccount::USER_ROLE_USERS;

        if (empty($user)) {
            // unable to identify the users user role
            $this->logger->error("OpenEMR Error - local user account could not be identified, so forced exit", [
                'userId' => $userId,
                'userRole' => $userRole]);
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $userUuid = $user['uuid'];
        $request->attributes->set('userId', $userUuid);
        $request->attributes->set('clientId', null);
        $request->attributes->set('tokenId', $csrfToken);
        $request->attributes->set('skipAuthorization', true);
        $request->setAccessTokenId($csrfToken);
        $request->setRequestUserRole($userRole);
        $request->setRequestUser($userUuid, $user);
        return true;
    }

    public function getUserService(): UserService
    {
        if (!isset($this->userService)) {
            $this->userService = new UserService();
        }
        return $this->userService;
    }

    public function setUserService(UserService $userService): void
    {
        $this->userService = $userService;
    }
}
