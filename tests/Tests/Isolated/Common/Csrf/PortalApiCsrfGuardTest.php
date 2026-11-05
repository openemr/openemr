<?php

/**
 * Tests for the patient portal API CSRF check.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Csrf;

use OpenEMR\Common\Csrf\CsrfInvalidException;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Csrf\PortalApiCsrfGuard;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class PortalApiCsrfGuardTest extends TestCase
{
    private Session $session;

    protected function setUp(): void
    {
        $this->session = new Session(new MockArraySessionStorage());
        CsrfUtils::setupCsrfKey($this->session);
    }

    /**
     * @return array<string, array{string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function methodProvider(): array
    {
        return [
            'GET' => ['GET', false],
            'HEAD' => ['HEAD', false],
            'OPTIONS' => ['OPTIONS', false],
            'lower-case get' => ['get', false],
            'POST' => ['POST', true],
            'PUT' => ['PUT', true],
            'DELETE' => ['DELETE', true],
            'PATCH' => ['PATCH', true],
            'unknown method' => ['PURGE', true],
        ];
    }

    #[DataProvider('methodProvider')]
    public function testOnlyReadOnlyMethodsSkipTheToken(string $method, bool $requiresToken): void
    {
        $this->assertSame($requiresToken, PortalApiCsrfGuard::requiresToken($method));
    }

    public function testReadOnlyRequestPassesWithoutAToken(): void
    {
        PortalApiCsrfGuard::assertValid('GET', null, $this->session);
        $this->addToAssertionCount(1);
    }

    #[DataProvider('writeMethodProvider')]
    public function testWriteWithTheSessionTokenPasses(string $method): void
    {
        PortalApiCsrfGuard::assertValid($method, CsrfUtils::collectCsrfToken($this->session), $this->session);
        $this->addToAssertionCount(1);
    }

    /**
     * @return array<string, array{string, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function badTokenProvider(): array
    {
        return [
            'POST without a token' => ['POST', null],
            'PUT with an empty token' => ['PUT', ''],
            'DELETE with a wrong token' => ['DELETE', str_repeat('0', 40)],
        ];
    }

    #[DataProvider('badTokenProvider')]
    public function testWriteWithoutTheSessionTokenIsRefused(string $method, ?string $token): void
    {
        $this->expectException(CsrfInvalidException::class);
        PortalApiCsrfGuard::assertValid($method, $token, $this->session);
    }

    public function testTokenFromAnotherSessionIsRefused(): void
    {
        $other = new Session(new MockArraySessionStorage());
        CsrfUtils::setupCsrfKey($other);

        $this->expectException(CsrfInvalidException::class);
        PortalApiCsrfGuard::assertValid('PUT', CsrfUtils::collectCsrfToken($other), $this->session);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function writeMethodProvider(): array
    {
        return [
            'POST' => ['POST'],
            'PUT' => ['PUT'],
            'DELETE' => ['DELETE'],
        ];
    }
}
