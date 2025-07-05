<?php

namespace OpenEMR\RestControllers\Authorization;

use OpenEMR\Common\Auth\UuidUserAccount;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\SystemLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class LocalApiAuthorizationController implements IAuthorizationStrategy
{
    public function __construct(SystemLogger $logger)
    {
    }

    public function shouldProcessRequest(Request $request) : bool
    {
        if ($request->headers->has("APICSRFTOKEN")) {
            $request->attributes->set("_is_local_api", true);
            // this is a local api request, so we should process it
            return true;
        }
        return false;
    }

    /**
     * @param Request $request
     * @return bool
     * @throw UnauthorizedHttpException if the request is not authorized
     */
    public function authorizeRequest(Request $request) : bool {
        // for legacy purposes
        $GLOBALS['is_local_api'] = true;
        // need to check for csrf match when using api locally
        $csrfFail = false;
        $csrfToken = $request->headers->get("APICSRFTOKEN");

        if (empty($csrfToken)) {
            $this->logger->error("OpenEMR Error: internal api failed because csrf token not received");
            $csrfFail = true;
        }

        if ((!$csrfFail) && (!CsrfUtils::verifyCsrfToken($csrfToken, 'api'))) {
            $this->logger->error("OpenEMR Error: internal api failed because csrf token did not match");
            $csrfFail = true;
        }

        if ($csrfFail) {
            $this->logger->error("dispatch.php CSRF failed", ["resource" => $request->getPathInfo()]);
            throw new UnauthorizedHttpException("APICSRFTOKEN", "OpenEMR Error: internal api failed because csrf token did not match");
        }
        $userId = $_SESSION['userId'];
        $uuidToUser = new UuidUserAccount($userId);
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

        $request->attributes->set('userId', $userId);
        $request->attributes->set('clientId', null);
        $request->attributes->set('tokenId', $csrfToken);
        if ($request instanceof HttpRestRequest) {
            $request->setAccessTokenId($csrfToken);
            $request->setRequestUserRole($userRole);
            $request->setRequestUser($user);
        }
    }
}
