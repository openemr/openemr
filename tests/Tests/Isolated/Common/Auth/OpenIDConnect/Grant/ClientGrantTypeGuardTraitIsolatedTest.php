<?php

/**
 * Isolated tests for ClientGrantTypeGuardTrait and ClientEntity grant-type storage.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\OpenIDConnect\Grant;

use League\OAuth2\Server\Exception\OAuthServerException;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Grant\ClientGrantTypeGuardTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ClientGrantTypeGuardTraitIsolatedTest extends TestCase
{
    private function guard(): object
    {
        return new class {
            use ClientGrantTypeGuardTrait;

            public function check(ClientEntity $client, string $grantType, LoggerInterface $logger, ?string $redirectUri = null): void
            {
                $this->assertClientMayUseGrant($client, $grantType, $logger, $redirectUri);
            }
        };
    }

    /**
     * @param string|array<array-key, mixed>|null $grantTypes
     */
    private function client(string|array|null $grantTypes): ClientEntity
    {
        $client = new ClientEntity();
        $client->setIdentifier('guard-test-client');
        $client->setGrantTypes($grantTypes);
        return $client;
    }

    public function testRegisteredGrantPasses(): void
    {
        $guard = $this->guard();
        $this->assertTrue(method_exists($guard, 'check'));
        $guard->check($this->client('authorization_code|password'), 'password', new NullLogger());
        $this->addToAssertionCount(1);
    }

    public function testUnregisteredGrantIsRejectedAndLogged(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error');
        $guard = $this->guard();
        $this->assertTrue(method_exists($guard, 'check'));

        try {
            $guard->check($this->client('authorization_code'), 'password', $logger);
            $this->fail('expected unauthorized_client');
        } catch (OAuthServerException $exception) {
            $this->assertSame('unauthorized_client', $exception->getErrorType());
            $this->assertSame(400, $exception->getHttpStatusCode());
        }
    }

    public function testRedirectUriIsCarriedForTheAuthorizeEndpoint(): void
    {
        $guard = $this->guard();
        $this->assertTrue(method_exists($guard, 'check'));
        try {
            $guard->check($this->client('client_credentials'), 'authorization_code', new NullLogger(), 'https://client.example/cb');
            $this->fail('expected unauthorized_client');
        } catch (OAuthServerException $exception) {
            $this->assertSame('unauthorized_client', $exception->getErrorType());
            $this->assertSame('https://client.example/cb', $exception->getRedirectUri());
        }
    }

    public function testClientEntityParsesStoredGrantTypes(): void
    {
        $this->assertSame(['authorization_code', 'password'], $this->client('authorization_code|password')->getGrantTypes());
        $this->assertSame(['client_credentials'], $this->client(['client_credentials', '', 7])->getGrantTypes());
        $this->assertSame([], $this->client(null)->getGrantTypes());
        $this->assertSame([], $this->client('')->getGrantTypes());
    }
}
