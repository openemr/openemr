<?php

/**
 * AddressService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Address\AddressData;
use OpenEMR\Services\Address\AddressRecord;
use Particle\Validator\ValidationResult;
use Particle\Validator\Validator;

class AddressService extends BaseService
{
    public function __construct()
    {
    }

    /**
     * Validate address data against length constraints.
     */
    public function validate(AddressData $address): ValidationResult
    {
        $validator = new Validator();

        $validator->optional('line1')->lengthBetween(2, 255);
        $validator->optional('line2')->lengthBetween(2, 255);
        $validator->optional('city')->lengthBetween(2, 255);
        $validator->optional('state')->lengthBetween(2, 35);
        $validator->optional('zip')->lengthBetween(2, 10);
        $validator->optional('plus_four')->lengthBetween(2, 4);
        $validator->optional('country')->lengthBetween(2, 255);

        return $validator->validate($address->toArray());
    }

    /**
     * Format an address record as a multi-line string.
     */
    public function getAddressFromRecordAsString(AddressRecord $addressRecord): string
    {
        return $addressRecord->toString();
    }

    /**
     * Insert a new address record.
     *
     * @return int|false The new address ID, or false on failure
     */
    public function insert(AddressData $data, int $foreignId): int|false
    {
        /** @var int $freshId */
        $freshId = $this->getFreshId("id", "addresses");

        $addressesSql  = " INSERT INTO addresses SET";
        $addressesSql .= "     id=?,";
        $addressesSql .= "     line1=?,";
        $addressesSql .= "     line2=?,";
        $addressesSql .= "     city=?,";
        $addressesSql .= "     state=?,";
        $addressesSql .= "     zip=?,";
        $addressesSql .= "     plus_four=?,";
        $addressesSql .= "     country=?,";
        $addressesSql .= "     foreign_id=?";

        $addressesSqlResults = QueryUtils::sqlInsert(
            $addressesSql,
            [
                $freshId,
                $data->line1,
                $data->line2,
                $data->city,
                $data->state,
                $data->zip,
                $data->plusFour,
                $data->country,
                $foreignId
            ]
        );

        if ($addressesSqlResults === 0) {
            return false;
        }

        return $freshId;
    }

    /**
     * Update an existing address record.
     *
     * @return int|string|null The address ID, or null if not found
     */
    public function update(AddressData $data, int $foreignId): int|string|null
    {
        $addressesSql  = " UPDATE addresses SET";
        $addressesSql .= "     line1=?,";
        $addressesSql .= "     line2=?,";
        $addressesSql .= "     city=?,";
        $addressesSql .= "     state=?,";
        $addressesSql .= "     zip=?,";
        $addressesSql .= "     plus_four=?,";
        $addressesSql .= "     country=?";
        $addressesSql .= "     WHERE foreign_id=?";

        QueryUtils::sqlStatementThrowException(
            $addressesSql,
            [
                $data->line1,
                $data->line2,
                $data->city,
                $data->state,
                $data->zip,
                $data->plusFour,
                $data->country,
                $foreignId
            ]
        );

        /** @var array{id: int|string}|false $addressIdSqlResults */
        $addressIdSqlResults = QueryUtils::querySingleRow(
            "SELECT id FROM addresses WHERE foreign_id=? LIMIT 1",
            [$foreignId]
        );

        return $addressIdSqlResults["id"] ?? null;
    }

    /**
     * Get an address by its foreign ID.
     */
    public function getOneByForeignId(int $foreignId): ?AddressData
    {
        $sql = "SELECT * FROM addresses WHERE foreign_id=? LIMIT 1";
        /** @var array<string, mixed>|false $result */
        $result = QueryUtils::querySingleRow($sql, [$foreignId]);
        if ($result === false) {
            return null;
        }
        return AddressData::fromArray($result);
    }
}
