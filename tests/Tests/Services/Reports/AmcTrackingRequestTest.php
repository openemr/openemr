<?php

/**
 * AmcTrackingRequestTest.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Reports;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AmcTrackingRequestTest extends TestCase
{
    // Provider ids no real user has; patient_data.providerID does not reference users.
    private const PROVIDER_A = 2000000301;
    private const PROVIDER_B = 2000000302;

    private const PUBPID_PREFIX = 'amc-tracking-test-';

    /**
     * pid of the fixture patient of each provider.
     *
     * @var array<int, int>
     */
    private array $pidByProvider = [];

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../../../../library/amc.php';
    }

    /**
     * Seeds one patient per provider, each with a pending "provide records to patient" request.
     */
    protected function setUp(): void
    {
        $this->deleteFixtures();
        foreach ([self::PROVIDER_A, self::PROVIDER_B] as $provider) {
            $pid = $this->createPatient($provider);
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO `amc_misc_data` (`amc_id`, `pid`, `date_created`) VALUES ('provide_rec_pat_amc', ?, '2026-01-10 09:00:00')",
                [$pid]
            );
            $this->pidByProvider[$provider] = $pid;
        }
    }

    /**
     * Removes the fixture patients and their requests.
     */
    protected function tearDown(): void
    {
        $this->deleteFixtures();
    }

    /**
     * With a provider picked in the report, only that provider's patients are listed.
     */
    #[Test]
    public function providerFilterListsOnlyThatProvidersPatients(): void
    {
        $this->assertSame([$this->pidByProvider[self::PROVIDER_A]], $this->fixturePidsInReport((string) self::PROVIDER_A));
        $this->assertSame([$this->pidByProvider[self::PROVIDER_B]], $this->fixturePidsInReport((string) self::PROVIDER_B));
    }

    /**
     * With no provider picked, the report covers the whole practice.
     */
    #[Test]
    public function noProviderListsEveryPatient(): void
    {
        $this->assertSame(
            [$this->pidByProvider[self::PROVIDER_A], $this->pidByProvider[self::PROVIDER_B]],
            $this->fixturePidsInReport('')
        );
    }

    /**
     * Runs the report for January 2026 and keeps the rows of the fixture patients, in pid order.
     *
     * @return list<int>
     */
    private function fixturePidsInReport(string $provider): array
    {
        $rows = amcTrackingRequest('provide_rec_pat_amc', '2026-01-01 00:00:00', '2026-01-31 23:59:59', $provider);
        $this->assertIsArray($rows);
        $fixturePids = array_values($this->pidByProvider);
        $pids = [];
        foreach ($rows as $row) {
            $this->assertIsArray($row);
            $pid = $row['pid'] ?? null;
            $this->assertIsNumeric($pid);
            if (in_array((int) $pid, $fixturePids, true)) {
                $pids[] = (int) $pid;
            }
        }
        sort($pids);
        return $pids;
    }

    /**
     * Inserts a patient whose primary provider is $provider and returns its pid.
     */
    private function createPatient(int $provider): int
    {
        $row = QueryUtils::querySingleRow("SELECT IFNULL(MAX(`pid`), 0) + 1 AS `next_pid` FROM `patient_data`");
        $this->assertIsArray($row);
        $this->assertIsNumeric($row['next_pid'] ?? null);
        $pid = (int) $row['next_pid'];
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `patient_data` (`pid`, `uuid`, `pubpid`, `fname`, `lname`, `DOB`, `providerID`) VALUES (?, ?, ?, 'Amc', 'Tracking', '1980-01-01', ?)",
            [$pid, (new UuidRegistry(['table_name' => 'patient_data']))->createUuid(), self::PUBPID_PREFIX . $provider, $provider]
        );
        return $pid;
    }

    /**
     * Deletes the fixture patients, their uuid registrations and their requests.
     */
    private function deleteFixtures(): void
    {
        $rows = QueryUtils::fetchRecords(
            "SELECT `pid`, `uuid` FROM `patient_data` WHERE `pubpid` LIKE ?",
            [self::PUBPID_PREFIX . '%']
        );
        foreach ($rows as $row) {
            QueryUtils::sqlStatementThrowException("DELETE FROM `amc_misc_data` WHERE `pid` = ?", [$row['pid']]);
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM `uuid_registry` WHERE `table_name` = 'patient_data' AND `uuid` = ?",
                [$row['uuid']]
            );
            QueryUtils::sqlStatementThrowException("DELETE FROM `patient_data` WHERE `pid` = ?", [$row['pid']]);
        }
        $this->pidByProvider = [];
    }
}
