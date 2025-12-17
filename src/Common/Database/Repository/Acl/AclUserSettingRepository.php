<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Database\Repository\Acl;

use OpenEMR\Common\Database\DatabaseManager;
use OpenEMR\Common\Database\Repository\AbstractRepository;

/**
 * Usage:
 *   $aclUserSettingRepository = RepositoryFactory::createRepository(AclUserSettingRepository::class);
 *   $settings = $aclUserSettingRepository->findAll();
 *   $sectionSettings = $aclUserSettingRepository->findBySectionId($sectionId);
 *
 * @phpstan-type TAclUserSetting = array{
 *     module_id: int,
 *     user_id: int,
 *     section_id: int,
 *     allowed: int,
 * }
 *
 * @template-extends AbstractRepository<TAclUserSetting>
 */
class AclUserSettingRepository extends AbstractRepository
{
    protected static function createInstance(): static
    {
        return new static(
            DatabaseManager::getInstance(),
            'module_acl_user_settings',
        );
    }

    public function normalize(array $data): array
    {
        return [
            'module_id' => (int) $data['module_id'],
            'user_id' => (int) $data['user_id'],
            'section_id' => (int) $data['section_id'],
            'allowed' => (int) $data['allowed'],
        ];
    }

    /**
     * @phpstan-return array<TAclUserSetting>
     */
    public function findBySectionId(int $sectionId): array
    {
        return $this->findBy(['section_id' => $sectionId]);
    }
}
