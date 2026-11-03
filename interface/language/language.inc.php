<?php

/**
 * language.inc.php script
 *
 * Thin delegator kept for the existing call sites. The body lives in LanguageService; see
 * the migration tracker, openemr/openemr#11674.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Services\LanguageService;

function check_pattern($data, $pat): bool
{
    return LanguageService::checkPattern((string) $data, (string) $pat);
}

// Function to insert/modify items in the language log table, lang_custom
// (non-string arguments are ignored)
//
function insert_language_log($lang_desc, $lang_code, $cons_name, $def): void
{
    if (!is_string($lang_desc) || !is_string($lang_code) || !is_string($cons_name) || !is_string($def)) {
        return;
    }
    LanguageService::insertLanguageLog($lang_desc, $lang_code, $cons_name, $def);
}
