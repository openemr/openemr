<?php

/**
 * EncounterSessionUtil refactored from encounter.inc.php handles setting the encounter in the session
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    <Unknown> Authorship was not listed in encounter.inc.php
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Session;

use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\FormService;

class EncounterSessionUtil
{
    /**
     * Read the active encounter from the session, normalized to int.
     *
     * Mirrors `interface/globals.php`: an unset, empty, or "0" encounter
     * collapses to 0 — callers rely on that to mean "no current encounter".
     */
    public static function getEncounter(): int
    {
        $raw = SessionWrapperFactory::getInstance()->getActiveSession()->get('encounter');
        return is_numeric($raw) ? (int) $raw : 0;
    }

    public static function setEncounter(string $enc): int
    {
        global $encounter;
        global $pid;
        global $attendant_type;

        $formsService = new FormService();

        $session = SessionWrapperFactory::getInstance()->getActiveSession();

        $attendant_id = $attendant_type === 'pid' ? $pid : $session->get('therapy_group');

        // Forcing enc through an integer to protect from sql injection
        $enc = (string) intval($enc);
        if ($enc === "0") {
            $enc = date("Ymd");
            $return_val = $formsService->getFormByEncounter($attendant_id, $enc)
                ? 1 // there is an encounter entered for today
                : 0;
        }

        SessionUtil::setSession('encounter', $enc);
        $encounter = $enc;

        // returns 1 on successful global set, or 0 if there was no
        // current encounter, signifying that the interface should load
        // the screen for a new encounter
        return $return_val ?? 1;
    }
}
