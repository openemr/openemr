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

namespace OpenEMR\Common\Database\Repository\Acl;

use OpenEMR\Common\Database\DatabaseManager;
use OpenEMR\Common\Database\Repository\AbstractRepository;

/**
 * Usage:
 *   $aclGroupSettingRepository = AclGroupSettingRepository::getInstance();
 *   $settings = $aclGroupSettingRepository->findAll();
 *   $sectionSettings = $aclGroupSettingRepository->findBySectionId($sectionId);
 *
 * @phpstan-type TAclGroupSetting = array{
 *     module_id: int,
 *     group_id: int,
 *     section_id: int,
 *     allowed: int,
 * }
 *
 * @template-extends AbstractRepository<TAclGroupSetting>
 */
class AclGroupSettingRepository extends AbstractRepository
{
    protected static function createInstance(): static
    {
        return new self(
            DatabaseManager::getInstance(),
            'module_acl_group_settings',
        );
    }

    public function normalize(array $data): array
    {
        return [
            'module_id' => (int) $data['module_id'],
            'group_id' => (int) $data['group_id'],
            'section_id' => (int) $data['section_id'],
            'allowed' => (int) $data['allowed'],
        ];
    }

    /**
     * @phpstan-return array<TAclGroupSetting>
     */
    public function findBySectionId(int $sectionId): array
    {
        return $this->findBy(['section_id' => $sectionId]);
    }
}
