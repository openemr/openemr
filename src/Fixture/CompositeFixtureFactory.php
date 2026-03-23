<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Fixture;

use OpenEMR\Common\Database\DatabaseManager;
use OpenEMR\Common\Database\DatabaseTables;

/**
 * Usage:
 *
 *  protected function setUp(): void {
 *      // Purge data
 *      $this->purger = CompositePurgerFactory::createPurgeable();
 *      $this->purger->purge();
 *
 *      // Load isolated data from fixtures
 *      $this->fixture = CompositeFixtureFactory::createLikeCleanInstallation();
 *      OR
 *      $this->fixture = new CompositeFixture([
 *          ...CompositeFixtureFactory::createLikeCleanInstallation()->getFixtures(),
 *          AdditionalUserFixture::getInstance(),
 *          Additional*Fixture::getInstance(),
 *      ]);
 *      $this->fixture->load();
 *
 *      // Create test client
 *  }
 *
 *  protected function tearDown(): void {
 *      // Restore purged data
 *      $this->purger->restore();
 *  }
 */
class CompositeFixtureFactory
{
    private const CLEAN_INSTALLATION = [
        // ACL
        // Note, we're not using AXO, so omitting axo tables here
        DatabaseTables::TABLE_GACL_ACL => ['clean/acl/gacl_acl.json'],
        DatabaseTables::TABLE_GACL_ACL_SEQ => ['clean/acl/gacl_acl_seq.json'],
        DatabaseTables::TABLE_GACL_ACL_SECTIONS => ['clean/acl/gacl_acl_sections.json'],

        DatabaseTables::TABLE_GACL_ACO => ['clean/acl/gacl_aco.json'],
        DatabaseTables::TABLE_GACL_ACO_SEQ => ['clean/acl/gacl_aco_seq.json'],
        DatabaseTables::TABLE_GACL_ACO_MAP => ['clean/acl/gacl_aco_map.json'],

        DatabaseTables::TABLE_GACL_ACO_SECTIONS => ['clean/acl/gacl_aco_sections.json'],
        DatabaseTables::TABLE_GACL_ACO_SECTIONS_SEQ => ['clean/acl/gacl_aco_sections_seq.json'],

        DatabaseTables::TABLE_GACL_ARO => ['clean/acl/gacl_aro.json'],
        DatabaseTables::TABLE_GACL_ARO_SEQ => ['clean/acl/gacl_aro_seq.json'],
        DatabaseTables::TABLE_GACL_ARO_MAP => ['clean/acl/gacl_aro_map.json'],

        DatabaseTables::TABLE_GACL_ARO_SECTIONS => ['clean/acl/gacl_aro_sections.json'],
        DatabaseTables::TABLE_GACL_ARO_SECTIONS_SEQ => ['clean/acl/gacl_aro_sections_seq.json'],

        DatabaseTables::TABLE_GACL_ARO_GROUPS => ['clean/acl/gacl_aro_groups.json'],
        DatabaseTables::TABLE_GACL_ARO_GROUPS_MAP => ['clean/acl/gacl_aro_groups_map.json'],
        DatabaseTables::TABLE_GACL_ARO_GROUPS_ID_SEQ => ['clean/acl/gacl_aro_groups_id_seq.json'],

        DatabaseTables::TABLE_GACL_GROUPS_ARO_MAP => ['clean/acl/gacl_groups_aro_map.json'],

        DatabaseTables::TABLE_MODULE_ACL_GROUP_SETTINGS => ['clean/acl/module_acl_group_settings.json'],
        DatabaseTables::TABLE_MODULE_ACL_SECTIONS => ['clean/acl/module_acl_sections.json'],
        DatabaseTables::TABLE_MODULE_ACL_USER_SETTINGS => ['clean/acl/module_acl_user_settings.json'],

        // Auth
        DatabaseTables::TABLE_GROUPS => ['clean/auth/groups.json'],
        // DatabaseTables::TABLE_OAUTH_CLIENTS => ['clean/auth/oauth_clients.json'], // Empty on clean installation
        DatabaseTables::TABLE_USERS => ['clean/auth/users.json'],
        DatabaseTables::TABLE_USERS_SECURE => ['clean/auth/users_secure.json'],
    ];

    /**
     * Create fixtures that reflect clean OpenEMR installation
     */
    public static function createLikeCleanInstallation(): CompositeFixture
    {
        return new CompositeFixture(array_map(
            fn (string $table, array $filenames) => new DbAwareAbstractFixture(
                DatabaseManager::getInstance(),
                $table,
                $filenames,
            ),
            array_keys(self::CLEAN_INSTALLATION),
            array_values(self::CLEAN_INSTALLATION),
        ));
    }
}
