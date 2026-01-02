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

namespace OpenEMR\Setting\Repository;

use OpenEMR\Common\Database\DatabaseManager;
use OpenEMR\Common\Database\Repository\AbstractRepository;

/**
 * Usage:
 *   $listOptionRepositoryRepository = ListOptionRepository::getInstance();
 *   $ccdaSectionPossibleValues = $listOptionRepositoryRepository->findByListId('ccda-sections')
 *
 * @phpstan-type TListOption = array{
 *     list_id: int,
 *     option_id: int,
 *     title: string,
 *     seq: int,
 *     is_default: bool,
 *     activity: bool,
 *     codes: bool
 * }
 *
 * @template-extends AbstractRepository<TListOption>
 */
class ListOptionRepository extends AbstractRepository
{
    protected static function createInstance(): static
    {
        return new self(
            DatabaseManager::getInstance(),
            'list_options',
            [
                'is_default' => 'DESC',
                'seq' => 'ASC',
                'title' => 'ASC',
            ]
        );
    }

    /**
     * @phpstan-return array<TListOption>
     */
    public function findByListId(string $listId): array
    {
        return $this->findBy([
            'list_id' => $listId,
        ]);
    }
}
