<?php

/**
 * Isolated tests for CsrfUtils
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Csrf;

use OpenEMR\Common\Csrf\CsrfUtils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CsrfUtilsTest extends TestCase
{
    public function testCsrfViolationSetsHttp403(): void
    {
        CsrfUtils::csrfViolation(toScreen: false, toLog: false);
        $this->assertSame(403, http_response_code());
    }

    public function testRoundTrip(): void
    {
        $session = $this->createSessionStub();
        CsrfUtils::setupCsrfKey($session);
        $token = CsrfUtils::collectCsrfToken($session);

        $this->assertTrue(CsrfUtils::verifyCsrfToken($token, $session));
    }

    public function testWrongTokenRejected(): void
    {
        $session = $this->createSessionStub();
        CsrfUtils::setupCsrfKey($session);

        $this->assertFalse(CsrfUtils::verifyCsrfToken('bad-token', $session));
    }

    public function testDifferentSubjectsProduceDifferentTokens(): void
    {
        $session = $this->createSessionStub();
        CsrfUtils::setupCsrfKey($session);

        $default = CsrfUtils::collectCsrfToken($session);
        $api = CsrfUtils::collectCsrfToken($session, 'api');

        $this->assertNotSame($default, $api);
    }

    public function testCollectCsrfTokenThrowsWithoutKey(): void
    {
        $session = $this->createSessionStub();

        $this->expectException(\RuntimeException::class);
        CsrfUtils::collectCsrfToken($session);
    }

    public function testTokenStability(): void
    {
        $session = $this->createSessionStub();
        CsrfUtils::setupCsrfKey($session);

        $first = CsrfUtils::collectCsrfToken($session);
        $second = CsrfUtils::collectCsrfToken($session);

        $this->assertSame($first, $second);
    }

    public function testVerifyWithCorrectSubject(): void
    {
        $session = $this->createSessionStub();
        CsrfUtils::setupCsrfKey($session);

        $apiToken = CsrfUtils::collectCsrfToken($session, 'api');

        $this->assertTrue(CsrfUtils::verifyCsrfToken($apiToken, $session, 'api'));
        $this->assertFalse(CsrfUtils::verifyCsrfToken($apiToken, $session));
    }

    private function createSessionStub(): SessionInterface
    {
        $store = [];
        $session = $this->createStub(SessionInterface::class);
        $session->method('set')
            ->willReturnCallback(function (string $key, mixed $value) use (&$store): void {
                $store[$key] = $value;
            });
        $session->method('get')
            ->willReturnCallback(function (string $key, mixed $default = null) use (&$store): mixed {
                return $store[$key] ?? $default;
            });
        return $session;
    }
}
