<?php

/**
 * Covers the patient-scoped template fetch path:
 *   - a row with pid=0 is visible to every caller (shared template)
 *   - a row with a positive pid is visible only to the matching caller
 *   - a row with a positive pid returns null for other patient-id contexts
 *
 * The underlying ID-only `fetchTemplate()` method is still available for
 * admin/profile flows that already know the owner; this test covers the
 * renderer-facing `fetchTemplateForPatient()` seam used by the portal and
 * shared renderers.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\DocumentTemplates;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\DocumentTemplates\DocumentTemplateService;
use PHPUnit\Framework\TestCase;

class DocumentTemplateServicePatientScopeTest extends TestCase
{
    private const TEST_PROFILE = 'vr7f-template-scope-test';

    protected function setUp(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM document_templates WHERE profile = ?',
            [self::TEST_PROFILE]
        );
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM document_templates WHERE profile = ?',
            [self::TEST_PROFILE]
        );
    }

    public function testSharedTemplateIsVisibleToAnyCaller(): void
    {
        $sharedId = $this->seedTemplate(pid: 0, name: 'shared_template', content: 'SHARED');
        $svc = new DocumentTemplateService();

        foreach ([1, 7, 0] as $callerPid) {
            $row = $svc->fetchTemplateForPatient($sharedId, $callerPid);
            self::assertIsArray($row, "caller pid=$callerPid should see shared row");
            self::assertSame('SHARED', $row['template_content']);
        }
    }

    public function testOwnedTemplateIsVisibleToMatchingPid(): void
    {
        $ownedId = $this->seedTemplate(pid: 1, name: 'pid1_template', content: 'PID1');
        $svc = new DocumentTemplateService();

        $row = $svc->fetchTemplateForPatient($ownedId, 1);
        self::assertIsArray($row);
        self::assertSame('PID1', $row['template_content']);
    }

    public function testOwnedTemplateIsHiddenFromDifferentPid(): void
    {
        $ownedId = $this->seedTemplate(pid: 1, name: 'pid1_only', content: 'PID1_ONLY');
        $svc = new DocumentTemplateService();

        $this->assertNull($svc->fetchTemplateForPatient($ownedId, 2));
    }

    public function testOwnedTemplateIsHiddenFromUnauthenticatedZeroPid(): void
    {
        $ownedId = $this->seedTemplate(pid: 1, name: 'pid1_private', content: 'PID1_PRIVATE');
        $svc = new DocumentTemplateService();

        $this->assertNull($svc->fetchTemplateForPatient($ownedId, 0));
    }

    public function testUnknownTemplateIdReturnsFalseFromUnderlyingFetch(): void
    {
        $svc = new DocumentTemplateService();
        $this->assertFalse($svc->fetchTemplateForPatient(999999, 1));
    }

    private function seedTemplate(int $pid, string $name, string $content): int
    {
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO document_templates (pid, profile, category, template_name, status, send_date, size, mime, template_content) '
            . 'VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?)',
            [$pid, self::TEST_PROFILE, 'Test', $name, 'active', strlen($content), 'text/html', $content]
        );
        $row = QueryUtils::querySingleRow(
            'SELECT MAX(id) AS id FROM document_templates WHERE template_name = ? AND profile = ?',
            [$name, self::TEST_PROFILE]
        );
        return isset($row['id']) && is_numeric($row['id']) ? (int) $row['id'] : 0;
    }
}
