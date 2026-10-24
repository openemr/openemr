<?php

/**
 * Database tests for linking an issue to an encounter when the link already exists.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\PatientIssuesService;
use PHPUnit\Framework\TestCase;

class PatientIssuesServiceLinkTest extends TestCase
{
    /** A patient, encounter and issue that exist only in this test's issue_encounter rows. */
    private string $pid = '';
    private string $encounter = '';
    private string $issueId = '';

    protected function setUp(): void
    {
        parent::setUp();
        $n = random_int(900_000_000, 999_999_999);
        $this->pid = (string) $n;
        $this->encounter = (string) ($n - 1);
        $this->issueId = (string) ($n - 2);
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM uuid_registry WHERE uuid IN (SELECT uuid FROM issue_encounter WHERE pid = ?)',
            [$this->pid]
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM issue_encounter WHERE pid = ?', [$this->pid]);
        parent::tearDown();
    }

    public function testLinkingAgainReturnsTheExistingLink(): void
    {
        $service = new PatientIssuesService();

        $first = $service->linkIssueToEncounter($this->pid, $this->encounter, $this->issueId, 1);
        $second = $service->linkIssueToEncounter($this->pid, $this->encounter, $this->issueId, 1);

        $this->assertNotSame('', $first);
        $this->assertSame($first, $second);
        $this->assertCount(1, $this->links());
    }

    public function testALinkSavedWithoutAUuidIsReusedAndGivenOne(): void
    {
        // As interface/forms/newGroupEncounter/save.php saves a link.
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO issue_encounter (pid, list_id, encounter) VALUES (?, ?, ?)',
            [$this->pid, $this->issueId, $this->encounter]
        );

        $uuid = (new PatientIssuesService())->linkIssueToEncounter($this->pid, $this->encounter, $this->issueId, 1);

        $links = $this->links();
        $this->assertCount(1, $links);
        $this->assertNotSame('', $uuid);
        $this->assertSame($uuid, $links[0]['uuid']);
        $this->assertCount(
            1,
            QueryUtils::fetchRecords("SELECT 1 FROM uuid_registry WHERE uuid = ? AND table_name = 'issue_encounter'", [$uuid]),
            'the new uuid is registered once'
        );
    }

    /**
     * @return list<array<mixed>>
     */
    private function links(): array
    {
        return QueryUtils::fetchRecords('SELECT id, uuid FROM issue_encounter WHERE pid = ?', [$this->pid]);
    }
}
