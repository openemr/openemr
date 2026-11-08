<?php

/**
 * ListService Integration Tests
 *
 * Covers the patient issue (lists) rows behind the Standard API
 * medication, surgery and dental_issue endpoints.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Lavoy <david@p.industries>
 * @copyright Copyright (c) 2026 P Industries
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\ListService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ListServiceTest extends TestCase
{
    private const TEST_PID = 9876501;

    private ListService $service;

    protected function setUp(): void
    {
        $this->service = new ListService();
    }

    protected function tearDown(): void
    {
        QueryUtils::fetchRecordsNoLog("DELETE FROM `lists` WHERE `pid` = ?", [self::TEST_PID]);
    }

    private function insertMedication(): int
    {
        $id = $this->service->insert([
            'pid' => self::TEST_PID,
            'type' => 'medication',
            'title' => 'test-fixture-ListService medication',
            'begdate' => '2026-01-01 00:00:00',
            'enddate' => null,
            'diagnosis' => null,
        ]);
        $this->assertIsNumeric($id);

        return (int) $id;
    }

    #[Test]
    public function insertAssignsUuid(): void
    {
        $id = $this->insertMedication();

        $row = QueryUtils::querySingleRow("SELECT `uuid` FROM `lists` WHERE `id` = ?", [$id]);
        $this->assertIsArray($row);
        $this->assertIsString($row['uuid']);
        $this->assertSame(16, strlen($row['uuid']));
    }

    #[Test]
    public function getAllWorksRightAfterInsert(): void
    {
        $id = $this->insertMedication();

        $rows = $this->service->getAll(self::TEST_PID, 'medication');

        $this->assertCount(1, $rows);
        $row = $rows[0];
        $this->assertIsArray($row);
        $this->assertEquals($id, $row['id']);
        $this->assertIsString($row['uuid']);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $row['uuid']);
    }

    #[Test]
    public function getOneReturnsUuidAsStringSoItCanBeJsonEncoded(): void
    {
        $id = $this->insertMedication();

        $row = $this->service->getOne(self::TEST_PID, 'medication', $id);

        $this->assertIsArray($row);
        $this->assertIsString($row['uuid']);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $row['uuid']);
        $this->assertNotFalse(json_encode($row));
    }

    #[Test]
    public function rowsWithoutUuidDoNotThrow(): void
    {
        $id = $this->insertMedication();
        QueryUtils::sqlStatementThrowException("UPDATE `lists` SET `uuid` = NULL WHERE `id` = ?", [$id]);

        $rows = $this->service->getAll(self::TEST_PID, 'medication');
        $row = $this->service->getOne(self::TEST_PID, 'medication', $id);

        $this->assertIsArray($rows[0]);
        $this->assertNull($rows[0]['uuid']);
        $this->assertIsArray($row);
        $this->assertNull($row['uuid']);
    }
}
