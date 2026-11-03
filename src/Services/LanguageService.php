<?php

/**
 * LanguageService: pattern checking and lang_custom log entry maintenance for the language admin
 * screens (moved here from interface/language/language.inc.php).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;

class LanguageService extends BaseService
{
    public const TABLE_NAME = 'lang_custom';

    /**
     * Binds the service to the lang_custom table; both methods below are static.
     */
    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    /**
     * Whether $data matches $pattern used as a (slash-escaped) regular expression.
     *
     * Moved from interface/language/language.inc.php (check_pattern).
     */
    public static function checkPattern(string $data, string $pattern): bool
    {
        if (preg_match("/" . addcslashes($pattern, '/') . "/", $data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Inserts or updates the appropriate lang_custom log entry, depending on which of
     * $langDesc/$consName is empty:
     * - $consName === '': a new language, deduplicated by lang_description.
     * - $langDesc === '': a new constant, deduplicated by constant_name.
     * - both set: a full entry, inserted, left alone if identical, or updated in place if the
     *   definition for that description/constant pair changed.
     *
     * Moved from interface/language/language.inc.php (insert_language_log).
     */
    public static function insertLanguageLog(string $langDesc, string $langCode, string $consName, string $def): void
    {
        // set up the mysql collation string to ensure case is sensitive in the mysql queries
        $case_sensitive_collation = "COLLATE utf8mb4_bin";

        if ($consName == '') {
            // NEW LANGUAGE
            // (ensure not a repeat log entry)
            $sql = "SELECT * FROM lang_custom WHERE constant_name='' AND lang_description " . $case_sensitive_collation . " =?";
            $res_test = QueryUtils::querySingleRow($sql, [$langDesc]);
            if (!is_array($res_test)) {
                $sql = "INSERT INTO lang_custom SET lang_code=?, lang_description=?";
                QueryUtils::sqlStatementThrowException($sql, [$langCode, $langDesc]);
            }
        } elseif ($langDesc == '') {
            // NEW CONSTANT
            // (ensure not a repeat entry)
            $sql = "SELECT * FROM lang_custom WHERE lang_description='' AND constant_name " . $case_sensitive_collation . " =?";
            $res_test = QueryUtils::querySingleRow($sql, [$consName]);
            if (!is_array($res_test)) {
                $sql = "INSERT INTO lang_custom SET constant_name=?";
                QueryUtils::sqlStatementThrowException($sql, [$consName]);
            }
        } else {
            // FULL ENTRY
            // (ensure not a repeat log entry)
            $sql = "SELECT * FROM lang_custom WHERE lang_description " . $case_sensitive_collation . " =? AND constant_name " . $case_sensitive_collation . " =? AND definition " . $case_sensitive_collation . " =?";
            $res_test = QueryUtils::querySingleRow($sql, [$langDesc, $consName, $def]);
            if (!is_array($res_test)) {
                // either modify already existing log entry or create a new one
                $sql = "SELECT * FROM lang_custom WHERE lang_description " . $case_sensitive_collation . " =? AND constant_name " . $case_sensitive_collation . " =?";
                $res_test2 = QueryUtils::querySingleRow($sql, [$langDesc, $consName]);
                if (is_array($res_test2)) {
                    // modify existing log entry(s)
                    $sql = "UPDATE lang_custom SET definition=? WHERE lang_description " . $case_sensitive_collation . " =? AND constant_name " . $case_sensitive_collation . " =?";
                    QueryUtils::sqlStatementThrowException($sql, [$def, $langDesc, $consName]);
                } else {
                    // create new log entry
                    $sql = "INSERT INTO lang_custom (lang_description,lang_code,constant_name,definition) VALUES (?,?,?,?)";
                    QueryUtils::sqlStatementThrowException($sql, [$langDesc, $langCode, $consName, $def]);
                }
            }
        }
    }
}
