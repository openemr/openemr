<?php

/**
 * Custom Password Grant
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\Grant;

use DateInterval;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use League\OAuth2\Server\RequestEvent;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\System\System;
use Psr\Http\Message\ServerRequestInterface;

class CustomPasswordGrant extends PasswordGrant
{
    /**
     * @var SystemLogger
     */
    private $logger;

    public function __construct(UserRepositoryInterface $userRepository, RefreshTokenRepositoryInterface $refreshTokenRepository)
    {
        $this->logger = new SystemLogger();
        parent::__construct($userRepository, $refreshTokenRepository);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ClientEntityInterface  $client
     *
     * @throws OAuthServerException
     *
     * @return UserEntityInterface
     */
    protected function validateUser(ServerRequestInterface $request, ClientEntityInterface $client)
    {
        $username = $this->getRequestParameter('username', $request);

        if (\is_null($username)) {
            throw OAuthServerException::invalidRequest('username');
        }

        $password = $this->getRequestParameter('password', $request);

        if (\is_null($password)) {
            throw OAuthServerException::invalidRequest('password');
        }

        $userrole = $this->getRequestParameter('user_role', $request);

        if (\is_null($userrole)) {
            throw OAuthServerException::invalidRequest('user_role');
        }

        $email = $this->getRequestParameter('email', $request);

        if (\is_null($email) && ($userrole == 'patient')) {
            throw OAuthServerException::invalidRequest('email');
        }


        $identifier = $this->getIdentifier();
        $user = $this->userRepository->getCustomUserEntityByUserCredentials(
            $userrole,
            $username,
            $password,
            $email,
            $identifier,
            $client,
        );

        if ($user instanceof UserEntityInterface === false) {
            $this->getEmitter()->emit(new RequestEvent(RequestEvent::USER_AUTHENTICATION_FAILED, $request));
            $clientVars = "undefined";
            if (empty($client)) {
                $clientVars = ['id' => $client->getIdentifier(), 'name' => $client->getName(), 'redirectUri' => $client->getRedirectUri()];
            }

            $this->logger->debug(
                "CustomPasswordGrant->validateUser() Failed to find user for request",
                ['userrole' => $userrole,'username' => $username, 'email' => $email, 'identifier' => $identifier
                ,
                'client' => $clientVars]
            );
            throw OAuthServerException::invalidGrant('Failed Authentication');
        }
        $_SESSION['pass_user_id'] = $user->getIdentifier();
        $_SESSION['pass_username'] = $username;
        $_SESSION['pass_user_role'] = $userrole;
        $_SESSION['pass_user_email'] = $email;

        return $user;
    }

    protected function validateClient(ServerRequestInterface $request)
    {
        $client = parent::validateClient($request);
        if (!($client instanceof ClientEntity)) {
            $this->logger->errorLogCaller("client returned was not a valid ClientEntity ", ['client' => $client->getIdentifier()]);
            throw OAuthServerException::invalidClient($request);
        }

        if (!$client->isEnabled()) {
            $this->logger->errorLogCaller("client returned was not enabled", ['client' => $client->getIdentifier()]);
            throw OAuthServerException::invalidClient($request);
        }
        return $client;
    }
}
