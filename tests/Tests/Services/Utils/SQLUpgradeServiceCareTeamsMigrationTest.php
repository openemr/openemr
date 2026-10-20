<?php

/**
 * Transaction handling of SQLUpgradeService::migrateCareTeamsV1ToV2().
 *
 * Characterization tests for issue #10384: the migration either commits every care
 * team it creates or none of them, rethrows the failure when the service is told to,
 * and otherwise reports it and lets the upgrade continue.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code <noreply@anthropic.com>
 * @copyright Copyright (c) 2026 OpenEMR Contributors
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Utils;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Utils\SQLUpgradeService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SQLUpgradeServiceCareTeamsMigrationTest extends TestCase
{
    private const PID = 900000101;

    private const FAILURE = 'simulated failure after the care team insert';

    protected function setUp(): void
    {
        $this->cleanUp();
        $others = self::rowCount(
            "SELECT COUNT(*) AS n FROM patient_data WHERE (care_team_provider != '' AND care_team_provider IS NOT NULL)"
            . " OR (care_team_facility != '' AND care_team_facility IS NOT NULL)"
        );
        if ($others !== 0) {
            self::markTestSkipped('The migration reads every patient; this database already holds v1 care team data.');
        }
        $providerId = QueryUtils::fetchSingleValue("SELECT id FROM users WHERE username = 'admin'", 'id');
        self::assertIsNumeric($providerId);
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, fname, lname, DOB, care_team_provider, last_updated, created_by, updated_by)"
            . " VALUES (?, 'Care', 'Team', '1980-01-01', ?, NOW(), 1, 1)",
            [self::PID, (string) $providerId]
        );
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    /**
     * A successful migration commits the care team and its member.
     */
    #[Test]
    public function successfulMigrationCommitsTheCareTeam(): void
    {
        $service = self::service(failAfterInsert: false);

        self::migrate($service);

        self::assertSame(1, $this->careTeamCount());
        self::assertSame(1, $this->careTeamMemberCount());
    }

    /**
     * A failure after a care team was written rolls it back and rethrows when told to.
     */
    #[Test]
    public function failureRollsBackAndRethrows(): void
    {
        $service = self::service(failAfterInsert: true);
        $service->setThrowExceptionOnError(true);

        try {
            self::migrate($service);
            self::fail('The migration swallowed the failure although it was told to rethrow it');
        } catch (\RuntimeException $exception) {
            self::assertSame(self::FAILURE, $exception->getMessage());
        }

        self::assertSame(0, $this->careTeamCount());
        self::assertSame(0, $this->careTeamMemberCount());
    }

    /**
     * Without rethrow, a failure is rolled back, reported in the output, and the upgrade goes on.
     */
    #[Test]
    public function failureRollsBackAndReportsWithoutRethrow(): void
    {
        $service = self::service(failAfterInsert: true);
        $service->setThrowExceptionOnError(false);

        self::migrate($service);

        self::assertSame(0, $this->careTeamCount());
        self::assertSame(0, $this->careTeamMemberCount());
        $output = '';
        foreach ($service->getRenderOutputBuffer() as $line) {
            $output .= is_string($line) ? $line : '';
        }
        self::assertStringContainsString('Care Teams v1 to v2 migration failed', $output);
    }

    /**
     * The real service, silent, optionally failing right after it writes a care team.
     */
    private static function service(bool $failAfterInsert): SQLUpgradeService
    {
        $service = new class ($failAfterInsert, self::FAILURE) extends SQLUpgradeService {
            public function __construct(private readonly bool $failAfterInsert, private readonly string $failure)
            {
                parent::__construct();
            }

            /**
             * @param array<mixed> $careTeamRecord
             */
            protected function insertCareTeam(array $careTeamRecord): void
            {
                parent::insertCareTeam($careTeamRecord);
                if ($this->failAfterInsert) {
                    throw new \RuntimeException($this->failure);
                }
            }
        };
        $service->setRenderOutputToScreen(false);
        return $service;
    }

    /**
     * Run the protected migration the way the #IfCareTeamsV1MigrationNeeded directive does.
     */
    private static function migrate(SQLUpgradeService $service): void
    {
        (new \ReflectionMethod($service, 'migrateCareTeamsV1ToV2'))->invoke($service);
    }

    private function careTeamCount(): int
    {
        return self::rowCount('SELECT COUNT(*) AS n FROM care_teams WHERE pid = ?', [self::PID]);
    }

    private function careTeamMemberCount(): int
    {
        return self::rowCount(
            'SELECT COUNT(*) AS n FROM care_team_member ctm JOIN care_teams ct ON ct.id = ctm.care_team_id WHERE ct.pid = ?',
            [self::PID]
        );
    }

    /**
     * The value of a single "COUNT(*) AS n" query.
     *
     * @param list<int|string> $binds
     */
    private static function rowCount(string $sql, array $binds = []): int
    {
        $count = QueryUtils::fetchSingleValue($sql, 'n', $binds);
        self::assertIsNumeric($count);
        return (int) $count;
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE ctm FROM care_team_member ctm JOIN care_teams ct ON ct.id = ctm.care_team_id WHERE ct.pid = ?',
            [self::PID]
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE ur FROM uuid_registry ur JOIN care_teams ct ON ct.uuid = ur.uuid WHERE ct.pid = ? AND ur.table_name = 'care_teams'",
            [self::PID]
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM care_teams WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pid = ?', [self::PID]);
    }
}
