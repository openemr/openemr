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

namespace OpenEMR\Common\Database\Repository\Settings;

use OpenEMR\Common\Database\DatabaseManager;
use OpenEMR\Common\Database\Repository\AbstractRepository;

/**
 * Usage:
 *   $categoryRepository = PostCalendarCategoryRepository::getInstance();
 *   $activeCategories = $categoryRepository->findActive();
 *
 * @phpstan-type TCategory = array{
 *     pc_catid: int,
 *     pc_constant_id: string,
 *     pc_catname: string,
 *     pc_catcolor: string,
 *     pc_catdesc: string,
 *     pc_recurrtype: int,
 *     pc_enddate: string,
 *     pc_recurrspec: string,
 *     pc_recurrfreq: int,
 *     pc_duration: int,
 *     pc_end_date_flag: int,
 *     pc_end_date_type: int,
 *     pc_end_date_freq: int,
 *     pc_end_all_day: int,
 *     pc_dailylimit: int,
 *     pc_cattype: int,
 *     pc_active: int,
 *     pc_seq: int,
 *     aco_spec: string,
 *     pc_last_updated: string,
 * }
 *
 * @template-extends AbstractRepository<TCategory>
 */
class PostCalendarCategoryRepository extends AbstractRepository
{
    protected static function createInstance(): static
    {
        return new self(
            DatabaseManager::getInstance(),
            'openemr_postcalendar_categories',
            [
                'pc_seq' => 'ASC',
            ],
        );
    }

    /**
     * @phpstan-return array<TCategory>
     */
    public function findActive(): array
    {
        return $this->findBy(['pc_active' => 1]);
    }
}
