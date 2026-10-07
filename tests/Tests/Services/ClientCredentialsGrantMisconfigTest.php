<?php

/**
 * Unit-level coverage for CustomClientCredentialsGrant defensive-guard
 * branches that only fire under misconfiguration and are unreachable
 * via HTTP in a wired-up deployment:
 *
 *   - line 156: getClientCredentials() when jwtAuthService is not set
 *   - line 208: validateClient()      when jwtAuthService is not set
 *   - line 108: issueAccessToken()    when userService returns no system user
 *
 * These constructor-injection-mismatch guards need direct instantiation
 * plus reflection to reach the protected methods.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use DateInterval;
use League\OAuth2\Server\Exception\OAuthServerException;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Grant\CustomClientCredentialsGrant;
use OpenEMR\Services\UserService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

class ClientCredentialsGrantMisconfigTest extends TestCase
{
    #[Test]
    public function testGetClientCredentialsWithoutJwtServiceThrowsInvalidRequest(): void
    {
        // Construct the grant but do NOT call setJWTAuthenticationService.
        // getClientCredentials() should hit the not-set guard at line 154
        // and throw invalidRequest("client_assertion_type", ...).
        $grant = new CustomClientCredentialsGrant(
            new Session(new MockFileSessionStorage()),
            'https://oauth.example/token'
        );

        $request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);

        $method = new ReflectionMethod($grant, 'getClientCredentials');
        try {
            $method->invoke($grant, $request);
            $this->fail('Expected OAuthServerException when jwtAuthService is not set');
        } catch (OAuthServerException $e) {
            $this->assertSame('invalid_request', $e->getErrorType());
        }
    }

    #[Test]
    public function testValidateClientWithoutJwtServiceThrowsInvalidRequest(): void
    {
        // Same shape as above but through the validateClient entry point
        // — this path passes through getClientCredentials() first, so the
        // guard at line 154 fires. Locks in the invariant that BOTH
        // entry points reject without the service.
        $grant = new CustomClientCredentialsGrant(
            new Session(new MockFileSessionStorage()),
            'https://oauth.example/token'
        );

        $request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);

        $method = new ReflectionMethod($grant, 'validateClient');
        try {
            $method->invoke($grant, $request);
            $this->fail('Expected OAuthServerException when jwtAuthService is not set');
        } catch (OAuthServerException $e) {
            $this->assertSame('invalid_request', $e->getErrorType());
        }
    }

    #[Test]
    public function testIssueAccessTokenWithMissingSystemUserThrowsServerError(): void
    {
        // CustomClientCredentialsGrant::issueAccessToken (line 100-111):
        // when userIdentifier is null the grant looks up the OpenEMR
        // "system user" via UserService::getSystemUser(). If that
        // returns a row without a uuid, the throw at line 108 fires.
        // Inject a UserService double that returns an empty array to
        // exercise that path without touching the real users table.
        $grant = new CustomClientCredentialsGrant(
            new Session(new MockFileSessionStorage()),
            'https://oauth.example/token'
        );

        $userServiceStub = $this->createMock(UserService::class);
        $userServiceStub->method('getSystemUser')->willReturn([]);
        $grant->setUserService($userServiceStub);

        // Set the extended session data expected by issueAccessToken so
        // json_encode doesn't fail on binary session values.
        $reflectionSession = new ReflectionProperty($grant, 'session');
        /** @var Session $session */
        $session = $reflectionSession->getValue($grant);
        $session->set('csrf_private_key', 'ignored-and-removed-inside-issueAccessToken');

        $client = $this->createMock(ClientEntity::class);
        $client->method('getIdentifier')->willReturn('some-client-id');

        $method = new ReflectionMethod($grant, 'issueAccessToken');
        try {
            $method->invoke($grant, new DateInterval('PT1H'), $client, null, []);
            $this->fail('Expected OAuthServerException::serverError when system user is missing');
        } catch (OAuthServerException $e) {
            $this->assertSame('server_error', $e->getErrorType());
        }
    }
}
