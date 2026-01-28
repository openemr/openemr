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
use Particle\Validator\ValidationResult;
use Particle\Validator\Validator;

/**
 * @phpstan-type AddressData array{
 *     line1: string,
 *     line2: string,
 *     city: string,
 *     state: string,
 *     zip: string,
 *     plus_four?: string|null,
 *     country: string
 * }
 * @phpstan-type AddressRecord array{
 *     street?: string,
 *     city?: string,
 *     state?: string,
 *     postal_code?: string,
 *     country_code?: string
 * }
 */
class AddressService extends BaseService
{
    public function __construct()
    {
    }

    /**
     * @param AddressData $address
     */
    public function validate(array $address): ValidationResult
    {
        $validator = new Validator();

        $validator->optional('line1')->lengthBetween(2, 255);
        $validator->optional('line2')->lengthBetween(2, 255);
        $validator->optional('city')->lengthBetween(2, 255);
        $validator->optional('state')->lengthBetween(2, 35);
        $validator->optional('zip')->lengthBetween(2, 10);
        $validator->optional('plus_four')->lengthBetween(2, 4);
        $validator->optional('country')->lengthBetween(2, 255);

        return $validator->validate($address);
    }

    /**
     * @param AddressRecord $addressRecord
     */
    public function getAddressFromRecordAsString(array $addressRecord): string
    {
        // works for patients and users
        $address = [];
        if (($addressRecord['street'] ?? '') !== '') {
            $address[] = $addressRecord['street'];
            $address[] = "\n";
        }
        if (($addressRecord['city'] ?? '') !== '') {
            $address[] = $addressRecord['city'];
            $address[] = ", ";
        }
        if (($addressRecord['state'] ?? '') !== '') {
            $address[] = $addressRecord['state'];
            $address[] = " ";
        }
        if (($addressRecord['postal_code'] ?? '') !== '') {
            $address[] = $addressRecord['postal_code'];
            $address[] = " ";
        }
        if (($addressRecord['country_code'] ?? '') !== '') {
            $address[] = $addressRecord['country_code'];
        }
        return implode("", $address);
    }

    /**
     * @param AddressData $data
     * @return int|false
     */
    public function insert(array $data, int $foreignId): int|false
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
                $data["line1"],
                $data["line2"],
                $data["city"],
                $data["state"],
                $data["zip"],
                $data["plus_four"] ?? null,
                $data["country"],
                $foreignId
            ]
        );

        if ($addressesSqlResults === 0) {
            return false;
        }

        return $freshId;
    }

    /**
     * @param AddressData $data
     * @return int|string|null
     */
    public function update(array $data, int $foreignId): int|string|null
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
                $data["line1"],
                $data["line2"],
                $data["city"],
                $data["state"],
                $data["zip"],
                $data["plus_four"] ?? null,
                $data["country"],
                $foreignId
            ]
        );

        /** @var array{id: int|string}|false $addressIdSqlResults */
        $addressIdSqlResults = QueryUtils::querySingleRow(
            "SELECT id FROM addresses WHERE foreign_id=?",
            [$foreignId]
        );

        return $addressIdSqlResults["id"] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getOneByForeignId(int $foreignId): ?array
    {
        $sql = "SELECT * FROM addresses WHERE foreign_id=?";
        /** @var array<string, mixed>|false $result */
        $result = QueryUtils::querySingleRow($sql, [$foreignId]);
        return $result ?: null;
    }
}
