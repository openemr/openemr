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
}
