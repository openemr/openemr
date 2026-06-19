<?php

/**
 * Pharmacy Persist Integration Test
 *
 * Exercises the legacy library/classes/Pharmacy.class.php persist() path
 * against the phone_numbers table. Covers the same null-national-digits
 * regression as InsuranceCompanyServiceTest::testLegacyPersistSkipsPhone...,
 * since Pharmacy got the exact same buggy persist() code from PR #10326
 * (the PhoneNumberService consolidation refactor) as InsuranceCompany did.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PharmacyPersistTest extends TestCase
{
    /** @var list<int|string> */
    private array $createdIds = [];

    protected function tearDown(): void
    {
        foreach ($this->createdIds as $id) {
            QueryUtils::fetchRecordsNoLog("DELETE FROM `addresses` WHERE `foreign_id` = ?", [$id]);
            QueryUtils::fetchRecordsNoLog("DELETE FROM `phone_numbers` WHERE `foreign_id` = ?", [$id]);
            QueryUtils::fetchRecordsNoLog("DELETE FROM `pharmacies` WHERE `id` = ?", [$id]);
        }
    }

    #[Test]
    public function testLegacyPersistSkipsPhoneWithoutTenDigitNationalNumber(): void
    {
        // Regression for the same TypeError that fired on insurance company
        // edits prior to InsuranceCompany::persist()'s null guard. Pharmacy
        // got identical code from #10326 and crashes identically when a phone
        // PhoneNumber::tryParse accepts doesn't yield a 10-digit NANP national
        // number (e.g. "555-1234").
        $pharmacy = new \Pharmacy();
        $pharmacy->set_name('test-fixture-LegacyPersistShortPhone');
        $pharmacy->set_phone('555-1234');

        $pharmacy->persist();
        $pharmacyId = $pharmacy->id;
        $this->assertTrue(is_int($pharmacyId) || is_string($pharmacyId));
        $this->createdIds[] = $pharmacyId;

        $phoneRows = QueryUtils::fetchRecordsNoLog(
            "SELECT id FROM phone_numbers WHERE foreign_id = ?",
            [$pharmacyId]
        );
        $this->assertSame([], $phoneRows);
    }

    #[Test]
    public function testLegacyPersistDoesNotDuplicatePhoneRowsOnResave(): void
    {
        // Same regression as the matching InsuranceCompany test — Pharmacy
        // inherited the duplicate-on-save bug from PR #10326. Without the
        // clear-then-insert in persist() every save added another WORK + FAX
        // pair, and the list view's two LEFT JOINs on phone_numbers
        // multiplied the pharmacy across (work_count * fax_count) result rows.
        $pharmacy = new \Pharmacy();
        $pharmacy->set_name('test-fixture-LegacyPersistDuplicate');
        $pharmacy->set_phone('5551234567');
        $pharmacy->set_fax('5559876543');
        $pharmacy->persist();
        $pharmacyId = $pharmacy->id;
        $this->assertTrue(is_int($pharmacyId) || is_string($pharmacyId));
        $this->createdIds[] = $pharmacyId;

        $afterCreate = QueryUtils::fetchRecordsNoLog(
            "SELECT id, type FROM phone_numbers WHERE foreign_id = ?",
            [$pharmacyId]
        );
        $this->assertCount(2, $afterCreate);

        $pharmacy->persist();
        $afterResave = QueryUtils::fetchRecordsNoLog(
            "SELECT id, type FROM phone_numbers WHERE foreign_id = ?",
            [$pharmacyId]
        );
        $this->assertCount(2, $afterResave);

        $pharmacy->set_phone('5551112222');
        $pharmacy->persist();
        $afterEdit = QueryUtils::fetchRecordsNoLog(
            "SELECT area_code, prefix, number, type FROM phone_numbers"
            . " WHERE foreign_id = ? ORDER BY type",
            [$pharmacyId]
        );
        $this->assertCount(2, $afterEdit);
        $workRow = $afterEdit[0];
        $this->assertSame('555', $workRow['area_code']);
        $this->assertSame('111', $workRow['prefix']);
        $this->assertSame('2222', $workRow['number']);
    }
}
