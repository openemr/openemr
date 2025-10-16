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

class ProviderFixtureManager
{
    private $providers = [];

    /**
     * Create a test provider
     *
     * @param array $data Provider data
     * @return array Created provider data
     */
    public function createProvider($data = [])
    {
        $sql = "INSERT INTO users SET 
                username = ?,
                password = ?,
                authorized = ?,
                lname = ?,
                fname = ?,
                active = ?,
                npi = ?,
                specialty = ?,
                uuid = ?";

        $uuid = UuidRegistry::uuidToString((new UuidRegistry(['table_name' => 'users']))->createUuid());
        $id = $data['id'] ?? $this->getUnusedProviderId();
        $username = $data['username'] ?? 'testprovider' . $id;
        
        $params = [
            $username,
            $data['password'] ?? password_hash('password', PASSWORD_BCRYPT),
            $data['authorized'] ?? 1,
            $data['lname'] ?? 'Provider' . $id,
            $data['fname'] ?? 'Test' . $id,
            $data['active'] ?? 1,
            $data['npi'] ?? '123456789' . $id,
            $data['specialty'] ?? 'Family Medicine',
            $uuid
        ];

        $id = QueryUtils::sqlInsert($sql, $params);
        
        $provider = [
            'id' => $id,
            'uuid' => $uuid,
            'username' => $username,
            'lname' => $params[3],
            'fname' => $params[4],
            'npi' => $params[6]
        ];
        
        $this->providers[] = $provider;
        return $provider;
    }

    /**
     * Get a single test provider
     * 
     * @return array
     */
    public function getSingleProvider()
    {
        if (empty($this->providers)) {
            return $this->createProvider();
        }
        
        return $this->providers[0];
    }

    /**
     * Get an unused provider ID
     *
     * @return int
     */
    private function getUnusedProviderId()
    {
        $result = QueryUtils::fetchRecordsNoLog(
            "SELECT MAX(id) as max_id FROM users"
        );
        
        return (int)($result[0]['max_id'] ?? 0) + 1;
    }

    /**
     * Remove all test providers
     */
    public function removeProviderFixtures()
    {
        if (empty($this->providers)) {
            return;
        }

        $ids = array_column($this->providers, 'id');
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        
        // Delete from users
        $sql = "DELETE FROM users WHERE id IN ($placeholders)";
        QueryUtils::sqlStatementNoLog($sql, $ids);
        
        // Clear the providers array
        $this->providers = [];
    }
}
