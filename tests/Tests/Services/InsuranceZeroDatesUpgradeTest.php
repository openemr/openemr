<?php

/**
 * The upgrade step that clears policies' 0000-00-00 ends (sql/8_5_0-to-8_6_0_upgrade.sql): a stored
 * 0000-00-00 end becomes NULL. The claim's own query read a 0000-00-00 end as ended where the coverage
 * rule (#15038) reads it as open; the step ran before the claim moved to the rule, and the claim now
 * reads the rule, so the two agree before and after it. A 0000-00-00 start, which both read as always,
 * is left as it is.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use DateTimeImmutable;
use OpenEMR\Billing\Claim;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\InsuranceService;
use OpenEMR\Services\Utils\SQLUpgradeService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InsuranceZeroDatesUpgradeTest extends TestCase
{
    private const PID = 99995601;
    private const OTHER_PID = 99995602;
    private const DAY = '2026-06-15';
    private const GUARDS = ['#IfRow insurance_data date_end 0000-00-00'];

    protected function setUp(): void
    {
        $this->cleanUp();
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    /**
     * Only a 0000-00-00 end changes: a 0000-00-00 start, real dates, partial dates (2026-05-00, which the
     * rule compares as stored, as SQL does) and NULLs stay as they are.
     */
    #[Test]
    public function onlyAZeroEndBecomesNull(): void
    {
        $zeroEnd = $this->addPolicy(self::PID, 'primary', '101', '2020-01-01', '0000-00-00');
        $zeroStart = $this->addPolicy(self::PID, 'secondary', '102', '0000-00-00', '2027-12-31');
        $partial = $this->addPolicy(self::PID, 'tertiary', '103', '2026-05-00', '2026-08-00');
        $dated = $this->addPolicy(self::OTHER_PID, 'primary', '104', '2021-02-03', '2026-09-30');
        $undated = $this->addPolicy(self::OTHER_PID, 'secondary', '105', null, null);

        $this->runUpgradeStep();

        self::assertSame(['2020-01-01', null], $this->dates($zeroEnd));
        self::assertSame(['0000-00-00', '2027-12-31'], $this->dates($zeroStart));
        self::assertSame(['2026-05-00', '2026-08-00'], $this->dates($partial));
        self::assertSame(['2021-02-03', '2026-09-30'], $this->dates($dated));
        self::assertSame([null, null], $this->dates($undated));
    }

    /**
     * A 0000-00-00 end: the claim and the coverage rule have the policy in force, before the step and
     * after it.
     */
    #[Test]
    public function theClaimAndTheRuleAgreeOnAZeroEnd(): void
    {
        $this->addPolicy(self::PID, 'primary', '101', '2020-01-01', '0000-00-00');

        self::assertSame([101], $this->claimCompanies(), 'the claim, before');
        self::assertSame([101], $this->ruleCompanies(), 'the rule, before');

        $this->runUpgradeStep();

        self::assertSame([101], $this->claimCompanies(), 'the claim, after');
        self::assertSame([101], $this->ruleCompanies(), 'the rule, after');
    }

    /**
     * Run again, the step finds no zero date and changes nothing.
     */
    #[Test]
    public function theStepIsSafeToRerun(): void
    {
        $this->addPolicy(self::PID, 'primary', '101', '0000-00-00', '0000-00-00');
        $this->addPolicy(self::PID, 'secondary', '102', '2020-01-01', '0000-00-00');
        $this->runUpgradeStep();
        $after = QueryUtils::fetchRecords('SELECT * FROM insurance_data WHERE pid IN (?, ?) ORDER BY id', [self::PID, self::OTHER_PID]);

        $this->runUpgradeStep();

        self::assertSame($after, QueryUtils::fetchRecords('SELECT * FROM insurance_data WHERE pid IN (?, ?) ORDER BY id', [self::PID, self::OTHER_PID]));
    }

    /**
     * @return array{?string, ?string} the policy's start and end as stored
     */
    private function dates(int $id): array
    {
        $row = QueryUtils::querySingleRow('SELECT CAST(`date` AS CHAR) AS d, CAST(`date_end` AS CHAR) AS e FROM insurance_data WHERE id = ?', [$id]);
        self::assertIsArray($row);
        $d = $row['d'] ?? null;
        $e = $row['e'] ?? null;
        return [is_string($d) ? $d : null, is_string($e) ? $e : null];
    }

    /**
     * The companies the claim bills for a visit on DAY (Claim::loadPayerInfo()), the claim
     * built without its constructor: only the payer choice is under test.
     *
     * @return list<int>
     */
    private function claimCompanies(): array
    {
        $claim = new class extends Claim {
            /**
             * No encounter, billing rows, facility or users to load: only loadPayerInfo() is under test.
             */
            public function __construct()
            {
            }
        };
        $claim->pid = self::PID;
        $claim->encounter = ['date' => self::DAY . ' 00:00:00'];
        $billrow = ['payer_id' => 0, 'process_date' => null];
        $claim->loadPayerInfo($billrow);
        $companies = [];
        foreach (is_array($claim->payers) ? $claim->payers : [] as $payer) {
            $provider = is_array($payer) && is_array($payer['data'] ?? null) ? ($payer['data']['provider'] ?? null) : null;
            if (is_numeric($provider)) {
                $companies[] = (int) $provider;
            }
        }
        return $companies;
    }

    /**
     * @return list<int> the companies of the policies the coverage rule has in force on DAY
     */
    private function ruleCompanies(): array
    {
        return array_map(
            static fn($row): int => $row->policy->companyId,
            (new InsuranceService())->getEffectiveInsuranceRows(self::PID, new DateTimeImmutable(self::DAY))
        );
    }

    /**
     * Runs the step's block of this release's upgrade file, as written there, through SQLUpgradeService.
     */
    private function runUpgradeStep(): void
    {
        $lines = file(dirname(__DIR__, 3) . '/sql/8_5_0-to-8_6_0_upgrade.sql', FILE_IGNORE_NEW_LINES);
        self::assertIsArray($lines);
        $block = [];
        foreach (self::GUARDS as $guard) {
            $start = array_search($guard, $lines, true);
            self::assertIsInt($start, "the block '$guard' is in the upgrade file");
            $end = array_search('#EndIf', array_slice($lines, $start, null, true), true);
            self::assertIsInt($end);
            $block = array_merge($block, array_slice($lines, $start, $end - $start + 1));
        }
        $dir = sys_get_temp_dir() . '/openemr-upgrade-block-' . bin2hex(random_bytes(4));
        self::assertTrue(mkdir($dir));
        try {
            file_put_contents("$dir/block.sql", implode("\n", $block) . "\n");
            $upgrade = new SQLUpgradeService();
            $upgrade->setRenderOutputToScreen(false);
            $upgrade->setThrowExceptionOnError(true);
            $upgrade->upgradeFromSqlFile('block.sql', $dir);
        } finally {
            if (is_file("$dir/block.sql")) {
                unlink("$dir/block.sql");
            }
            rmdir($dir);
        }
    }

    /**
     * Stored as given: the connection's sql_mode '' keeps a 0000-00-00 date, as a site's does.
     *
     * @return int the policy's id
     */
    private function addPolicy(int $pid, string $type, string $provider, ?string $start, ?string $end): int
    {
        return QueryUtils::sqlInsert(
            "INSERT INTO insurance_data (uuid, pid, type, provider, date, date_end, plan_name) VALUES (?, ?, ?, ?, ?, ?, 'test-fixture')",
            [(new UuidRegistry(['table_name' => 'insurance_data']))->createUuid(), $pid, $type, $provider, $start, $end]
        );
    }

    private function cleanUp(): void
    {
        foreach (QueryUtils::fetchTableColumn('SELECT uuid FROM insurance_data WHERE pid IN (?, ?) AND uuid IS NOT NULL', 'uuid', [self::PID, self::OTHER_PID]) as $uuid) {
            QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE table_name = 'insurance_data' AND uuid = ?", [$uuid]);
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM insurance_data WHERE pid IN (?, ?)', [self::PID, self::OTHER_PID]);
    }
}
