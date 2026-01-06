<?php

/**
 * language.inc.php script
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function check_pattern($data, $pat)
{
    if (preg_match("/" . addcslashes((string) $pat, '/') . "/", (string) $data)) {
        return true ;
    } else {
        return false;
    }
}

// Function to insert/modify items in the language log table, lang_custom
//
function insert_language_log($lang_desc, $lang_code, $cons_name, $def): void
{
    global $disable_utf8_flag, $sqlconf;

  // set up the mysql collation string to ensure case is sensitive in the mysql queries
    if (!$disable_utf8_flag) {
        if (!empty($sqlconf["db_encoding"]) && ($sqlconf["db_encoding"] == "utf8mb4")) {
            $case_sensitive_collation = "COLLATE utf8mb4_bin";
        } else {
            $case_sensitive_collation = "COLLATE utf8_bin";
        }
    } else {
        $case_sensitive_collation = "COLLATE latin1_bin";
    }


    if ($cons_name == '') {
        // NEW LANGUAGE
        // (ensure not a repeat log entry)
        $sql = "SELECT * FROM lang_custom WHERE constant_name='' AND lang_description " . $case_sensitive_collation . " =?";
        $res_test = sqlStatement($sql, [$lang_desc]);
        if (!sqlFetchArray($res_test)) {
            $sql = "INSERT INTO lang_custom SET lang_code=?, lang_description=?";
            sqlStatement($sql, [$lang_code, $lang_desc]);
        }
    } elseif ($lang_desc == '') {
        // NEW CONSTANT
        // (ensure not a repeat entry)
        $sql = "SELECT * FROM lang_custom WHERE lang_description='' AND constant_name " . $case_sensitive_collation . " =?";
        $res_test = sqlStatement($sql, [$cons_name]);
        if (!sqlFetchArray($res_test)) {
            $sql = "INSERT INTO lang_custom SET constant_name=?";
            sqlStatement($sql, [$cons_name]);
        }
    } else {
        // FULL ENTRY
        // (ensure not a repeat log entry)
        $sql = "SELECT * FROM lang_custom WHERE lang_description " . $case_sensitive_collation . " =? AND constant_name " . $case_sensitive_collation . " =? AND definition " . $case_sensitive_collation . " =?";
        $res_test = sqlStatement($sql, [$lang_desc, $cons_name, $def]);
        if (!sqlFetchArray($res_test)) {
            // either modify already existing log entry or create a new one
            $sql = "SELECT * FROM lang_custom WHERE lang_description " . $case_sensitive_collation . " =? AND constant_name " . $case_sensitive_collation . " =?";
            $res_test2 = sqlStatement($sql, [$lang_desc, $cons_name]);
            if (sqlFetchArray($res_test2)) {
                // modify existing log entry(s)
                $sql = "UPDATE lang_custom SET definition=? WHERE lang_description " . $case_sensitive_collation . " =? AND constant_name " . $case_sensitive_collation . " =?";
                sqlStatement($sql, [$def, $lang_desc, $cons_name]);
            } else {
                // create new log entry
                $sql = "INSERT INTO lang_custom (lang_description,lang_code,constant_name,definition) VALUES (?,?,?,?)";
                sqlStatement($sql, [$lang_desc, $lang_code, $cons_name, $def]);
            }
        }
    }
}
