<?php

/**
 * Isolated tests for the Fax/SMS module lifecycle helpers.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\FaxSMS;

use OpenEMR\Modules\FaxSMS\ModuleLifecycleState;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../../interface/modules/custom_modules/oe-module-faxsms/src/ModuleLifecycleState.php';

class ModuleLifecycleStateTest extends TestCase
{
    public function testDecodeSettingsReturnsSavedSettings(): void
    {
        self::assertSame(
            ['oefax_enable_fax' => '1', 'oefax_enable_sms' => '0'],
            ModuleLifecycleState::decodeSettings('{"oefax_enable_fax":"1","oefax_enable_sms":"0"}')
        );
    }

    /**
     * @return array<string, array{mixed}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unusableStoredValueProvider(): array
    {
        return [
            'nothing stored' => [null],
            'not a string' => [42],
            'invalid json' => ['{not json'],
            'json scalar' => ['"text"'],
        ];
    }

    #[DataProvider('unusableStoredValueProvider')]
    public function testDecodeSettingsReturnsEmptyArrayForUnusableValues(mixed $stored): void
    {
        self::assertSame([], ModuleLifecycleState::decodeSettings($stored));
    }

    public function testTaskNamesKeepsOnlyThisModulesTasks(): void
    {
        $rows = [
            ['name' => 'Notification_SMS_Task'],
            ['name' => 'phimail'],
            ['name' => 'Notification_Email_Task'],
            ['other' => 'Notification_SMS_Task'],
            'not a row',
        ];

        self::assertSame(
            ['Notification_SMS_Task', 'Notification_Email_Task'],
            ModuleLifecycleState::taskNames($rows)
        );
    }

    public function testTaskNamesIsEmptyWhenNoTaskWasActive(): void
    {
        self::assertSame([], ModuleLifecycleState::taskNames([]));
    }

    public function testTasksToRestoreReturnsSavedTasks(): void
    {
        self::assertSame(
            ['Notification_Email_Task'],
            ModuleLifecycleState::tasksToRestore('["Notification_Email_Task"]')
        );
    }

    public function testTasksToRestoreIgnoresUnknownNamesAndBadData(): void
    {
        self::assertSame(
            ['Notification_SMS_Task'],
            ModuleLifecycleState::tasksToRestore('["phimail", 7, "Notification_SMS_Task"]')
        );
        self::assertSame([], ModuleLifecycleState::tasksToRestore(null));
        self::assertSame([], ModuleLifecycleState::tasksToRestore('[]'));
    }
}
