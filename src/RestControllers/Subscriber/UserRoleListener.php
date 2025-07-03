<?php

namespace OpenEMR\RestControllers\Subscriber;

use OpenEMR\Common\Auth\UuidUserAccount;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserRoleListener
{
    public function __construct(private SystemLogger $logger)
    {
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->attributes->has('userId')) {
            return; // No userId attribute, so nothing to do here
        }
        $userId = $request->attributes->get('userId');
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

        // TODO: @adunsulag need to add in the user role verification logic back here

        $this->setupSessionForUserRole($userRole, $user, $request);

    }
}
