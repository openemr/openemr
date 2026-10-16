<?php

/**
 * A facility edit reports saved only when that row is stored.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\FacilityService;
use PHPUnit\Framework\TestCase;

class FacilityIdStoredTest extends TestCase
{
    private int $facilityId = 0;

    /**
     * Remove only the row this test inserted.
     */
    protected function tearDown(): void
    {
        if ($this->facilityId > 0) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM facility WHERE id = ?',
                [$this->facilityId]
            );
        }
        parent::tearDown();
    }

    /**
     * A stored id is found, and a missing id is not.
     */
    public function testStoredIdIsFoundAndAMissingIdIsNot(): void
    {
        $this->facilityId = QueryUtils::sqlInsert(
            'INSERT INTO facility (name, street, city, state, postal_code, country_code, federal_ein,'
            . ' facility_npi, tax_id_type, color, oid, pos_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                'ZZ ID stored',
                '1 Main',
                'Testville',
                'TX',
                '564701234',
                'US',
                '12-3456789',
                '1234567893',
                'EI',
                '#000000',
                '',
                11,
            ]
        );
        $this->assertGreaterThan(0, $this->facilityId);
        $service = new FacilityService();

        $this->assertTrue($service->facilityIdStored((string) $this->facilityId));
        $this->assertFalse($service->facilityIdStored('0'));
        $this->assertFalse($service->facilityIdStored(''));
        $this->assertFalse($service->facilityIdStored('999999999'));
    }
}
