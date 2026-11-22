<?php

/**
 * Database tests for the uploaded flag ErxPage sets on the medications and allergies it sent.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Rx\Ensora;

use eRxStore;
use GuzzleHttp\Client;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Rx\Ensora\ErxGlobalsInterface;
use OpenEMR\Rx\Ensora\ErxPage;
use OpenEMR\Rx\Ensora\ErxXmlBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

require_once __DIR__ . '/../../../../../interface/eRxStore.php';

class ErxPageUploadedFlagTest extends TestCase
{
    /** A patient that exists only in this test's lists rows. */
    private int $pid = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pid = random_int(900_000_000, 999_999_999);
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM lists WHERE pid = ?', [$this->pid]);
        parent::tearDown();
    }

    public function testAMedicationEditedWhileTheRequestIsInFlightStaysPending(): void
    {
        $unchanged = $this->addIssue('medication', 'Metformin', null, null, 0);
        $retitled = $this->addIssue('medication', 'lisinopril', '2026-01-02 00:00:00', null, 0);
        $redated = $this->addIssue('medication', 'Atorvastatin', '2026-01-02 00:00:00', null, 0);
        $page = $this->page();
        $page->getXMLBuilder()->getPatientMedication($this->pid, 0, 10);

        // As add_edit_issue.php saves an edit: the new values, and pending upload.
        $this->edit('UPDATE lists SET title = ?, erx_uploaded = ? WHERE id = ?', ['Lisinopril', '0', $retitled]);
        $this->edit('UPDATE lists SET begdate = ?, erx_uploaded = ? WHERE id = ?', ['2026-02-03 00:00:00', '0', $redated]);
        $page->updatePatientData();

        $this->assertSame('1', $this->uploaded($unchanged));
        $this->assertSame('0', $this->uploaded($retitled), 'a change in case is an edit');
        $this->assertSame('0', $this->uploaded($redated));
    }

    public function testAnAllergyEditedWhileTheRequestIsInFlightStaysPending(): void
    {
        $unchanged = $this->addIssue('allergy', 'Penicillin', null, null, 0);
        $commented = $this->addIssue('allergy', 'Latex', null, 'rash', 0);
        $reassessed = $this->addIssue('allergy', 'Sulfa', null, null, 0);
        $page = $this->page();
        $page->getXMLBuilder()->getPatientFreeformAllergy($this->pid);

        $this->edit('UPDATE lists SET comments = ?, erx_uploaded = ? WHERE id = ?', ['hives', '0', $commented]);
        $this->edit('UPDATE lists SET outcome = ?, erx_uploaded = ? WHERE id = ?', [1, '0', $reassessed]);
        $page->updatePatientData();

        $this->assertSame('1', $this->uploaded($unchanged));
        $this->assertSame('0', $this->uploaded($commented));
        $this->assertSame('0', $this->uploaded($reassessed));
    }

    private function page(): ErxPage
    {
        $builder = new ErxXmlBuilder($this->createStub(ErxGlobalsInterface::class), new eRxStore(), new Client(), true);

        return (new ErxPage($builder, new NullLogger()))->setPatientId($this->pid);
    }

    private function addIssue(string $type, string $title, ?string $begdate, ?string $comments, int $outcome): int
    {
        return QueryUtils::sqlInsert(
            <<<'SQL'
            INSERT INTO lists (pid, type, title, begdate, comments, outcome, erx_source, erx_uploaded)
            VALUES (?, ?, ?, ?, ?, ?, '0', '0')
            SQL,
            [$this->pid, $type, $title, $begdate, $comments, $outcome]
        );
    }

    /**
     * @param list<int|string> $binds
     */
    private function edit(string $sql, array $binds): void
    {
        QueryUtils::sqlStatementThrowException($sql, $binds);
    }

    private function uploaded(int $id): string
    {
        $uploaded = QueryUtils::fetchSingleValue('SELECT erx_uploaded FROM lists WHERE id = ?', 'erx_uploaded', [$id]);
        $this->assertIsString($uploaded);

        return $uploaded;
    }
}
