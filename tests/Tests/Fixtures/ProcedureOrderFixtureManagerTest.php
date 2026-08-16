<?php

/**
 * Database-backed tests for the procedure order and procedure provider fixture managers.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

/**
 * Exercises ProcedureOrderFixtureManager and ProcedureProviderFixtureManager against a live database.
 *
 * These managers install rows across five tables (patient_data, form_encounter,
 * procedure_providers, procedure_order, procedure_order_code, forms) and are only
 * meaningful when those writes actually happen, so every test here talks to the
 * database rather than a double.
 */
class ProcedureOrderFixtureManagerTest extends TestCase
{
    /** LIKE pattern matching every fixture row this suite installs. */
    private const FIXTURE_LIKE = 'test-fixture-%';

    /** Number of orders declared in procedure-orders.php. */
    private const EXPECTED_ORDER_COUNT = 1;

    /** Number of codes declared in procedure-order-codes.php, installed once per order. */
    private const EXPECTED_CODES_PER_ORDER = 2;

    /** Number of labs declared in procedure-providers.php. */
    private const EXPECTED_PROVIDER_COUNT = 4;

    /** Number of practitioners declared in practitioners.php. */
    private const EXPECTED_PRACTITIONER_COUNT = 5;

    /** NPI carried by every practitioner in practitioners.php. */
    private const EXPECTED_PRACTITIONER_NPI = '0123456789';

    /** The built-in administrator: has no NPI, and is what a broken practitioner lookup used to select. */
    private const ADMIN_USER_ID = 1;

    /** Names declared in procedure-providers.php. */
    private const EXPECTED_PROVIDER_NAMES = [
        'test-fixture-Generic Lab',
        'test-fixture-Ammon Lab',
        'test-fixture-LabCorp Laboratory',
        'test-fixture-Quest Diagnostics',
    ];

    private ProcedureProviderFixtureManager $providerFixtureManager;

    private ProcedureOrderFixtureManager $fixtureManager;

    private PractitionerFixtureManager $practitionerFixtureManager;

    protected function setUp(): void
    {
        $this->providerFixtureManager = new ProcedureProviderFixtureManager();
        $this->fixtureManager = new ProcedureOrderFixtureManager(null, null, $this->providerFixtureManager);
        $this->practitionerFixtureManager = new PractitionerFixtureManager();
        // Any fixture rows left behind by an earlier failure would corrupt the
        // counts asserted below, so start every test from a known-clean table set.
        $this->fixtureManager->removeFixtures();
        // The manager only removes practitioners it installed itself, so clear any
        // strays here rather than relying on that ownership check.
        $this->practitionerFixtureManager->removePractitionerFixtures();
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeFixtures();
        $this->practitionerFixtureManager->removePractitionerFixtures();
    }

    public function testInstallFixturesCreatesOrdersCodesFormsAndProviders(): void
    {
        $installCount = $this->fixtureManager->installFixtures();

        self::assertSame(self::EXPECTED_ORDER_COUNT, $installCount);

        $orderIds = $this->fixtureManager->getInstalledOrderIds();
        self::assertCount(self::EXPECTED_ORDER_COUNT, $orderIds);

        self::assertSame(self::EXPECTED_ORDER_COUNT, $this->countOrders());
        self::assertSame(
            self::EXPECTED_ORDER_COUNT * self::EXPECTED_CODES_PER_ORDER,
            $this->countCodesForOrders($orderIds)
        );
        self::assertSame(self::EXPECTED_ORDER_COUNT, $this->countFormsForOrders($orderIds));
        self::assertSame(self::EXPECTED_PROVIDER_COUNT, $this->countProviders());
    }

    public function testGetInstalledOrderIdsMatchesTheRowsActuallyInserted(): void
    {
        $this->fixtureManager->installFixtures();

        $installedIds = $this->fixtureManager->getInstalledOrderIds();
        $databaseIds = array_map(
            self::toInt(...),
            QueryUtils::fetchTableColumn(
                <<<'SQL'
                SELECT procedure_order_id FROM procedure_order
                WHERE control_id LIKE ?
                ORDER BY procedure_order_id
                SQL,
                'procedure_order_id',
                [self::FIXTURE_LIKE]
            )
        );

        self::assertSame($databaseIds, $installedIds);
        foreach ($installedIds as $installedId) {
            self::assertGreaterThan(0, $installedId);
        }
    }

    public function testInstalledOrderPointsAtRealPatientEncounterProviderAndLab(): void
    {
        $this->fixtureManager->installFixtures();

        $orderId = $this->firstInstalledOrderId();
        $order = $this->fetchRow(
            <<<'SQL'
            SELECT patient_id, encounter_id, provider_id, lab_id, date_ordered
            FROM procedure_order WHERE procedure_order_id = ?
            SQL,
            [$orderId]
        );

        self::assertSame(
            1,
            $this->countRows('SELECT COUNT(*) AS cnt FROM patient_data WHERE pid = ?', [self::toInt($order['patient_id'])]),
            'procedure_order.patient_id does not reference a patient_data row'
        );
        self::assertSame(
            1,
            $this->countRows(
                'SELECT COUNT(*) AS cnt FROM form_encounter WHERE encounter = ? AND reason LIKE ?',
                [self::toInt($order['encounter_id']), self::FIXTURE_LIKE]
            ),
            'procedure_order.encounter_id does not reference a fixture encounter'
        );
        self::assertSame(
            1,
            $this->countRows('SELECT COUNT(*) AS cnt FROM users WHERE id = ?', [self::toInt($order['provider_id'])]),
            'procedure_order.provider_id does not reference a users row'
        );

        $labId = self::toInt($order['lab_id']);
        self::assertSame(
            1,
            $this->countRows('SELECT COUNT(*) AS cnt FROM procedure_providers WHERE ppid = ?', [$labId]),
            'procedure_order.lab_id does not reference a procedure_providers row'
        );
        $providers = $this->providerFixtureManager->getInstalledProviders();
        self::assertSame($labId, self::toInt($providers[0]['ppid'] ?? null));

        // The forms row carries the order's own date_ordered; form_encounter.date is
        // NULL for fixture encounters and cannot supply one.
        $formDate = QueryUtils::fetchSingleValue(
            <<<'SQL'
            SELECT date FROM forms WHERE formdir = ? AND form_id = ?
            SQL,
            'date',
            ['procedure_order', $orderId]
        );
        self::assertSame($order['date_ordered'], $formDate);
    }

    public function testOrderingProviderIsAFixturePractitionerCarryingAnNpi(): void
    {
        $this->fixtureManager->installFixtures();

        self::assertSame(
            self::EXPECTED_PRACTITIONER_COUNT,
            $this->countPractitioners(),
            'installFixtures() did not install the practitioner fixtures'
        );

        $provider = $this->fetchRow(
            <<<'SQL'
            SELECT u.id, u.fname, u.npi
            FROM procedure_order po
            JOIN users u ON u.id = po.provider_id
            WHERE po.procedure_order_id = ?
            SQL,
            [$this->firstInstalledOrderId()]
        );

        self::assertNotSame(
            self::ADMIN_USER_ID,
            self::toInt($provider['id']),
            'the ordering provider fell back to the admin user instead of a fixture practitioner'
        );
        $fname = $provider['fname'];
        self::assertIsString($fname);
        self::assertStringStartsWith(PractitionerFixtureManager::FIXTURE_PREFIX, $fname);
        // A populated NPI is the whole point: HL7 order generation reads it, and a
        // null there is what produced the str_replace() deprecations.
        self::assertSame(self::EXPECTED_PRACTITIONER_NPI, $provider['npi']);
    }

    public function testInstallReusesPractitionersTheCallerAlreadyInstalled(): void
    {
        $this->practitionerFixtureManager->installPractitionerFixtures();

        $this->fixtureManager->installFixtures();

        self::assertSame(
            self::EXPECTED_PRACTITIONER_COUNT,
            $this->countPractitioners(),
            'installFixtures() installed a second copy of the practitioner fixtures'
        );

        // Practitioners this manager did not install are not its to remove.
        $this->fixtureManager->removeFixtures();
        self::assertSame(self::EXPECTED_PRACTITIONER_COUNT, $this->countPractitioners());
    }

    public function testRemoveFixturesTearsDownThePractitionersItInstalled(): void
    {
        $this->fixtureManager->installFixtures();
        self::assertSame(self::EXPECTED_PRACTITIONER_COUNT, $this->countPractitioners());

        $this->fixtureManager->removeFixtures();

        self::assertSame(0, $this->countPractitioners(), 'practitioner rows survived removeFixtures()');
    }

    public function testProviderManagerExposesEveryInstalledLab(): void
    {
        $installCount = $this->providerFixtureManager->installFixtures();

        self::assertSame(self::EXPECTED_PROVIDER_COUNT, $installCount);

        $providers = $this->providerFixtureManager->getInstalledProviders();
        self::assertCount(self::EXPECTED_PROVIDER_COUNT, $providers);

        $names = [];
        foreach ($providers as $provider) {
            $names[] = $provider['name'] ?? null;
            $ppid = self::toInt($provider['ppid'] ?? null);
            self::assertGreaterThan(0, $ppid);
            self::assertSame(
                1,
                $this->countRows('SELECT COUNT(*) AS cnt FROM procedure_providers WHERE ppid = ?', [$ppid])
            );
        }
        self::assertSame(self::EXPECTED_PROVIDER_NAMES, $names);
    }

    public function testGetProviderByNameMatchesCaseInsensitiveSubstrings(): void
    {
        $this->providerFixtureManager->installFixtures();

        $quest = $this->providerFixtureManager->getProviderByName('quest');
        self::assertNotNull($quest);
        self::assertSame('test-fixture-Quest Diagnostics', $quest['name'] ?? null);

        $labCorp = $this->providerFixtureManager->getProviderByName('LabCorp');
        self::assertNotNull($labCorp);
        self::assertSame('test-fixture-LabCorp Laboratory', $labCorp['name'] ?? null);

        self::assertNull($this->providerFixtureManager->getProviderByName('no-such-laboratory'));
    }

    public function testRemoveFixturesLeavesEveryTouchedTableClean(): void
    {
        $this->fixtureManager->installFixtures();
        $orderIds = $this->fixtureManager->getInstalledOrderIds();
        self::assertNotSame([], $orderIds);

        $this->fixtureManager->removeFixtures();

        self::assertSame(0, $this->countOrders(), 'procedure_order rows survived removeFixtures()');
        self::assertSame(0, $this->countCodesForOrders($orderIds), 'procedure_order_code rows were orphaned');
        self::assertSame(0, $this->countFormsForOrders($orderIds), 'forms rows were orphaned');
        self::assertSame(0, $this->countProviders(), 'procedure_providers rows survived removeFixtures()');
        self::assertSame(
            0,
            $this->countRows('SELECT COUNT(*) AS cnt FROM form_encounter WHERE reason LIKE ?', [self::FIXTURE_LIKE]),
            'form_encounter rows survived removeFixtures()'
        );
        self::assertSame(0, $this->countPatients(), 'patient_data rows survived removeFixtures()');

        self::assertSame([], $this->fixtureManager->getInstalledOrderIds());
        self::assertSame([], $this->providerFixtureManager->getInstalledProviders());
    }

    public function testInstallRemoveInstallDoesNotAccumulateOrCollide(): void
    {
        $this->fixtureManager->installFixtures();
        $firstIds = $this->fixtureManager->getInstalledOrderIds();
        $firstPatientCount = $this->countPatients();
        self::assertGreaterThan(0, $firstPatientCount);
        $this->fixtureManager->removeFixtures();

        $secondCount = $this->fixtureManager->installFixtures();
        $secondIds = $this->fixtureManager->getInstalledOrderIds();

        self::assertSame(self::EXPECTED_ORDER_COUNT, $secondCount);
        self::assertCount(self::EXPECTED_ORDER_COUNT, $secondIds);
        self::assertSame([], array_intersect($firstIds, $secondIds), 'the second install reused order ids');

        // A duplicated patient row is what breaks form_encounter's pubpid subquery,
        // so assert the dependent fixtures are singular rather than doubled.
        self::assertSame(self::EXPECTED_ORDER_COUNT, $this->countOrders());
        self::assertSame(
            self::EXPECTED_ORDER_COUNT * self::EXPECTED_CODES_PER_ORDER,
            $this->countCodesForOrders($secondIds)
        );
        self::assertSame(self::EXPECTED_ORDER_COUNT, $this->countFormsForOrders($secondIds));
        self::assertSame(self::EXPECTED_PROVIDER_COUNT, $this->countProviders());
        self::assertSame(
            $firstPatientCount,
            $this->countPatients(),
            'the second install duplicated the patient fixtures'
        );
    }

    public function testDefaultConstructorInstallsItsOwnDependencies(): void
    {
        $manager = new ProcedureOrderFixtureManager();

        $installCount = $manager->installFixtures();

        self::assertSame(self::EXPECTED_ORDER_COUNT, $installCount);
        self::assertSame(self::EXPECTED_ORDER_COUNT, $this->countOrders());
        self::assertSame(self::EXPECTED_PROVIDER_COUNT, $this->countProviders());
        self::assertSame(
            self::EXPECTED_ORDER_COUNT,
            $this->countRows('SELECT COUNT(*) AS cnt FROM form_encounter WHERE reason LIKE ?', [self::FIXTURE_LIKE])
        );

        $manager->removeFixtures();

        self::assertSame(0, $this->countOrders());
        self::assertSame(0, $this->countProviders());
    }

    public function testInstallFixturesRequiresAPatientFixture(): void
    {
        $encounterManager = new class extends EncounterFixtureManager {
            public function installFixtures(): int
            {
                return 0;
            }

            protected function removeInstalledFixtures(): void
            {
            }
        };
        $manager = new ProcedureOrderFixtureManager(null, $encounterManager, new ProcedureProviderFixtureManager());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Procedure order fixtures require a patient fixture');

        $manager->installFixtures();
    }

    public function testInstallFixturesRequiresAnEncounterFixture(): void
    {
        $encounterManager = new class (new FixtureManager()) extends EncounterFixtureManager {
            public function __construct(private readonly FixtureManager $patientFixtureManager)
            {
                parent::__construct();
            }

            public function installFixtures(): int
            {
                $this->patientFixtureManager->installPatientFixtures();
                return 0;
            }

            protected function removeInstalledFixtures(): void
            {
                $this->patientFixtureManager->removePatientFixtures();
            }
        };
        $manager = new ProcedureOrderFixtureManager(null, $encounterManager, new ProcedureProviderFixtureManager());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Procedure order fixtures require an encounter fixture');

        $manager->installFixtures();
    }

    private function countOrders(): int
    {
        return $this->countRows(
            'SELECT COUNT(*) AS cnt FROM procedure_order WHERE control_id LIKE ?',
            [self::FIXTURE_LIKE]
        );
    }

    private function firstInstalledOrderId(): int
    {
        $orderIds = $this->fixtureManager->getInstalledOrderIds();
        self::assertArrayHasKey(0, $orderIds);

        return $orderIds[0];
    }

    /**
     * Count practitioner fixtures by `fname`, the column that actually carries the
     * prefix. PractitionerFixtureManager leaves `username` plain (kperez, lcohen,
     * ...), so counting by username always returns zero.
     */
    private function countPractitioners(): int
    {
        return $this->countRows(
            'SELECT COUNT(*) AS cnt FROM users WHERE fname LIKE ?',
            [self::FIXTURE_LIKE]
        );
    }

    private function countPatients(): int
    {
        return $this->countRows(
            'SELECT COUNT(*) AS cnt FROM patient_data WHERE pubpid LIKE ?',
            [self::FIXTURE_LIKE]
        );
    }

    private function countProviders(): int
    {
        return $this->countRows(
            'SELECT COUNT(*) AS cnt FROM procedure_providers WHERE name LIKE ?',
            [self::FIXTURE_LIKE]
        );
    }

    /**
     * Count procedure_order_code rows attached to the given orders.
     *
     * Counting by order id rather than by joining procedure_order matters after a
     * removal: a join would report zero simply because the parent orders are gone,
     * hiding orphaned code rows.
     *
     * @param list<int> $orderIds
     */
    private function countCodesForOrders(array $orderIds): int
    {
        return $this->countRows(
            'SELECT COUNT(*) AS cnt FROM procedure_order_code WHERE procedure_order_id IN ('
                . self::placeholders($orderIds) . ')',
            $orderIds
        );
    }

    /**
     * Count forms rows attached to the given orders, orphans included.
     *
     * @param list<int> $orderIds
     */
    private function countFormsForOrders(array $orderIds): int
    {
        return $this->countRows(
            'SELECT COUNT(*) AS cnt FROM forms WHERE formdir = ? AND form_id IN ('
                . self::placeholders($orderIds) . ')',
            array_merge(['procedure_order'], $orderIds)
        );
    }

    /**
     * @param list<int> $values
     */
    private static function placeholders(array $values): string
    {
        return implode(', ', array_fill(0, count($values), '?'));
    }

    /**
     * @param list<mixed> $binds
     */
    private function countRows(string $sql, array $binds, string $message = ''): int
    {
        return self::toInt(QueryUtils::fetchSingleValue($sql, 'cnt', $binds), $message);
    }

    /**
     * @param list<mixed> $binds
     * @return array<mixed>
     */
    private function fetchRow(string $sql, array $binds): array
    {
        $records = QueryUtils::fetchRecords($sql, $binds);
        self::assertArrayHasKey(0, $records);

        return $records[0];
    }

    /**
     * Narrow a value read back from ADODB, which returns numeric columns as strings.
     */
    private static function toInt(mixed $value, string $message = ''): int
    {
        if (!is_numeric($value)) {
            // @codeCoverageIgnoreStart
            // Defensive — every call site reads a NOT NULL numeric column, so a
            // non-numeric value means the query itself changed shape, which is not
            // a path normal CI exercises.
            self::fail($message !== '' ? $message : 'Expected a numeric value from the database');
            // @codeCoverageIgnoreEnd
        }

        return (int) $value;
    }
}
