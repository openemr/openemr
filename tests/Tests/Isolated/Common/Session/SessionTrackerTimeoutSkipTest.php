<?php

/**
 * Tests for SessionTracker background-poll timeout skip helpers.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Session;

use OpenEMR\Common\Session\SessionTracker;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SessionTrackerTimeoutSkipTest extends TestCase
{
    /**
     * @return array<string, array{0: array<string, mixed>, 1: string, 2: string, 3: bool, 4?: string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function skipTimeoutResetProvider(): array
    {
        return [
            'explicit request flag' => [
                ['skip_timeout_reset' => '1'],
                '/interface/main/calendar/index.php',
                'GET',
                true,
            ],
            'normal page load' => [
                [],
                '/interface/main/calendar/index.php',
                'GET',
                false,
            ],
            'dated reminders counter' => [
                [],
                '/library/ajax/dated_reminders_counter.php',
                'POST',
                true,
            ],
            'dated reminders ajax post' => [
                [],
                '/interface/main/dated_reminders/dated_reminders.php',
                'POST',
                true,
            ],
            'dated reminders page get is user activity' => [
                [],
                '/interface/main/dated_reminders/dated_reminders.php',
                'GET',
                false,
            ],
            'telehealth invite get poll' => [
                [],
                '/interface/modules/custom_modules/oe-module-telehealth-jse/public/api/invite.php',
                'GET',
                true,
            ],
            'telehealth invite post action' => [
                [],
                '/interface/modules/custom_modules/oe-module-telehealth-jse/public/api/invite.php',
                'POST',
                false,
            ],
            'telehealth batch status get' => [
                [],
                '/interface/modules/custom_modules/oe-module-telehealth-jse/public/api/invite_status_batch.php',
                'GET',
                true,
            ],
            'telehealth patient status get' => [
                [],
                '/interface/modules/custom_modules/oe-module-telehealth-jse/public/api/patient_status.php',
                'GET',
                true,
            ],
            'telehealth session ready is user action' => [
                [],
                '/interface/modules/custom_modules/oe-module-telehealth-jse/public/api/session_ready.php',
                'GET',
                false,
            ],
            'script path with query string' => [
                [],
                '/interface/modules/custom_modules/oe-module-telehealth-jse/public/api/invite.php?pid=2',
                'GET',
                true,
            ],
            'absolute filesystem script path' => [
                [],
                '/var/www/localhost/htdocs/openemr/library/ajax/dated_reminders_counter.php',
                'POST',
                true,
                '',
            ],
            'background service local api' => [
                [],
                '/apis/dispatch.php',
                'POST',
                true,
                '/apis/default/api/background_service/$run',
            ],
            'other local api still counts as activity' => [
                [],
                '/apis/dispatch.php',
                'POST',
                false,
                '/apis/default/api/patient',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $request
     */
    #[DataProvider('skipTimeoutResetProvider')]
    public function testShouldSkipTimeoutReset(
        array $request,
        string $scriptPath,
        string $method,
        bool $expected,
        string $requestUri = ''
    ): void {
        $this->assertSame(
            $expected,
            SessionTracker::shouldSkipTimeoutReset($request, $scriptPath, $method, $requestUri)
        );
    }
}
