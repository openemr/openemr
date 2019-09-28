<?php
/**
 * Functions to globally validate and prepare data for sql database insertion.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2009 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


/**
 * Escape a parameter to prepare for a sql query.
 *
 * @param   string $s  Parameter to be escaped.
 * @return  string     Escaped parameter.
 */
function add_escape_custom($s)
{
    //prepare for safe mysql insertion
    $s = mysqli_real_escape_string($GLOBALS['dbh'], $s);
    return $s;
}

/**
 * Escape a sql limit variable to prepare for a sql query.
 *
 * This will escape integers within the LIMIT ?, ? part of a sql query.
 * Note that there is a maximum value to these numbers, which is why
 * should only use for the LIMIT ? , ? part of the sql query and why
 * this is centralized to a function (in case need to upgrade this
 * function to support larger numbers in the future).
 *
 * @param   string $s  Limit variable to be escaped.
 * @return  string     Escaped limit variable.
 */
function escape_limit($s)
{
    //prepare for safe mysql insertion
    $s = (int)$s;
    return $s;
}

/**
 * Escape/sanitize a sql sort order keyword variable to prepare for a sql query.
 *
 * This will escape/sanitize the sort order keyword. It is done by whitelisting
 * only certain keywords(asc,desc). If the keyword is illegal, then will default
 * to asc.
 *
 * @param   string $s  Sort order keyword variable to be escaped/sanitized.
 * @return  string     Escaped sort order keyword variable.
 */
function escape_sort_order($s)
{
    return escape_identifier(strtolower($s), array("asc","desc"), "first");
}

/**
 * If parameter string contains comma(,) delimeter
 * Splits parameter string into an array, using comma(,) as delimeter
 * else it returns original string
 *
 * @param   string       $s  string to be processed
 * @return  array        $columns   an array formed by spliting $s with comma(,) delimeter
 */

function process_cols_escape($s)
{
    //returns an array of columns
    $columns = explode(",", $s);
    if (count($columns) > 1) {
        return $columns;
    }

    return $s;
}

/**
 * Escape/sanitize a table sql column name for a sql query..
 *
 * This will escape/sanitize the sql column name for a sql query. It is done by whitelisting
 * all of the current sql column names in the openemr database from a table(s). Note that if
 * there is no match, then it will die() and a error message will be sent to the screen and
 * the error log (if $die_if_no_match is true) or it will return false (if $die_if_no_match is false).
 * This function should not be used for escaping tables outside the openemr
 * database (should use escape_identifier() function below for that scenario)
 *
 * @param   string|array  $s               sql column name(s) variable to be escaped/sanitized.
 * @param   array         $tables          The table(s) that the sql columns is from (in an array).
 * @param   boolean       $long            Use long form (ie. table.colname) vs short form (ie. colname).
 * @param   boolean       $die_if_no_match If true, then will die if no match. If false, then will return false if no match.
 * @return  string                         Escaped table name variable.
 */
function escape_sql_column_name($s, $tables, $long = false, $die_if_no_match = true)
{
    // If $s is asterisk return asterisk to select all columns
    if ($s === "*") {
        return "*";
    }

     // If $s is an array process then use recursion to check each column
    if (is_array($s)) {
        $multiple_columns = [];
        foreach ($s as $column) {
            $multiple_columns[] = escape_sql_column_name(trim($column), $tables);
        }
        return implode(", ", $multiple_columns);
    }

    // If the $tables is empty, then process them all
    if (empty($tables)) {
        $res = sqlStatementNoLog("SHOW TABLES");
        $tables = array();
        while ($row=sqlFetchArray($res)) {
            $keys_return = array_keys($row);
            $tables[]=$row[$keys_return[0]];
        }
    }

    // First need to escape the $tables
    $tables_escaped = array();
    foreach ($tables as $table) {
        $tables_escaped[] = escape_table_name($table);
    }

    // Collect all the possible sql columns from the tables
    $columns_options = array();
    foreach ($tables_escaped as $table_escaped) {
        $res = sqlStatementNoLog("SHOW COLUMNS FROM ".$table_escaped);
        while ($row=sqlFetchArray($res)) {
            if ($long) {
                $columns_options[]=$table_escaped.".".$row['Field'];
            } else {
                $columns_options[]=$row['Field'];
            }
        }
    }

    // Now can escape(via whitelisting) the sql column name
    $noMatchMode = ($die_if_no_match) ? "die" : "boolean";
    return escape_identifier($s, $columns_options, $noMatchMode);
}

/**
 * Escape/sanitize a table name for a sql query. This function can also can be used to
 * process tables that contain any upper case letters.
 *
 * This will escape/sanitize the table name for a sql query. It is done by whitelisting
 * all of the current tables in the openemr database. The matching is not case sensitive,
 * although it will attempt a case sensitive match before proceeding to a case insensitive
 * match (see below escape_identifier() function for more details on this). Note that if
 * there is no match, then it will die() and a error message will be sent to the screen
 * and the error log. This function should not be used for escaping tables outside the
 * openemr database (should use escape_identifier() function below for that scenario).
 * Another use of this function is to deal with casing issues that arise in tables that
 * contain upper case letter(s) (these tables can be huge issues when transferring databases
 * from Windows to Linux and vice versa); this function can avoid this issues if run the
 * table name through this function (To avoid confusion, there is a wrapper function
 * entitled mitigateSqlTableUpperCase() that is used when just need to mitigate casing
 * for table names that contain any uppercase letters).
 * @param   string $s  sql table name variable to be escaped/sanitized.
 * @return  string     Escaped table name variable.
 */
function escape_table_name($s)
{
    $res = sqlStatementNoLog("SHOW TABLES");
    $tables_array = array();
    while ($row=sqlFetchArray($res)) {
        $keys_return = array_keys($row);
        $tables_array[]=$row[$keys_return[0]];
    }

    // Now can escape(via whitelisting) the sql table name
    return escape_identifier($s, $tables_array, "die", false);
}

/**
 * Process tables that contain any upper case letters; this is simple a wrapper function of
 * escape_table_name() above when using it for the sole purpose of mitigating sql table names
 * that contain upper case letters.
 *
 * @param   string $s  sql table name variable to be escaped/sanitized.
 * @return  string     Escaped table name variable.
 */
function mitigateSqlTableUpperCase($s)
{
    return escape_table_name($s);
}

/**
 * Escape/sanitize a sql identifier variable to prepare for a sql query.
 *
 * This will escape/sanitize a sql identifier. There are two options provided by this
 * function.
 * The first option is done by whitelisting ($whitelist_items is used) and in this case
 * only certain identifiers (listed in the $whitelist_items array) can be used. If
 * there is no match, then there are 3 modes:
 *                                         'die'     (die and echo an error to screen and log; this is default)
 *                                         'boolean' (return false)
 *                                         'first'   (return the first whitelist token)
 * Note there is an option to allow case insensitive matching; if this option is chosen,
 * it will first attempt a case sensitive match and if this fails, then attempt a case insensitive match.
 * The second option is done by sanitizing ($whitelist_items is not used) and in this case
 * only US alphanumeric,'_' and '.' items are kept in the returned string. Note
 * the second option is still experimental as we figure out the ideal items to
 * filter out of the identifier. The first option is ideal if all the possible identifiers
 * are known, however we realize this may not always be the case.
 *
 * @param   string   $s                Sql identifier variable to be escaped/sanitized.
 * @param   array    $whitelist_items  Items used in whitelisting method (See function description for details of whitelisting method).
 * @param   boolean  $mode_if_no_match Mode for when there is no match. Options are:
 *                                         'die'     (die and echo an error to screen and log; this is default)
 *                                         'boolean' (return false)
 *                                         'first'   (return the first whitelist token)
 * @param   boolean  $case_sens_match  Use case sensitive match (this is default).
 * @return  string|boolean             Escaped/sanitized sql identifier variable or false.
 */
function escape_identifier($s, $whitelist_items, $mode_if_no_match = "die", $case_sens_match = true)
{
    if (is_array($whitelist_items)) {
        // Only return an item within the whitelist_items
        $ok = $whitelist_items;
        // First, search for case sensitive match
        $key = array_search($s, $ok);
        if ($key === false) {
            // No match
            if (!$case_sens_match) {
                // Attempt a case insensitive match
                $ok_UPPER = array_map("strtoupper", $ok);
                $key = array_search(strtoupper($s), $ok_UPPER);
            }

            if ($key === false) {
                // Still no match
                if ($mode_if_no_match == "die") {
                    // No match and 'die' mode is set, so die() and send error messages to screen and log
                    error_Log("ERROR: OpenEMR SQL Escaping ERROR of the following string: ".errorLogEscape($s), 0);
                    die("<br><span style='color:red;font-weight:bold;'>".xlt("There was an OpenEMR SQL Escaping ERROR of the following string")." ".text($s)."</span><br>");
                } else if ($mode_if_no_match == "first") {
                    // No match and 'first' mode is set, so return first token
                    $key = 0;
                } else { // $mode_if_no_match == "boolean"
                    // No match and 'boolean' mode is set, so return false
                    return false;
                }
            }
        }
        return $ok[$key];
    } else {
        // Return an item that has been "cleaned" up
        // (this is currently experimental and goal is to avoid using this)
        return preg_replace('/[^a-zA-Z0-9_.]/', '', $s);
    }
}

/**
 * (Note this function is deprecated for new scripts and is only utilized to support legacy scripts)
 * Function to manage POST, GET, and REQUEST variables.
 *
 * @param string $name name of the variable requested.
 * @param string $type 'P', 'G' for post or get data, otherwise uses request.
 * @param bool $istrim whether to use trim() on the data.
 * @return string variable requested, or empty string
 */
function formData($name, $type = 'P', $isTrim = false)
{
    if ($type == 'P') {
        $s = isset($_POST[$name]) ? $_POST[$name] : '';
    } else if ($type == 'G') {
        $s = isset($_GET[$name]) ? $_GET[$name] : '';
    } else {
        $s = isset($_REQUEST[$name]) ? $_REQUEST[$name] : '';
    }

    return formDataCore($s, $isTrim);
}

/**
 * (Note this function is deprecated for new scripts and is only utilized to support legacy scripts)
 * NEED TO KEEP THIS FUNCTION TO ENSURE LEGACY FORMS ARE SUPPORTED
 * Core function that will be called by formData.
 * Note it can also be called directly if preparing
 * normal variables (not GET,POST, or REQUEST)
 *
 * @param string $s
 * @param bool $istrim whether to use trim() on the data.
 * @return string
 */
function formDataCore($s, $isTrim = false)
{
    //trim if selected
    if ($isTrim) {
        $s = trim($s);
    }

    //add escapes for safe database insertion
    $s = add_escape_custom($s);
    return $s;
}
