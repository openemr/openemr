<?php

/**
 * Duplicate detection of PatientNameHistoryService::createPatientNameHistory().
 *
 * Regression test for issue #12857. An empty end date is stored as NULL, but the
 * duplicate lookup compared it with `= ''`, which never matches NULL: the same
 * previous name without an end date could be saved again and again.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code <noreply@anthropic.com>
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\PatientNameHistoryService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PatientNameHistoryServiceTest extends TestCase
{
    private const PID = '900000202';

    private PatientNameHistoryService $service;

    protected function setUp(): void
    {
        $this->service = new PatientNameHistoryService();
        $this->cleanUp();
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    /**
     * The same name without an end date is saved once.
     */
    #[Test]
    public function sameNameWithoutEndDateIsADuplicate(): void
    {
        $first = $this->service->createPatientNameHistory(self::PID, self::record(''));
        $second = $this->service->createPatientNameHistory(self::PID, self::record(''));

        self::assertIsInt($first);
        self::assertFalse($second);
        self::assertSame(1, $this->storedRows());
    }

    /**
     * The same name with the same end date is saved once.
     */
    #[Test]
    public function sameNameWithSameEndDateIsADuplicate(): void
    {
        $first = $this->service->createPatientNameHistory(self::PID, self::record('2020-05-01'));
        $second = $this->service->createPatientNameHistory(self::PID, self::record('2020-05-01'));

        self::assertIsInt($first);
        self::assertFalse($second);
        self::assertSame(1, $this->storedRows());
    }

    /**
     * An end date on one entry and none on the other makes two different entries.
     */
    #[Test]
    public function sameNameWithAndWithoutEndDateAreDifferent(): void
    {
        $first = $this->service->createPatientNameHistory(self::PID, self::record(''));
        $second = $this->service->createPatientNameHistory(self::PID, self::record('2020-05-01'));

        self::assertIsInt($first);
        self::assertIsInt($second);
        self::assertSame(2, $this->storedRows());
    }

    /**
     * @return array<string, string>
     */
    private static function record(string $endDate): array
    {
        return [
            'previous_name_prefix' => '',
            'previous_name_first' => 'Maria',
            'previous_name_middle' => '',
            'previous_name_last' => 'Rossi',
            'previous_name_suffix' => '',
            'previous_name_enddate' => $endDate,
        ];
    }

    private function storedRows(): int
    {
        $count = QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS n FROM patient_history WHERE pid = ? AND history_type_key = 'name_history'",
            'n',
            [self::PID]
        );
        self::assertIsNumeric($count);
        return (int) $count;
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE ur FROM uuid_registry ur JOIN patient_history ph ON ph.uuid = ur.uuid WHERE ph.pid = ?",
            [self::PID]
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM patient_history WHERE pid = ?', [self::PID]);
    }
}
