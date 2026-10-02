<?php

/**
 * Thin delegators kept for the existing call sites of library/encounter.inc.php.
 * The bodies live in EncounterService; see the migration tracker, openemr/openemr#11674.
 */

use OpenEMR\Common\Session\EncounterSessionUtil;
use OpenEMR\Services\EncounterService;

//function called to set the global session variable for encounter number
function setencounter($enc)
{
    return EncounterSessionUtil::setEncounter($enc);
}

/**
 * Fetches the encounter pc_catid by encounter number.
 */
function fetchCategoryIdByEncounter($encounter)
{
    if (!is_int($encounter) && !is_string($encounter)) {
        return null;
    }
    return EncounterService::fetchCategoryIdByEncounter($encounter);
}

/**
 * Date of service (YYYY-MM-DD) of an encounter.
 */
function fetchDateService($encounter): string
{
    if (!is_int($encounter) && !is_string($encounter)) {
        return '';
    }
    return EncounterService::fetchDateService($encounter);
}
