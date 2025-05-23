<?php

/**
 * Helper class for dealing with language translations.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2016-2024 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2024 Open Plan IT Ltd. <support@openplanit.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Utils;

class TranslationService
{
    public static function getLanguageDefinitionsForSession()
    {
        $language = $_SESSION['language_choice'] ?? '1'; // defaults english
        return self::getLanguageDefinitionsForLanguage($language);
    }

    public static function getLanguageDefinitionsForLanguage(int $languageId)
    {
        $sql = "SELECT c.constant_name, d.definition FROM lang_definitions as d
        JOIN lang_constants AS c ON d.cons_id = c.cons_id
        WHERE d.lang_id = ?";
        $tarns = sqlStatement($sql, $languageId);
        $language_defs = array();
        while ($row = SqlFetchArray($tarns)) {
            $language_defs[$row['constant_name']] = $row['definition'];
        }
        return $language_defs;
    }
}
