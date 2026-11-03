<?php

/**
 * The request's patient filter, as AbstractQdmService::executeQuery() applies it: to the rows a
 * service's SQL returns, whatever that SQL holds. A WHERE only inside a join, an OR at the top
 * level, or a GROUP BY doesn't change which patients' rows come back.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Qdm;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\Qdm\Interfaces\QdmRequestInterface;
use OpenEMR\Services\Qdm\QdmRecord;
use OpenEMR\Services\Qdm\QdmRequestOne;
use OpenEMR\Services\Qdm\QdmRequestSome;
use OpenEMR\Services\Qdm\Services\AbstractQdmService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AbstractQdmServiceDatabaseTest extends TestCase
{
    // Synthetic patients; no patient_data rows are needed, and every row made here is removed.
    private const PID = 990620;
    private const OTHER_PID = 990621;
    private const REASON = 'qdm patient filter test';

    protected function setUp(): void
    {
        $this->assertSame(0, $this->rowsLeft(), 'no rows for the synthetic patients before the test');
        foreach ([self::PID => 99062001, self::OTHER_PID => 99062101] as $pid => $encounter) {
            QueryUtils::sqlInsert(
                "INSERT INTO `form_encounter` (`date`, `reason`, `pid`, `encounter`) VALUES ('2026-03-10 09:00:00', ?, ?, ?)",
                [self::REASON, $pid, $encounter],
            );
        }
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM `form_encounter` WHERE `pid` IN (?, ?)', [self::PID, self::OTHER_PID]);
        $this->assertSame(0, $this->rowsLeft(), 'the synthetic rows are removed');
    }

    /**
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     * @return \Generator<string, array{string}>
     */
    public static function serviceQueries(): \Generator
    {
        // No WHERE of its own, and a NOT EXISTS in a join's ON clause: a filter added after a WHERE found
        // anywhere in the text would land in that ON clause, and the LEFT JOIN would then keep every
        // patient's visits. EncounterService's query has the same shape, its only WHERE in a subquery.
        yield 'a WHERE only inside a join' => ["SELECT FE.pid, FE.encounter
            FROM form_encounter FE
            LEFT JOIN procedure_order O ON O.encounter_id = FE.encounter AND NOT EXISTS (
                SELECT 1 FROM forms WHERE forms.form_id = O.procedure_order_id AND forms.pid = O.patient_id AND forms.deleted = 1
            )"];
        // An AND added after it would bind to the second alternative only.
        yield 'an OR at the top level' => ["SELECT pid, encounter FROM form_encounter
            WHERE reason = '" . self::REASON . "' OR reason = 'another reason'"];
        // An AND added after it would land in the GROUP BY, grouping by a condition instead of filtering.
        yield 'a GROUP BY' => ["SELECT pid, MAX(encounter) AS encounter FROM form_encounter
            WHERE reason = '" . self::REASON . "' GROUP BY pid"];
    }

    #[DataProvider('serviceQueries')]
    public function testOnlyTheRequestedPatientsRowsAreRead(string $sql): void
    {
        $this->assertSame([self::PID], $this->pids($sql, new QdmRequestOne((string)self::PID)));
        $this->assertSame([self::PID, self::OTHER_PID], $this->pids($sql, new QdmRequestSome([self::PID, self::OTHER_PID])));
    }

    /** @return list<int> the pid of each row the service reads, sorted */
    private function pids(string $sql, QdmRequestInterface $request): array
    {
        $service = new class ($request, new CodeTypesService()) extends AbstractQdmService {
            public string $sql = '';

            public function getSqlStatement(): string
            {
                return $this->sql;
            }

            public function makeQdmModel(QdmRecord $recordObj): never
            {
                throw new \LogicException('This service only reads rows.');
            }
        };
        $service->sql = $sql;
        $result = $service->executeQuery();
        $this->assertInstanceOf(\ADORecordSet::class, $result);
        $pids = [];
        while (is_array($row = QueryUtils::fetchArrayFromResultSet($result))) {
            $pid = $row['pid'] ?? null;
            $this->assertIsNumeric($pid);
            $pids[] = (int)$pid;
        }
        sort($pids);
        return $pids;
    }

    private function rowsLeft(): int
    {
        $n = QueryUtils::fetchSingleValue('SELECT COUNT(*) AS n FROM `form_encounter` WHERE `pid` IN (?, ?)', 'n', [self::PID, self::OTHER_PID]);
        $this->assertIsNumeric($n);
        return (int)$n;
    }
}
