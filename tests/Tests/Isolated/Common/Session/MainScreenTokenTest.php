<?php

/**
 * Main-screen token retry window.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Session;

use OpenEMR\Common\Session\MainScreenToken;
use OpenEMR\Common\Session\MainScreenTokenDecision;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MainScreenTokenTest extends TestCase
{
    private const TOKEN = 'abcdefghijklmnopqrstuvwxyz012345';

    /**
     * @return array<string, array{?string, string, ?int, int, bool, MainScreenTokenDecision}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function decisionProvider(): array
    {
        $now = 1_700_000_000;

        return [
            'missing expected token' => [null, self::TOKEN, null, $now, true, MainScreenTokenDecision::Reject],
            'empty presented token' => [self::TOKEN, '', null, $now, true, MainScreenTokenDecision::Reject],
            'different token of the same length' => [self::TOKEN, substr(self::TOKEN, 0, -1) . '0', null, $now, true, MainScreenTokenDecision::Reject],
            'different length is not compared as a match' => [self::TOKEN, 'short', null, $now, true, MainScreenTokenDecision::Reject],
            'first hit records the time' => [self::TOKEN, self::TOKEN, null, $now, true, MainScreenTokenDecision::AcceptAndStamp],
            'retry inside the window is allowed and does not move the window' => [self::TOKEN, self::TOKEN, $now, $now + MainScreenToken::RETRY_SECONDS, true, MainScreenTokenDecision::Accept],
            'retry one second past the window is rejected' => [self::TOKEN, self::TOKEN, $now, $now + MainScreenToken::RETRY_SECONDS + 1, true, MainScreenTokenDecision::Reject],
            'protection off accepts without a stamp' => [self::TOKEN, self::TOKEN, null, $now, false, MainScreenTokenDecision::Accept],
            'protection off ignores an old stamp' => [self::TOKEN, self::TOKEN, $now - 3600, $now, false, MainScreenTokenDecision::Accept],
        ];
    }

    #[DataProvider('decisionProvider')]
    public function testDecision(
        ?string $expected,
        string $presented,
        ?int $acceptedAt,
        int $now,
        bool $preventRefresh,
        MainScreenTokenDecision $decision,
    ): void {
        $this->assertSame(
            $decision,
            MainScreenToken::decide($expected, $presented, $acceptedAt, $now, $preventRefresh),
        );
    }
}
