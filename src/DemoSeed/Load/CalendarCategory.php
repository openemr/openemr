<?php

/**
 * An active calendar category, as the loader looks it up by pc_constant_id.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Load;

use OpenEMR\Common\Database\QueryUtils;

final readonly class CalendarCategory
{
    /** pc_cattype of categories for patient appointments */
    public const PATIENT_TYPE = 0;

    /**
     * @param int $minutes the category's default length (pc_duration)
     * @param int $type pc_cattype: 0 patient, 1 provider, 2 clinic, 3 group
     */
    public function __construct(
        public int $id,
        public string $constantId,
        public string $name,
        public int $minutes,
        public int $type,
    ) {
    }

    /**
     * @return array<string, self> by pc_constant_id
     */
    public static function allActive(): array
    {
        $categories = [];
        $rows = QueryUtils::fetchRecords(
            'SELECT `pc_catid`, `pc_constant_id`, `pc_catname`, `pc_duration`, `pc_cattype`'
            . ' FROM `openemr_postcalendar_categories` WHERE `pc_active` = 1',
        );
        foreach ($rows as $row) {
            $constantId = $row['pc_constant_id'] ?? null;
            $name = $row['pc_catname'] ?? null;
            $id = $row['pc_catid'] ?? null;
            if (!is_string($constantId) || $constantId === '' || !is_string($name) || !is_numeric($id)) {
                continue;
            }
            $duration = $row['pc_duration'] ?? null;
            $type = $row['pc_cattype'] ?? null;
            $categories[$constantId] = new self(
                (int) $id,
                $constantId,
                $name,
                is_numeric($duration) ? intdiv((int) $duration, 60) : 0,
                is_numeric($type) ? (int) $type : self::PATIENT_TYPE,
            );
        }
        return $categories;
    }
}
