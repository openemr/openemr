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
 * As section table has section_id primary key rather than just id -
 * we have custom find & remove methods
 *
 * Usage:
 *   $aclSectionRepository = AclSectionRepository::getInstance();
 *   $sections = $aclSectionRepository->findAll();
 *   $section = $aclSectionRepository->find(5);
 *   $affected = $aclSectionRepository->remove(5);
 *
 * @phpstan-type TAclSection = array{
 *     parent_section: int,
 *     section_id: int,
 *     section_identifier: string,
 *     section_name: string,
 *     module_id: int
 * }
 *
 * @template-extends AbstractRepository<TAclSection>
 */
class AclSectionRepository extends AbstractRepository
{
    protected static function createInstance(): static
    {
        return new self(
            DatabaseManager::getInstance(),
            'module_acl_sections',
        );
    }

    public function normalize(array $data): array
    {
        return [
            'parent_section' => (int) $data['parent_section'],
            'section_id' => (int) $data['section_id'],
            'section_identifier' => $data['section_identifier'],
            'section_name' => $data['section_name'],
            'module_id' => (int) $data['module_id'],
        ];
    }

    public function find(int|string $id): null|array
    {
        return $this->findOneBy(['section_id' => $id]);
    }

    public function remove(int|string $id): int
    {
        return $this->removeBy(['section_id' => $id]);
    }
}
