<?php

/**
 * @package OpenEMR
 * @link https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Common\Calendar;

use PHPUnit\Framework\TestCase;

class PatientFinderMarkupTest extends TestCase
{
    private string $source;

    protected function setUp(): void
    {
        $path = dirname(__DIR__, 5) . '/interface/main/calendar/find_patient_popup.php';
        $source = file_get_contents($path);
        self::assertNotFalse($source);
        $this->source = $source;
    }

    public function testNoResultsAddPatientLinkUsesConfiguredWebrootAndVisibilityGuards(): void
    {
        self::assertMatchesRegularExpression(
            '~<a class="noresult" href="<\?php echo attr\(OEGlobalsBag::getInstance\(\)->getWebRoot\(\)\); \?>/interface/new/new\.php"~',
            $this->source
        );
        self::assertMatchesRegularExpression(
            "~isset\\(\\\$_GET\\['pflag'\\]\\)\\s*\\|\\|\\s*!AclMain::aclCheckCore\\('patients', 'demo', '', \\['write', 'addonly'\\]\\)~",
            $this->source
        );
        self::assertStringContainsString('style="display: none;"', $this->source);
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
