<?php

/**
 * Isolated tests for ClientGrantTypePolicy: which grants a client may register for and use.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\OpenIDConnect;

use League\OAuth2\Server\Exception\OAuthServerException;
use OpenEMR\Common\Auth\OpenIDConnect\ClientGrantTypePolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ClientGrantTypePolicyIsolatedTest extends TestCase
{
    /**
     * @return array<string, array{list<string>, string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function grantAllowedProvider(): array
    {
        return [
            'auth code client may use auth code' => [['authorization_code'], 'authorization_code', true],
            'auth code client may not use password' => [['authorization_code'], 'password', false],
            'auth code client may not use client_credentials' => [['authorization_code'], 'client_credentials', false],
            'auth code client may refresh' => [['authorization_code'], 'refresh_token', true],
            'password client may refresh' => [['password'], 'refresh_token', true],
            'password client may not use auth code' => [['password'], 'authorization_code', false],
            'client_credentials client may not refresh' => [['client_credentials'], 'refresh_token', false],
            'client_credentials client may not use password' => [['client_credentials'], 'password', false],
            'explicit refresh_token is honoured' => [['client_credentials', 'refresh_token'], 'refresh_token', true],
            'multi-grant client' => [['authorization_code', 'password', 'client_credentials'], 'password', true],
            'no recorded grants defaults to auth code' => [[], 'authorization_code', true],
            'no recorded grants does not allow password' => [[], 'password', false],
            'unknown recorded values fall back to the default' => [['implicit'], 'authorization_code', true],
            'unknown grant is never allowed' => [['authorization_code'], 'urn:ietf:params:oauth:grant-type:device_code', false],
        ];
    }

    /**
     * @param list<string> $registered
     */
    #[DataProvider('grantAllowedProvider')]
    public function testIsGrantAllowed(array $registered, string $grantType, bool $expected): void
    {
        $this->assertSame($expected, (new ClientGrantTypePolicy())->isGrantAllowed($registered, $grantType));
    }

    /**
     * @return array<string, array{mixed, bool, bool, bool, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function registrationProvider(): array
    {
        return [
            'omitted: public app gets auth code' => [null, false, false, false, ['authorization_code']],
            'omitted: confidential app gets auth code' => [null, true, false, false, ['authorization_code']],
            'omitted: backend services client gets client_credentials' => [null, true, true, true, ['authorization_code', 'client_credentials']],
            'omitted: system scopes without jwks get no client_credentials' => [null, true, true, false, ['authorization_code']],
            'omitted: public app never gets client_credentials' => [null, false, true, true, ['authorization_code']],
            'explicit password is kept' => [['password'], true, false, false, ['password']],
            'explicit list is de-duplicated in order' => [['authorization_code', 'refresh_token', 'authorization_code'], false, false, false, ['authorization_code', 'refresh_token']],
            'explicit client_credentials with jwks' => [['client_credentials'], true, true, true, ['client_credentials']],
        ];
    }

    /**
     * @param list<string> $expected
     */
    #[DataProvider('registrationProvider')]
    public function testResolveRegistrationGrantTypes(mixed $requested, bool $isConfidential, bool $hasSystemScopes, bool $hasJwks, array $expected): void
    {
        $this->assertSame(
            $expected,
            (new ClientGrantTypePolicy())->resolveRegistrationGrantTypes($requested, $isConfidential, $hasSystemScopes, $hasJwks)
        );
    }

    /**
     * @return array<string, array{mixed, bool, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function invalidRegistrationProvider(): array
    {
        return [
            'not an array' => ['authorization_code', true, true],
            'empty array' => [[], true, true],
            'unsupported grant' => [['implicit'], true, true],
            'non-string entry' => [[42], true, true],
            'client_credentials without jwks' => [['client_credentials'], true, false],
            'client_credentials for a public client' => [['client_credentials'], false, true],
            'refresh_token alone' => [['refresh_token'], true, true],
        ];
    }

    #[DataProvider('invalidRegistrationProvider')]
    public function testInvalidRegistrationIsRejected(mixed $requested, bool $isConfidential, bool $hasJwks): void
    {
        try {
            (new ClientGrantTypePolicy())->resolveRegistrationGrantTypes($requested, $isConfidential, true, $hasJwks);
            $this->fail('expected invalid_client_metadata');
        } catch (OAuthServerException $exception) {
            $this->assertSame('invalid_client_metadata', $exception->getErrorType());
            $this->assertSame(400, $exception->getHttpStatusCode());
        }
    }

    public function testUnauthorizedClientIsRfc6749Error(): void
    {
        $exception = (new ClientGrantTypePolicy())->unauthorizedClient('password');
        $this->assertSame('unauthorized_client', $exception->getErrorType());
        $this->assertSame(400, $exception->getHttpStatusCode());
    }

    public function testEffectiveGrantTypesDropsUnknownValues(): void
    {
        $this->assertSame(
            ['password', 'client_credentials'],
            (new ClientGrantTypePolicy())->effectiveGrantTypes(['implicit', 'password', 'client_credentials'])
        );
    }
}
