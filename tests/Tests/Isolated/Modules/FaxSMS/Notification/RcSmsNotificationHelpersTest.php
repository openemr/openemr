<?php

/**
 * Regression guard for the helpers file's own dependency loading.
 *
 * `rc_sms_notification_helpers.php` is loaded from two entry points:
 * the popup `rc_sms_notification.php` (which separately requires
 * `library/appointments.inc.php` before us) and the CLI bridge
 * `run_notifications.php` driven by `bin/console background:services
 * run --name=Notification_Email_Task` (which does not). When the
 * helpers file did not pull `appointments.inc.php` itself, the CLI
 * path threw `Call to undefined function fetchEvents()` from
 * `faxsms_getAlertPatientData()` — and the orchestrator's silent
 * catch in `BackgroundServiceRunner::runOne` (fixed in the sibling
 * commit) recorded `status=error` with no log line, so the regression
 * shipped undetected. See issue #11827.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\FaxSMS\Notification;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('faxsms')]
class RcSmsNotificationHelpersTest extends TestCase
{
    /**
     * Load the helpers file in a fresh process with a stub
     * `appointments.inc.php` on disk (pointed at by `$GLOBALS['srcdir']`)
     * and assert that `fetchEvents()` is defined afterward. If the
     * helpers file ever stops pulling `appointments.inc.php` itself,
     * the CLI background-service path silently breaks again.
     *
     * Process isolation is required because `require_once` is process-
     * scoped: a previous test that already loaded the helpers file
     * (directly or transitively) would leave `function_exists()` true
     * regardless of whether the require was wired up correctly.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testHelpersFileRequiresAppointmentsLibrary(): void
    {
        $tempDir = sys_get_temp_dir() . '/oce_helpers_dep_' . uniqid('', true);
        if (!mkdir($tempDir, 0700, true) && !is_dir($tempDir)) {
            // @codeCoverageIgnoreStart
            // Defensive — only fires if the OS refuses to create a fresh
            // tempdir, which is not a path real CI exercises.
            $this->fail("could not create temp dir {$tempDir}");
            // @codeCoverageIgnoreEnd
        }

        try {
            // Stub appointments.inc.php with just enough to register the
            // symbol the helpers file consumes via fetchEvents(). Avoids
            // dragging in the real file's transitive requires (calendar
            // includes, xl()) which aren't loaded under isolated tests.
            file_put_contents(
                $tempDir . '/appointments.inc.php',
                "<?php\nfunction fetchEvents(): array { return []; }\n",
            );
            $GLOBALS['srcdir'] = $tempDir;

            require_once dirname(__DIR__, 6)
                . '/interface/modules/custom_modules/oe-module-faxsms/library/rc_sms_notification_helpers.php';

            $this->assertTrue(
                function_exists('fetchEvents'),
                'rc_sms_notification_helpers.php must require appointments.inc.php so fetchEvents() is available to faxsms_getAlertPatientData()',
            );
        } finally {
            @unlink($tempDir . '/appointments.inc.php');
            @rmdir($tempDir);
        }
    }
}
