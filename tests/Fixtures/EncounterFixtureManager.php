<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;

class EncounterFixtureManager
{
    private $encounters = [];

    /**
     * Create a test encounter
     *
     * @param array $data Encounter data
     * @return array Created encounter data
     */
    public function createEncounter($data)
    {
        $sql = "INSERT INTO form_encounter SET 
                pid = ?,
                encounter = ?,
                date = ?,
                reason = ?,
                facility = ?,
                facility_id = ?,
                provider_id = ?,
                signed = ?,
                signed_time = ?,
                signed_by = ?,
                uuid = ?";

        $uuid = UuidRegistry::uuidToString((new UuidRegistry(['table_name' => 'form_encounter']))->createUuid());
        
        $params = [
            $data['pid'] ?? 0,
            $data['encounter'] ?? $this->getUnusedEncounterId(),
            $data['date'] ?? date('Y-m-d H:i:s'),
            $data['reason'] ?? 'Test encounter',
            $data['facility'] ?? 'Test Facility',
            $data['facility_id'] ?? 1,
            $data['provider_id'] ?? 1,
            $data['signed'] ?? 1,
            $data['signed_time'] ?? date('Y-m-d H:i:s'),
            $data['signed_by'] ?? 'Test Provider',
            $uuid
        ];

        $id = QueryUtils::sqlInsert($sql, $params);
        
        $encounter = [
            'id' => $id,
            'uuid' => $uuid,
            'pid' => $params[0],
            'encounter' => $params[1]
        ];
        
        $this->encounters[] = $encounter;
        return $encounter;
    }

    /**
     * Get an unused encounter ID
     *
     * @return int
     */
    public function getUnusedEncounterId()
    {
        $result = QueryUtils::fetchRecordsNoLog(
            "SELECT MAX(encounter) as max_encounter FROM form_encounter"
        );
        
        return (int)($result[0]['max_encounter'] ?? 0) + 1;
    }

    /**
     * Remove all test fixtures
     */
    public function removeFixtures()
    {
        if (empty($this->encounters)) {
            return;
        }

        $ids = array_column($this->encounters, 'id');
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        
        // Delete from form_encounter
        $sql = "DELETE FROM form_encounter WHERE id IN ($placeholders)";
        QueryUtils::sqlStatementNoLog($sql, $ids);
        
        // Clear the fixtures array
        $this->encounters = [];
    }
}
