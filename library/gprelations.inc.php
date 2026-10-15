<?php

/**
 * Thin delegators kept for the existing call sites of library/gprelations.inc.php.
 * The bodies live in GpRelationService; see the migration tracker, openemr/openemr#11674.
 *
 * Type codes for the gprelations table are documented in GpRelationService's docblock.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (C) 2009 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Services\GpRelationService;

/**
 * Whether a relation between the two given records already exists in gprelations.
 */
function isGpRelation($type1, $id1, $type2, $id2): bool
{
    if (!is_int($type1) && !is_string($type1)) {
        return false;
    }
    if (!is_int($id1) && !is_string($id1)) {
        return false;
    }
    if (!is_int($type2) && !is_string($type2)) {
        return false;
    }
    if (!is_int($id2) && !is_string($id2)) {
        return false;
    }
    return GpRelationService::isGpRelation($type1, $id1, $type2, $id2);
}

/**
 * Creates or removes a relation between the two given records. $set keeps the legacy signature
 * (accepts anything) and is cast to bool, the exact translation of the original `if (!$set)`
 * truthiness check.
 */
function setGpRelation($type1, $id1, $type2, $id2, $set = true): void
{
    if (!is_int($type1) && !is_string($type1)) {
        return;
    }
    if (!is_int($id1) && !is_string($id1)) {
        return;
    }
    if (!is_int($type2) && !is_string($type2)) {
        return;
    }
    if (!is_int($id2) && !is_string($id2)) {
        return;
    }
    GpRelationService::setGpRelation($type1, $id1, $type2, $id2, (bool) $set);
}
