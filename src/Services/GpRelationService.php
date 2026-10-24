<?php

/**
 * GpRelationService: many-to-many links between records of different tables, stored in the
 * gprelations table (moved here from library/gprelations.inc.php).
 *
 * Type codes: 1 documents, 2 form_encounter (visits), 3 immunizations, 4 lists (issues),
 * 5 openemr_postcalendar_events (appointments), 6 pnotes, 7 prescriptions,
 * 8 transactions (e.g. referrals). By convention type1 <= type2.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (C) 2009 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;

class GpRelationService extends BaseService
{
    public const TABLE_NAME = 'gprelations';

    /**
     * Binds the service to the gprelations table; the lookups themselves are static.
     */
    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    /**
     * Whether a relation between the two given records already exists in gprelations.
     *
     * Moved from library/gprelations.inc.php (isGpRelation).
     */
    public static function isGpRelation(int|string $type1, int|string $id1, int|string $type2, int|string $id2): bool
    {
        $row = QueryUtils::querySingleRow(
            "SELECT 1 FROM `gprelations` WHERE `type1` = ? AND `id1` = ? AND `type2` = ? AND `id2` = ? LIMIT 1",
            [$type1, $id1, $type2, $id2]
        );
        return is_array($row);
    }

    /**
     * Creates or removes a relation between the two given records: inserts the row when $set is
     * true and it is missing, deletes it when $set is false and it exists, otherwise does
     * nothing.
     *
     * Moved from library/gprelations.inc.php (setGpRelation).
     */
    public static function setGpRelation(int|string $type1, int|string $id1, int|string $type2, int|string $id2, bool $set = true): void
    {
        $exists = self::isGpRelation($type1, $id1, $type2, $id2);
        if ($exists && !$set) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM `gprelations` WHERE `type1` = ? AND `id1` = ? AND `type2` = ? AND `id2` = ?",
                [$type1, $id1, $type2, $id2]
            );
            return;
        }
        if (!$exists && $set) {
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO `gprelations` (`type1`, `id1`, `type2`, `id2`) VALUES (?, ?, ?, ?)",
                [$type1, $id1, $type2, $id2]
            );
        }
    }
}
