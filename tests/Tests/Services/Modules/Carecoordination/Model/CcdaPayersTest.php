<?php

/**
 * The C-CDA payers section (EncounterccdadispatchTable::getPayers()): the
 * primary in force on the document's date, today, with its company's
 * address when it has one and its company's work phone.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Modules\Carecoordination\Model;

use Carecoordination\Model\EncounterccdadispatchTable;
use DateTimeImmutable;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CcdaPayersTest extends TestCase
{
    private const PID = 99995301;
    private const CURRENT_PLAN = 99995311;
    private const NEXT_PLAN = 99995312;
    private const NO_ADDRESS_PLAN = 99995313;
    private const NAMES = [
        self::CURRENT_PLAN => 'TEST-FIXTURE CURRENT MUTUAL',
        self::NEXT_PLAN => 'TEST-FIXTURE NEXT MUTUAL',
        self::NO_ADDRESS_PLAN => 'TEST-FIXTURE NO ADDRESS MUTUAL',
    ];

    protected function setUp(): void
    {
        $this->cleanUp();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (uuid, pid, pubpid, fname, lname, DOB, sex, date) VALUES (?, ?, ?, 'JANE', 'TESTPATIENT', '1980-01-01', 'Female', NOW())",
            [(new UuidRegistry(['table_name' => 'patient_data']))->createUuid(), self::PID, (string) self::PID]
        );
        foreach (self::NAMES as $id => $name) {
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO insurance_companies (id, uuid, name) VALUES (?, ?, ?)',
                [$id, (new UuidRegistry(['table_name' => 'insurance_companies']))->createUuid(), $name]
            );
            if ($id !== self::NO_ADDRESS_PLAN) {
                QueryUtils::sqlStatementThrowException(
                    "INSERT INTO addresses (id, line1, city, state, zip, country, foreign_id) VALUES (?, '1 Test Way', 'Testville', 'CA', '90001', 'USA', ?)",
                    [$id, $id]
                );
            }
        }
        // The current plan's work phone (phone_numbers.type 2), as the insurance company screen saves it.
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO phone_numbers (id, country_code, area_code, prefix, number, type, foreign_id) VALUES (?, '+1', '555', '555', '0199', 2, ?)",
            [self::CURRENT_PLAN, self::CURRENT_PLAN]
        );
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    /**
     * Next year's card, entered in advance, isn't in force today: the CCD
     * names the plan that is, with its company's phone.
     */
    #[Test]
    public function thePayerIsThePrimaryInForceTodayWithItsCompanysPhone(): void
    {
        $this->addPolicy(self::CURRENT_PLAN, -1000, null);
        $this->addPolicy(self::NEXT_PLAN, 60, null);

        $xml = (new EncounterccdadispatchTable())->getPayers(self::PID);

        self::assertStringContainsString('<name>' . self::NAMES[self::CURRENT_PLAN] . '</name>', $xml);
        self::assertStringNotContainsString(self::NAMES[self::NEXT_PLAN], $xml);
        self::assertStringContainsString('<number>(555)555-0199</number>', $xml);
    }

    /**
     * A blank work phone (older versions saved one with every company) isn't sent: the section keeps
     * its placeholder.
     */
    #[Test]
    public function aBlankWorkPhoneIsNotSent(): void
    {
        $this->addPolicy(self::NEXT_PLAN, -1000, null);
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO phone_numbers (id, country_code, area_code, prefix, number, type, foreign_id) VALUES (?, '+1', '', '', '', 2, ?)",
            [self::NEXT_PLAN, self::NEXT_PLAN]
        );

        $xml = (new EncounterccdadispatchTable())->getPayers(self::PID);

        self::assertStringContainsString('<name>' . self::NAMES[self::NEXT_PLAN] . '</name>', $xml);
        self::assertStringContainsString('<number>(000)000-0000</number>', $xml);
        self::assertStringNotContainsString('<number>()-</number>', $xml);
    }

    /**
     * A company with no address row is still named.
     */
    #[Test]
    public function aCompanyWithoutAnAddressIsStillThePayer(): void
    {
        $this->addPolicy(self::CURRENT_PLAN, -1000, -500);
        $this->addPolicy(self::NO_ADDRESS_PLAN, -499, null);

        $xml = (new EncounterccdadispatchTable())->getPayers(self::PID);

        self::assertStringContainsString('<name>' . self::NAMES[self::NO_ADDRESS_PLAN] . '</name>', $xml);
        self::assertStringNotContainsString(self::NAMES[self::CURRENT_PLAN], $xml);
    }

    /**
     * A primary that ended is no payer today: the section is empty.
     */
    #[Test]
    public function anEndedPrimaryIsNoPayer(): void
    {
        $this->addPolicy(self::CURRENT_PLAN, -1000, -100);

        self::assertSame('<payers></payers>', (new EncounterccdadispatchTable())->getPayers(self::PID));
    }

    /**
     * Dates are days from today, so the policy in force doesn't depend on the day the test runs.
     */
    private function addPolicy(int $company, ?int $startDays, ?int $endDays): void
    {
        $day = static fn(?int $days): ?string => $days === null ? null : (new DateTimeImmutable())->modify("$days days")->format('Y-m-d');
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO insurance_data (uuid, pid, type, provider, policy_number, date, date_end, plan_name) VALUES (?, ?, 'primary', ?, 'POLICY-1', ?, ?, 'test-fixture')",
            [(new UuidRegistry(['table_name' => 'insurance_data']))->createUuid(), self::PID, (string) $company, $day($startDays), $day($endDays)]
        );
    }

    private function cleanUp(): void
    {
        foreach (['insurance_data', 'patient_data'] as $table) {
            foreach (QueryUtils::fetchTableColumn("SELECT uuid FROM `$table` WHERE pid = ? AND uuid IS NOT NULL", 'uuid', [self::PID]) as $uuid) {
                QueryUtils::sqlStatementThrowException('DELETE FROM uuid_registry WHERE table_name = ? AND uuid = ?', [$table, $uuid]);
            }
        }
        $companies = array_keys(self::NAMES);
        $in = implode(',', array_fill(0, count($companies), '?'));
        foreach (QueryUtils::fetchTableColumn("SELECT uuid FROM insurance_companies WHERE id IN ($in) AND uuid IS NOT NULL", 'uuid', $companies) as $uuid) {
            QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE table_name = 'insurance_companies' AND uuid = ?", [$uuid]);
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM insurance_data WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException("DELETE FROM insurance_companies WHERE id IN ($in)", $companies);
        QueryUtils::sqlStatementThrowException("DELETE FROM addresses WHERE foreign_id IN ($in)", $companies);
        QueryUtils::sqlStatementThrowException("DELETE FROM phone_numbers WHERE foreign_id IN ($in)", $companies);
    }
}
