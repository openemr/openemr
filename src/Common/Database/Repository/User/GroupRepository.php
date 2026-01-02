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

namespace OpenEMR\Common\Database\Repository\User;

use OpenEMR\Common\Database\DatabaseManager;
use OpenEMR\Common\Database\Repository\AbstractRepository;

/**
 * Related to groups DB table
 *
 * Usage:
 *   $groupsRepository = GroupRepository::getInstance();
 *   $user = $groupsRepository->find($groupId);
 *
 * @phpstan-type TGroup = array{
 *     id: int,
 *     name: string,
 *     user: string
 * }
 *
 * @template-extends AbstractRepository<TGroup>
 */
class GroupRepository extends AbstractRepository
{
    protected static function createInstance(): static
    {
        return new self(
            DatabaseManager::getInstance(),
            'groups',
        );
    }
}
