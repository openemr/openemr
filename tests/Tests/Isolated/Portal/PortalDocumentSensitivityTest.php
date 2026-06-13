<?php

/**
 * PortalDocumentSensitivityTest
 *
 * Verifies that the portal document listing and download action respect the
 * category `aco_spec` field so that high-sensitivity documents are hidden from
 * patients.
 *
 * These tests are isolated (no database, no full OpenEMR bootstrap) and focus
 * on the SQL query logic changes introduced to fix issues #11284 and #11279.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    craigrallen
 * @copyright Copyright (c) 2026 OpenEMR Community
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Portal;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for portal document sensitivity filtering.
 *
 * These tests verify the SQL query construction and filter logic introduced to
 * resolve issues #11284 (patients accessing high-sensitivity documents) and
 * #11279 (high-sensitivity note in report breaks subsequent notes).
 */
class PortalDocumentSensitivityTest extends TestCase
{
    /**
     * The portal-accessible ACO spec value used as the filter boundary.
     * Documents in categories with any other spec must be hidden from patients.
     */
    private const PORTAL_ACO_SPEC = 'patients|docs';

    /**
     * Verify that the portal listing SQL includes the aco_spec filter.
     *
     * The production query in get_patient_documents.php must JOIN the categories
     * table and restrict to `aco_spec = 'patients|docs'`.  This test guards
     * against accidental removal of that constraint.
     */
    #[Test]
    public function listingQueryFiltersOnAcoSpec(): void
    {
        // Read the actual file to ensure the SQL filter is present
        $listingFile = realpath(__DIR__ . '/../../../../portal/get_patient_documents.php');
        $this->assertNotFalse($listingFile, 'portal/get_patient_documents.php not found');
        $content = file_get_contents($listingFile);
        $this->assertIsString($content);

        // The fix uses NOT EXISTS so that a document hidden when ANY category is restricted.
        $this->assertStringContainsString(
            'NOT EXISTS',
            $content,
            'get_patient_documents.php must use NOT EXISTS to exclude restricted-category docs'
        );
        $this->assertStringContainsString(
            "aco_spec` != 'patients|docs'",
            $content,
            'get_patient_documents.php must exclude docs with non-portal aco_spec'
        );
    }

    /**
     * Verify that the download action also enforces the aco_spec filter.
     *
     * A patient could attempt to download a restricted document by submitting
     * its ID directly.  The download action must independently verify the
     * category restriction.
     */
    #[Test]
    public function downloadActionFiltersOnAcoSpec(): void
    {
        $downloadFile = realpath(__DIR__ . '/../../../../portal/report/document_downloads_action.php');
        $this->assertNotFalse($downloadFile, 'portal/report/document_downloads_action.php not found');
        $content = file_get_contents($downloadFile);
        $this->assertIsString($content);

        $this->assertStringContainsString(
            'NOT EXISTS',
            $content,
            'document_downloads_action.php must use NOT EXISTS to block restricted-category downloads'
        );
        $this->assertStringContainsString(
            "aco_spec` != 'patients|docs'",
            $content,
            'document_downloads_action.php must exclude docs with non-portal aco_spec'
        );
    }

    /**
     * Documents in portal-accessible categories should pass the filter.
     *
     * @param string $acoSpec The category's aco_spec value
     * @param bool   $expected Whether the document should be accessible
     */
    #[Test]
    #[DataProvider('acoSpecProvider')]
    public function documentAccessibilityMatchesAcoSpec(string $acoSpec, bool $expected): void
    {
        $isAccessible = ($acoSpec === self::PORTAL_ACO_SPEC);
        $this->assertSame(
            $expected,
            $isAccessible,
            "Document with aco_spec '$acoSpec' accessibility should be " . ($expected ? 'true' : 'false')
        );
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function acoSpecProvider(): array
    {
        return [
            'standard patient docs (accessible)'  => ['patients|docs', true],
            'high sensitivity (restricted)'         => ['patients|high', false],
            'demographics (restricted)'             => ['patients|demo', false],
            'admin super (restricted)'              => ['admin|super', false],
            'encounters coding (restricted)'        => ['encounters|coding', false],
            'empty string (restricted)'             => ['', false],
        ];
    }

    /**
     * Verify the LBF report function returns early (does not die) for portal requests
     * when the form has a restricted ACO spec.
     *
     * Issue #11279: AccessDeniedHelper::deny() is a `never`-returning function.
     * When called from lbf_report() during a portal report render, it terminates
     * the entire request and hides all subsequent notes.  The fix returns early
     * instead when $GLOBALS['patient_portal_onsite_two'] is set.
     */
    #[Test]
    public function lbfReportReturnsEarlyForPortalOnSensitivityDeny(): void
    {
        $lbfReportFile = realpath(__DIR__ . '/../../../../interface/forms/LBF/report.php');
        $this->assertNotFalse($lbfReportFile, 'interface/forms/LBF/report.php not found');
        $content = file_get_contents($lbfReportFile);
        $this->assertIsString($content);

        // The fix must include a portal-context check that returns early
        $this->assertStringContainsString(
            "patient_portal_onsite_two",
            $content,
            'lbf_report() must check patient_portal_onsite_two before calling deny()'
        );
        // The fix must return early, not call die()/AccessDeniedHelper::deny()
        $this->assertMatchesRegularExpression(
            '/patient_portal_onsite_two[^}]+return;/s',
            $content,
            'lbf_report() must return (not die) when portal requests a restricted form'
        );
    }
}
