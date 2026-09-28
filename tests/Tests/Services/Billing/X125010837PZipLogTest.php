<?php

/**
 * genX12837P() writes both facility ZIP warnings into the claim log.
 *
 * The isolated test only reads the constants. This one builds an encounter
 * so an old literal at either ZIP branch fails here.
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
    private const PID = 989551001;

    private const ENCOUNTER = 989551;

    private const PARTNER_NAME = 'zip-log-test-partner';

    private const USERNAME = 'zip-log-test-user';

    private int $billingFacilityId = 0;

    private int $serviceFacilityId = 0;

    private int $partnerId = 0;

    private int $providerId = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->removeFixture();
    }

    protected function tearDown(): void
    {
        $this->removeFixture();
        parent::tearDown();
    }

    public function testShortFacilityZipsAreWrittenIntoTheClaimLog(): void
    {
        [$claimText, $log] = $this->generateClaim('10101', '20202');

        $this->assertStringContainsString(X125010837P::BILLING_ZIP_LOG, $log);
        $this->assertStringContainsString(X125010837P::SERVICE_ZIP_LOG, $log);
        $this->assertStringNotContainsString('Rejecting claim', $log);
        $this->assertStringContainsString('*10101~', $claimText);
        $this->assertStringContainsString('*20202~', $claimText);
    }

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
        $this->providerId = $this->insertId(
            'INSERT INTO users (username, fname, lname, npi, active) VALUES (?, ?, ?, ?, 1)',
            [self::USERNAME, 'Zip', 'Logger', '1234567893']
        );
        $this->billingFacilityId = $this->insertFacility('zip-log-test-billing', $billingZip);
        $this->serviceFacilityId = $this->insertFacility('zip-log-test-service', $serviceZip);
        $this->partnerId = $this->insertPartner();
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO patient_data (pid, fname, lname, mname, DOB, sex, status) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [self::PID, 'Ada', 'Lovelace', '', '1980-01-01', 'Female', 'single']
        );
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO form_encounter (pid, encounter, date, facility_id, billing_facility, provider_id, pos_code, reason)'
            . ' VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                self::PID,
                self::ENCOUNTER,
                '2026-09-25 10:00:00',
                $this->serviceFacilityId,
                $this->billingFacilityId,
                $this->providerId,
                11,
                'zip-log-test',
            ]
        );
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO billing (pid, encounter, code, code_type, code_text, units, fee, provider_id, payer_id, activity)'
            . ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)',
            [self::PID, self::ENCOUNTER, '99213', 'CPT4', 'Office visit', 1, 100, $this->providerId, 0]
        );

        $log = '';
        $edicount = 0;
        $patSegmentCount = 0;
        $claimText = X125010837P::genX12837P(
            self::PID,
            self::ENCOUNTER,
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
     * x12_partners.id is not auto-increment. It defaults to 0, and a second insert then collides.
     */
    private function insertPartner(): int
    {
        $id = $this->allocatePartnerId();
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO x12_partners (id, name, x12_sender_id, x12_receiver_id, x12_gs02, x12_per06)'
            . ' VALUES (?, ?, ?, ?, ?, ?)',
            [$id, self::PARTNER_NAME, 'SENDER', 'RECEIVER', 'ZIPLOG', '5555550100']
        );

        return $id;
    }

    private function allocatePartnerId(): int
    {
        $row = QueryUtils::querySingleRow('SELECT COALESCE(MAX(`id`), 0) + 1 AS next_id FROM x12_partners');
        $nextId = is_array($row) ? ($row['next_id'] ?? null) : null;
        if (is_string($nextId) && ctype_digit($nextId)) {
            $nextId = (int) $nextId;
        }

        if (!is_int($nextId) || $nextId <= 0) {
            $this->fail('Could not allocate an X12 partner id');
        }

        return $nextId;
    }

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

    private function removeFixture(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM billing WHERE pid = ? AND encounter = ?',
            [self::PID, self::ENCOUNTER]
        );
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM form_encounter WHERE pid = ? AND encounter = ?',
            [self::PID, self::ENCOUNTER]
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM x12_partners WHERE name = ?', [self::PARTNER_NAME]);
        if ($this->partnerId > 0) {
            QueryUtils::sqlStatementThrowException('DELETE FROM x12_partners WHERE id = ?', [$this->partnerId]);
        }

        QueryUtils::sqlStatementThrowException(
            'DELETE FROM facility WHERE name IN (?, ?)',
            ['zip-log-test-billing', 'zip-log-test-service']
        );
        foreach ([$this->billingFacilityId, $this->serviceFacilityId] as $facilityId) {
            if ($facilityId > 0) {
                QueryUtils::sqlStatementThrowException('DELETE FROM facility WHERE id = ?', [$facilityId]);
            }
        }

        QueryUtils::sqlStatementThrowException('DELETE FROM users WHERE username = ?', [self::USERNAME]);
        if ($this->providerId > 0) {
            QueryUtils::sqlStatementThrowException('DELETE FROM users WHERE id = ?', [$this->providerId]);
        }
    }
}
