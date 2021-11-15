<?php

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\PasswordGrant;
use OAuth2ServerExamples\Repositories\AccessTokenRepository;
use OAuth2ServerExamples\Repositories\ClientRepository;
use OAuth2ServerExamples\Repositories\RefreshTokenRepository;
use OAuth2ServerExamples\Repositories\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use OpenIDConnectServer\IdTokenResponse;
use OpenIDConnectServerExamples\Repositories\IdentityRepository;
use OpenIDConnectServerExamples\Repositories\ScopeRepository;
use OpenIDConnectServer\ClaimExtractor;

include __DIR__ . '/../vendor/autoload.php';

$app = new App([
    // Add the authorization server to the DI container
    AuthorizationServer::class => function () {
        // OpenID Connect Response Type
        $responseType = new IdTokenResponse(new IdentityRepository(), new ClaimExtractor());

        // Setup the authorization server
        $server = new AuthorizationServer(
            new ClientRepository(),                 // instance of ClientRepositoryInterface
            new AccessTokenRepository(),            // instance of AccessTokenRepositoryInterface
            new ScopeRepository(),                  // instance of ScopeRepositoryInterface
            'file://' . __DIR__ . '/../private.key',    // path to private key
            'lxZFUEsBCJ2Yb14IF2ygAHI5N4+ZAUXXaSeeJm6+twsUmIen',      // encryption key
            $responseType
        );

        $grant = new PasswordGrant(
            new UserRepository(),           // instance of UserRepositoryInterface
            new RefreshTokenRepository()    // instance of RefreshTokenRepositoryInterface
        );
        $grant->setRefreshTokenTTL(new \DateInterval('P1M')); // refresh tokens will expire after 1 month

        // Enable the password grant on the server with a token TTL of 1 hour
        $server->enableGrantType(
            $grant,
            new \DateInterval('PT1H') // access tokens will expire after 1 hour
        );

        return $server;
    },
]);

$app->post(
    '/access_token',
    function (ServerRequestInterface $request, ResponseInterface $response) use ($app) {

        /* @var \League\OAuth2\Server\AuthorizationServer $server */
        $server = $app->getContainer()->get(AuthorizationServer::class);

        try {

            // Try to respond to the access token request
            return $server->respondToAccessTokenRequest($request, $response);
        } catch (OAuthServerException $exception) {

            // All instances of OAuthServerException can be converted to a PSR-7 response
            return $exception->generateHttpResponse($response);
        } catch (\Exception $exception) {

            // Catch unexpected exceptions
            $body = $response->getBody();
            $body->write($exception->getMessage());

            return $response->withStatus(500)->withBody($body);
        }
    }
);

$app->run();
