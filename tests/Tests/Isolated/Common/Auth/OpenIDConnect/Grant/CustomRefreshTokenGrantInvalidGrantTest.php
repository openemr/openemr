<?php

/**
 * An unusable refresh token is reported as 400 invalid_grant.
 *
 * Regression test for issue #13613. league/oauth2-server 8.x answers an expired,
 * revoked, undecryptable or other client's refresh token with 401 invalid_request;
 * RFC 6749 section 5.2 (and league/oauth2-server 9.0.0) use 400 invalid_grant, which
 * is what spec-compliant clients watch for before sending the user back to sign in.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code <noreply@anthropic.com>
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\OpenIDConnect\Grant;

use Defuse\Crypto\Key;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Nyholm\Psr7\ServerRequest;
use OpenEMR\Common\Auth\OpenIDConnect\Grant\CustomRefreshTokenGrant;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class CustomRefreshTokenGrantInvalidGrantTest extends TestCase
{
    private const CLIENT_ID = 'test-client';

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function deadRefreshTokenProvider(): array
    {
        return [
            'undecryptable' => ['undecryptable', 'Cannot decrypt the refresh token'],
            'issued to another client' => ['other-client', 'Token is not linked to client'],
            'expired' => ['expired', 'Token has expired'],
            'revoked' => ['revoked', 'Token has been revoked'],
        ];
    }

    /**
     * Every unusable refresh token comes back as 400 invalid_grant, keeping the library's hint.
     */
    #[Test]
    #[DataProvider('deadRefreshTokenProvider')]
    public function deadRefreshTokenIsInvalidGrant(string $case, string $hint): void
    {
        $grant = $this->grant(revoked: $case === 'revoked');
        // 'revoked' encrypts a live token; the repository reports it revoked.
        $token = match ($case) {
            'undecryptable' => 'definitely-not-a-valid-refresh-token',
            'other-client' => self::encrypt($grant, ['client_id' => 'another-client', 'expire_time' => time() + 3600]),
            'expired' => self::encrypt($grant, ['client_id' => self::CLIENT_ID, 'expire_time' => time() - 1]),
            default => self::encrypt($grant, ['client_id' => self::CLIENT_ID, 'expire_time' => time() + 3600]),
        };

        $exception = $this->validate($grant, $token);

        self::assertSame('invalid_grant', $exception->getErrorType());
        self::assertSame(400, $exception->getHttpStatusCode());
        self::assertSame(8, $exception->getCode());
        self::assertSame($hint, $exception->getHint());
        self::assertInstanceOf(OAuthServerException::class, $exception->getPrevious());
    }

    /**
     * Other request errors keep the library's error: a missing refresh_token is a malformed request.
     */
    #[Test]
    public function missingRefreshTokenStaysInvalidRequest(): void
    {
        $exception = $this->validate($this->grant(revoked: false), null);

        self::assertSame('invalid_request', $exception->getErrorType());
        self::assertSame(400, $exception->getHttpStatusCode());
        self::assertNull($exception->getPrevious());
    }

    /**
     * A usable refresh token still validates.
     */
    #[Test]
    public function liveRefreshTokenValidates(): void
    {
        $grant = $this->grant(revoked: false);
        $token = self::encrypt($grant, ['client_id' => self::CLIENT_ID, 'expire_time' => time() + 3600]);

        $data = self::validateOldRefreshToken($grant, $this->request($token));

        self::assertIsArray($data);
        self::assertSame(self::CLIENT_ID, $data['client_id']);
    }

    private function validate(CustomRefreshTokenGrant $grant, ?string $token): OAuthServerException
    {
        try {
            self::validateOldRefreshToken($grant, $this->request($token));
        } catch (OAuthServerException $exception) {
            return $exception;
        }
        self::fail('validateOldRefreshToken() accepted the refresh token');
    }

    private function grant(bool $revoked): CustomRefreshTokenGrant
    {
        $repository = $this->createMock(RefreshTokenRepositoryInterface::class);
        $repository->method('isRefreshTokenRevoked')->willReturn($revoked);
        $grant = new CustomRefreshTokenGrant(new Session(new MockArraySessionStorage()), $repository);
        $grant->setEncryptionKey(Key::createNewRandomKey());
        return $grant;
    }

    private function request(?string $token): ServerRequest
    {
        $body = ['grant_type' => 'refresh_token', 'client_id' => self::CLIENT_ID];
        if ($token !== null) {
            $body['refresh_token'] = $token;
        }
        return (new ServerRequest('POST', '/oauth2/default/token'))->withParsedBody($body);
    }

    /**
     * Call the grant's protected validateOldRefreshToken() the way respondToAccessTokenRequest() does.
     */
    private static function validateOldRefreshToken(CustomRefreshTokenGrant $grant, ServerRequest $request): mixed
    {
        return (new \ReflectionMethod($grant, 'validateOldRefreshToken'))->invoke($grant, $request, self::CLIENT_ID);
    }

    /**
     * Encrypt a refresh token payload with the grant's own key, as the library does when it issues one.
     *
     * @param array<string, mixed> $data
     */
    private static function encrypt(CustomRefreshTokenGrant $grant, array $data): string
    {
        $encrypted = (new \ReflectionMethod($grant, 'encrypt'))
            ->invoke($grant, json_encode($data + ['refresh_token_id' => 'rt-1'], JSON_THROW_ON_ERROR));
        self::assertIsString($encrypted);
        return $encrypted;
    }
}
