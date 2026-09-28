<?php

/**
 * genX12837P() writes both facility ZIP warnings into the claim log.
 *
 * The isolated test only reads the constants. This one builds an encounter
 * so an old literal at either ZIP branch fails here.
 *
 * Fixture ids are allocated from the live tables and removed by those ids.
 * Nothing is deleted before this test has inserted it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\X125010837P;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

class X125010837PZipLogTest extends TestCase
{
    private int $pid = 0;

    private int $encounter = 0;

    private int $billingFacilityId = 0;

    private int $serviceFacilityId = 0;

    private int $partnerId = 0;

    private int $providerId = 0;

    /**
     * No rows are created here, and nothing is deleted.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Remove only the rows this test inserted.
     */
    protected function tearDown(): void
    {
        $this->removeCreatedRows();
        parent::tearDown();
    }

    /**
     * A short billing ZIP and a short service ZIP are both named in the log.
     */
    public function testShortFacilityZipsAreWrittenIntoTheClaimLog(): void
    {
        [$claimText, $log] = $this->generateClaim('10101', '20202');

        $this->assertStringContainsString(X125010837P::BILLING_ZIP_LOG, $log);
        $this->assertStringContainsString(X125010837P::SERVICE_ZIP_LOG, $log);
        $this->assertStringNotContainsString('Rejecting claim', $log);
        $this->assertStringContainsString('*10101~', $claimText);
        $this->assertStringContainsString('*20202~', $claimText);
    }

    /**
     * Nine-digit ZIPs are still written on the claim, with no ZIP warning.
     */
    public function testNineDigitFacilityZipsAreSentWithoutTheWarning(): void
    {
        [$claimText, $log] = $this->generateClaim('101010101', '202020202');

        $this->assertStringNotContainsString(X125010837P::BILLING_ZIP_LOG, $log);
        $this->assertStringNotContainsString(X125010837P::SERVICE_ZIP_LOG, $log);
        $this->assertStringContainsString('*101010101~', $claimText);
        $this->assertStringContainsString('*202020202~', $claimText);
    }

    /**
     * @return array{string, string}
     */
    private function generateClaim(string $billingZip, string $serviceZip): array
    {
        $suffix = bin2hex(random_bytes(4));
        $this->providerId = $this->insertId(
            'INSERT INTO users (username, fname, lname, npi, active) VALUES (?, ?, ?, ?, 1)',
            ['zip-log-user-' . $suffix, 'Zip', 'Logger', '1234567893']
        );
        $this->billingFacilityId = $this->insertFacility('zip-log-billing-' . $suffix, $billingZip);
        $this->serviceFacilityId = $this->insertFacility('zip-log-service-' . $suffix, $serviceZip);
        $this->insertPatientEncounterAndPartner();
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO billing (pid, encounter, code, code_type, code_text, units, fee,'
            . ' provider_id, payer_id, activity)'
            . ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)',
            [$this->pid, $this->encounter, '99213', 'CPT4', 'Office visit', 1, 100, $this->providerId, 0]
        );

        $log = '';
        $edicount = 0;
        $patSegmentCount = 0;
        $claimText = X125010837P::genX12837P(
            $this->pid,
            $this->encounter,
            $this->partnerId,
            $log,
            false,
            false,
            0,
            $edicount,
            $patSegmentCount
        );
        if (!is_string($log)) {
            $this->fail('Claim log was not a string');
        }

        return [$claimText, $log];
    }

    /**
     * Insert the patient, encounter, and X12 partner under one lock.
     * Each id is stored only after its insert succeeds.
     */
    private function insertPatientEncounterAndPartner(): void
    {
        $row = QueryUtils::querySingleRow('SELECT GET_LOCK(?, 5) AS locked', ['openemr_zip_log_fixture']);
        $locked = is_array($row) ? ($row['locked'] ?? null) : null;
        if ($locked !== 1 && $locked !== '1') {
            $this->fail('Could not reserve fixture ids');
        }

        try {
            $pid = $this->allocatePositiveId(
                'SELECT COALESCE(MAX(pid), 0) + 1 AS next_id FROM patient_data'
            );
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO patient_data (pid, fname, lname, mname, DOB, sex, status) VALUES (?, ?, ?, ?, ?, ?, ?)',
                [$pid, 'Ada', 'Lovelace', '', '1980-01-01', 'Female', 'single']
            );
            $this->pid = $pid;

            $encounter = $this->allocatePositiveId(
                'SELECT COALESCE(MAX(encounter), 0) + 1 AS next_id FROM form_encounter'
            );
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO form_encounter (pid, encounter, date, facility_id, billing_facility,'
                . ' provider_id, pos_code, reason)'
                . ' VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $pid,
                    $encounter,
                    '2026-09-25 10:00:00',
                    $this->serviceFacilityId,
                    $this->billingFacilityId,
                    $this->providerId,
                    11,
                    'zip-log-test',
                ]
            );
            $this->encounter = $encounter;

            $partnerId = $this->allocatePositiveId(
                'SELECT COALESCE(MAX(`id`), 0) + 1 AS next_id FROM x12_partners'
            );
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO x12_partners (id, name, x12_sender_id, x12_receiver_id, x12_gs02, x12_per06)'
                . ' VALUES (?, ?, ?, ?, ?, ?)',
                [$partnerId, 'zip-log-partner-' . $pid, 'SENDER', 'RECEIVER', 'ZIPLOG', '5555550100']
            );
            $this->partnerId = $partnerId;
        } finally {
            QueryUtils::sqlStatementThrowException('SELECT RELEASE_LOCK(?)', ['openemr_zip_log_fixture']);
        }
    }

    /**
     * Read the next positive id from a MAX()+1 query.
     */
    private function allocatePositiveId(string $sql): int
    {
        $row = QueryUtils::querySingleRow($sql);
        $nextId = is_array($row) ? ($row['next_id'] ?? null) : null;
        if (is_string($nextId) && ctype_digit($nextId)) {
            $nextId = (int) $nextId;
        }

        if (!is_int($nextId) || $nextId <= 0) {
            $this->fail('Could not allocate an id');
        }

        return $nextId;
    }

    /**
     * Insert one facility and return its id.
     */
    private function insertFacility(string $name, string $postalCode): int
    {
        return $this->insertId(
            'INSERT INTO facility (name, street, city, state, postal_code, country_code, federal_ein, facility_npi,'
            . ' tax_id_type, color, oid, pos_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$name, '1 Main', 'Testville', 'TX', $postalCode, 'US', '12-3456789', '1234567893', 'EI', '#000000', '', 11]
        );
    }

    /**
     * @param list<int|string> $parameters
     */
    private function insertId(string $sql, array $parameters): int
    {
        $id = QueryUtils::sqlInsert($sql, $parameters);
        if ($id <= 0) {
            $this->fail('Insert did not return an id');
        }

        return $id;
    }

    /**
     * Delete only the rows this process inserted. A shared database can already
     * contain the same names; those rows are not ours.
     */
    private function removeCreatedRows(): void
    {
        if ($this->pid > 0 && $this->encounter > 0) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM billing WHERE pid = ? AND encounter = ?',
                [$this->pid, $this->encounter]
            );
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM form_encounter WHERE pid = ? AND encounter = ?',
                [$this->pid, $this->encounter]
            );
        }
        if ($this->pid > 0) {
            QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pid = ?', [$this->pid]);
        }
        if ($this->partnerId > 0) {
            QueryUtils::sqlStatementThrowException('DELETE FROM x12_partners WHERE id = ?', [$this->partnerId]);
        }
        foreach ([$this->billingFacilityId, $this->serviceFacilityId] as $facilityId) {
            if ($facilityId > 0) {
                QueryUtils::sqlStatementThrowException('DELETE FROM facility WHERE id = ?', [$facilityId]);
            }
        }
        if ($this->providerId > 0) {
            QueryUtils::sqlStatementThrowException('DELETE FROM users WHERE id = ?', [$this->providerId]);
        }
    }
}
