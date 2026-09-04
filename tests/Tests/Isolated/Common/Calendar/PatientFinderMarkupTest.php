<?php

/**
 * @package OpenEMR
 * @link https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Common\Calendar;

use OpenEMR\Common\Calendar\PatientFinderView;
use PHPUnit\Framework\TestCase;

final class PatientFinderMarkupTest extends TestCase
{
    private string $source;

    protected function setUp(): void
    {
        $path = dirname(__DIR__, 5) . '/interface/main/calendar/find_patient_popup.php';
        $source = file_get_contents($path);
        self::assertNotFalse($source);
        $this->source = $source;
    }

    public function testViewBuildsUrlsForRootInstallation(): void
    {
        self::assertSame('/library/js/calendar-patient-finder.js', PatientFinderView::scriptUrl(''));
        self::assertSame('/interface/new/new.php', PatientFinderView::addPatientUrl(''));
    }

    public function testViewBuildsUrlsForConfiguredWebroot(): void
    {
        self::assertSame('/openemr/library/js/calendar-patient-finder.js', PatientFinderView::scriptUrl('/openemr'));
        self::assertSame('/openemr/interface/new/new.php', PatientFinderView::addPatientUrl('/openemr'));
    }

    public function testPflagHidesLinkWithoutCallingAcl(): void
    {
        $aclCalled = false;

        $style = PatientFinderView::addPatientVisibilityStyle(true, static function () use (&$aclCalled): bool {
            $aclCalled = true;
            return true;
        });

        self::assertSame(' style="display: none;"', $style);
        self::assertFalse($aclCalled);
    }

    public function testDeniedAclHidesLink(): void
    {
        self::assertSame(
            ' style="display: none;"',
            PatientFinderView::addPatientVisibilityStyle(false, static fn(): bool => false)
        );
    }

    public function testAllowedAclLeavesStyleAbsent(): void
    {
        self::assertSame('', PatientFinderView::addPatientVisibilityStyle(false, static fn(): bool => true));
    }

    public function testPageUsesViewHelperAndExistingAclContract(): void
    {
        self::assertStringContainsString('PatientFinderView::scriptUrl(', $this->source);
        self::assertStringContainsString('PatientFinderView::addPatientUrl(', $this->source);
        self::assertMatchesRegularExpression(
            "~\\\$canAddPatient = static fn\\(\\): bool => AclMain::aclCheckCore\\(\\s*'patients',\\s*'demo',\\s*'',\\s*\\['write', 'addonly'\\]\\s*\\);~",
            $this->source
        );
        self::assertMatchesRegularExpression(
            "~PatientFinderView::addPatientVisibilityStyle\\(isset\\(\\\$_GET\\['pflag'\\]\\), \\\$canAddPatient\\)~",
            $this->source
        );
    }

    public function testNoResultsClickUsesNavigationHelperWithoutLegacyAppointmentSubmission(): void
    {
        self::assertMatchesRegularExpression(
            '~\\$\\("\\.noresult"\\)\\.click\\(function \\(event\\) \\{\\s*event\\.preventDefault\\(\\);\\s*OpenEMRCalendarPatientFinder\\.openAddPatient\\(window, this\\.href, function \\(\\) \\{\\s*dlgclose\\(\\);~',
            $this->source
        );
        self::assertStringNotContainsString('opener.document.theform', $this->source);
        self::assertStringNotContainsString('resname', $this->source);
    }
}
