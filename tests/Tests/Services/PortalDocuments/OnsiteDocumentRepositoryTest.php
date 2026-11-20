<?php

/**
 * DB tests for the portal document repository: the list's patient, Locked, record, filter, sort
 * and paging rules, and that request text never changes the SQL.
 *
 * The documents belong to an unused pid and are removed after each test.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\PortalDocuments;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\PortalDocuments\OnsiteDocumentListQuery;
use OpenEMR\Services\PortalDocuments\OnsiteDocumentRepository;
use OpenEMR\Services\PortalDocuments\OnsiteDocumentSort;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OnsiteDocumentRepositoryTest extends TestCase
{
    /** No real patient has these pids. */
    private const PID = 990000101;
    private const OTHER_PID = 990000102;

    private OnsiteDocumentRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new OnsiteDocumentRepository();
        $this->removeDocuments();
    }

    protected function tearDown(): void
    {
        $this->removeDocuments();
    }

    public function testInsertFindUpdateAndDelete(): void
    {
        $id = $this->repository->insert($this->document(['docType' => 'Consent']));

        $found = $this->repository->find($id);
        $this->assertIsArray($found);
        $this->assertSame(array_keys(OnsiteDocumentRepository::COLUMNS), array_keys($found));
        $this->assertSame((string) $id, $found['id']);
        $this->assertSame((string) self::PID, $found['pid'], 'values come back as strings, as the API always returned them');
        $this->assertSame('Consent', $found['docType']);

        $this->repository->update($id, ['denialReason' => 'In Review'] + $found);
        $updated = $this->repository->find($id);
        $this->assertIsArray($updated);
        $this->assertSame('In Review', $updated['denialReason']);
        $this->assertSame('Consent', $updated['docType']);

        $this->repository->delete($id);
        $this->assertNull($this->repository->find($id));
    }

    public function testListIsOnlyThePatientsDocumentsAndHidesLockedOnesByDefault(): void
    {
        $open = $this->repository->insert($this->document(['denialReason' => 'New']));
        $locked = $this->repository->insert($this->document(['denialReason' => 'Locked']));
        $this->repository->insert($this->document(['pid' => self::OTHER_PID]));

        $this->assertSame([(string) $open], $this->ids(new OnsiteDocumentListQuery(self::PID)));
        $this->assertEqualsCanonicalizing(
            [(string) $open, (string) $locked],
            $this->ids(new OnsiteDocumentListQuery(self::PID, includeLocked: true)),
        );
        $this->assertSame(
            [(string) $locked],
            $this->ids(new OnsiteDocumentListQuery(self::PID, recordId: $locked, includeLocked: true)),
        );
    }

    public function testFilterMatchesAnyColumnButTheTemplateData(): void
    {
        $match = $this->repository->insert($this->document(['fileName' => 'Intake Questionnaire']));
        $this->repository->insert($this->document(['fileName' => 'Other', 'templateData' => 'Questionnaire']));

        $this->assertSame([(string) $match], $this->ids(new OnsiteDocumentListQuery(self::PID, filter: 'Questionn')));
        $this->assertSame([], $this->ids(new OnsiteDocumentListQuery(self::PID, filter: 'no such text')));
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function injectionProvider(): array
    {
        return [
            'quote' => ["' OR '1'='1"],
            'comment' => ["x%' OR 1=1 -- "],
            'union' => ["' UNION SELECT id, pid FROM onsite_documents -- "],
        ];
    }

    #[DataProvider('injectionProvider')]
    public function testFilterTextIsMatchedNeverRun(string $payload): void
    {
        $this->repository->insert($this->document());

        $this->assertSame([], $this->ids(new OnsiteDocumentListQuery(self::PID, filter: $payload)));
    }

    public function testSortsByTheColumnThenTheReviewDate(): void
    {
        $b = $this->repository->insert($this->document(['docType' => 'B', 'reviewDate' => '2026-01-01 00:00:00']));
        $a2 = $this->repository->insert($this->document(['docType' => 'A', 'reviewDate' => '2026-02-01 00:00:00']));
        $a1 = $this->repository->insert($this->document(['docType' => 'A', 'reviewDate' => '2026-01-01 00:00:00']));

        $this->assertSame(
            [(string) $a1, (string) $a2, (string) $b],
            $this->ids(new OnsiteDocumentListQuery(self::PID, orderBy: OnsiteDocumentSort::DocType)),
        );
        $this->assertSame(
            [(string) $b, (string) $a2, (string) $a1],
            $this->ids(new OnsiteDocumentListQuery(self::PID, orderBy: OnsiteDocumentSort::DocType, orderDesc: true)),
        );
    }

    /**
     * Rows that tie on the sort keys are ordered by id, so no row repeats or goes missing between pages.
     */
    public function testRowsThatTieOnTheSortKeysKeepOneOrderAcrossPages(): void
    {
        $ids = [];
        foreach (range(1, 4) as $n) {
            $ids[] = (string) $this->repository->insert($this->document());
        }

        $paged = [];
        foreach ([1, 2] as $number) {
            $page = $this->repository->list(new OnsiteDocumentListQuery(self::PID, orderBy: OnsiteDocumentSort::DocType, page: $number, pageSize: 2));
            foreach ($page->rows as $row) {
                $paged[] = $row['id'];
            }
        }

        $this->assertSame($ids, $paged, 'every row once, by id');
        $this->assertSame(
            array_reverse($ids),
            $this->ids(new OnsiteDocumentListQuery(self::PID, orderBy: OnsiteDocumentSort::DocType, orderDesc: true)),
        );
    }

    /**
     * A page reports its rows, the total and where it falls.
     */
    public function testPagesReportTheirPosition(): void
    {
        foreach (range(1, 3) as $n) {
            $this->repository->insert($this->document(['docType' => 'Doc ' . $n]));
        }

        $page = $this->repository->list(new OnsiteDocumentListQuery(self::PID, orderBy: OnsiteDocumentSort::DocType, page: 2, pageSize: 2));
        $this->assertCount(1, $page->rows);
        $this->assertSame('Doc 3', $page->rows[0]['docType']);
        $this->assertSame(3, $page->totalResults);
        $this->assertSame(2, $page->totalPages);
        $this->assertSame(2, $page->pageSize);
        $this->assertSame(2, $page->currentPage);

        $all = $this->repository->list(new OnsiteDocumentListQuery(self::PID));
        $this->assertCount(3, $all->rows);
        $this->assertSame(3, $all->totalResults);
        $this->assertSame(1, $all->totalPages);
        $this->assertSame(3, $all->pageSize);
        $this->assertSame(1, $all->currentPage);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function document(array $overrides = []): array
    {
        return $overrides + [
            'pid' => self::PID,
            'facility' => 0,
            'provider' => 0,
            'encounter' => 0,
            'createDate' => '2026-11-04 10:00:00',
            'docType' => 'Test',
            'patientSignedStatus' => 0,
            'patientSignedTime' => '2026-11-04 10:00:00',
            'authorizeSignedTime' => '2026-11-04 10:00:00',
            'acceptSignedStatus' => 0,
            'authorizingSignator' => '',
            'reviewDate' => '2026-11-04 10:00:00',
            'denialReason' => 'New',
            'authorizedSignature' => '',
            'patientSignature' => '',
            'fullDocument' => '<p>template</p>',
            'fileName' => 'Test',
            'filePath' => '0',
            'templateData' => '',
        ];
    }

    /**
     * @return list<string>
     */
    private function ids(OnsiteDocumentListQuery $query): array
    {
        $ids = [];
        foreach ($this->repository->list($query)->rows as $row) {
            $this->assertIsString($row['id']);
            $ids[] = $row['id'];
        }

        return $ids;
    }

    private function removeDocuments(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM `onsite_documents` WHERE `pid` IN (?, ?)', [self::PID, self::OTHER_PID]);
    }
}
