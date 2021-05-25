<?php

/**
 * Library and data structure to manage Code Types and code type lookups.
 *
 * The data structure is the $code_types array.
 * The $code_types array is built from the code_types sql table and provides
 * abstraction of diagnosis/billing code types.  This is desirable
 * because different countries or fields of practice use different methods for
 * coding diagnoses, procedures and supplies.  Fees will not be relevant where
 * medical care is socialized.
 * <pre>Attributes of the $code_types array are:
 *  active   - 1 if this code type is activated
 *  id       - the numeric identifier of this code type in the codes table
 *  claim    - 1 if this code type is used in claims
 *  fee      - 1 if fees are used, else 0
 *  mod      - the maximum length of a modifier, 0 if modifiers are not used
 *  just     - the code type used for justification, empty if none
 *  rel      - 1 if other billing codes may be "related" to this code type
 *  nofs     - 1 if this code type should NOT appear in the Fee Sheet
 *  diag     - 1 if this code type is for diagnosis
 *  proc     - 1 if this code type is a procedure/service
 *  label    - label used for code type
 *  external - 0 for storing codes in the code table
 *             1 for storing codes in external ICD10 Diagnosis tables
 *             2 for storing codes in external SNOMED (RF1) Diagnosis tables
 *             3 for storing codes in external SNOMED (RF2) Diagnosis tables
 *             4 for storing codes in external ICD9 Diagnosis tables
 *             5 for storing codes in external ICD9 Procedure/Service tables
 *             6 for storing codes in external ICD10 Procedure/Service tables
 *             7 for storing codes in external SNOMED Clinical Term tables
 *             8 for storing codes in external SNOMED (RF2) Clinical Term tables (for future)
 *             9 for storing codes in external SNOMED (RF1) Procedure Term tables
 *             10 for storing codes in external SNOMED (RF2) Procedure Term tables (for future)
 *  term     - 1 if this code type is used as a clinical term
 *  problem  - 1 if this code type is used as a medical problem
 *  drug     - 1 if this code type is used as a medication
 *
 *  </pre>
 *
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2006-2010 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../library/csv_like_join.php");

$code_types = array();
global $code_types;
$ctres = sqlStatement("SELECT * FROM code_types WHERE ct_active=1 ORDER BY ct_seq, ct_key");
while ($ctrow = sqlFetchArray($ctres)) {
    $code_types[$ctrow['ct_key']] = array(
    'active' => $ctrow['ct_active'  ],
    'id'   => $ctrow['ct_id'  ],
    'fee'  => $ctrow['ct_fee' ],
    'mod'  => $ctrow['ct_mod' ],
    'just' => $ctrow['ct_just'],
    'rel'  => $ctrow['ct_rel' ],
    'nofs' => $ctrow['ct_nofs'],
    'diag' => $ctrow['ct_diag'],
    'mask' => $ctrow['ct_mask'],
    'label' => ( (empty($ctrow['ct_label'])) ? $ctrow['ct_key'] : $ctrow['ct_label'] ),
    'external' => $ctrow['ct_external'],
    'claim' => $ctrow['ct_claim'],
    'proc' => $ctrow['ct_proc'],
    'term' => $ctrow['ct_term'],
    'problem' => $ctrow['ct_problem'],
    'drug' => $ctrow['ct_drug']
    );
    if (array_key_exists($GLOBALS['default_search_code_type'], $code_types)) {
        $default_search_type = $GLOBALS['default_search_code_type'];
    } else {
        reset($code_types);
        $default_search_type = key($code_types);
    }
}

/** This array contains metadata describing the arrangement of the external data
 *  tables for storing codes.
 */
$code_external_tables = array();
global $code_external_tables;
define('EXT_COL_CODE', 'code');
define('EXT_COL_DESCRIPTION', 'description');
define('EXT_COL_DESCRIPTION_BRIEF', 'description_brief');
define('EXT_TABLE_NAME', 'table');
define('EXT_FILTER_CLAUSES', 'filter_clause');
define('EXT_VERSION_ORDER', 'filter_version_order');
define('EXT_JOINS', 'joins');
define('JOIN_TABLE', 'join');
define('JOIN_FIELDS', 'fields');
define('DISPLAY_DESCRIPTION', "display_description");

/**
 * This is a helper function for defining the metadata that describes the tables
 *
 * @param type $results             A reference to the global array which stores all the metadata
 * @param type $index               The external table ID.  This corresponds to the value in the code_types table in the ct_external column
 * @param type $table_name          The name of the table which stores the code informattion (e.g. icd9_dx_code
 * @param type $col_code            The name of the column which is the code
 * @param type $col_description     The name of the column which is the description
 * @param type $col_description_brief The name of the column which is the brief description
 * @param type $filter_clauses      An array of clauses to be included in the search "WHERE" clause that limits results
 * @param type $version_order       How to choose between different revisions of codes
 * @param type $joins               An array which describes additional tables to join as part of a code search.
 */
function define_external_table(&$results, $index, $table_name, $col_code, $col_description, $col_description_brief, $filter_clauses = array(), $version_order = "", $joins = array(), $display_desc = "")
{
    $results[$index] = array(EXT_TABLE_NAME => $table_name,
                           EXT_COL_CODE => $col_code,
                           EXT_COL_DESCRIPTION => $col_description,
                           EXT_COL_DESCRIPTION_BRIEF => $col_description_brief,
                           EXT_FILTER_CLAUSES => $filter_clauses,
                           EXT_JOINS => $joins,
                           EXT_VERSION_ORDER => $version_order,
                           DISPLAY_DESCRIPTION => $display_desc
                           );
}
// In order to treat all the code types the same for lookup_code_descriptions, we include metadata for the original codes table
define_external_table($code_external_tables, 0, 'codes', 'code', 'code_text', 'code_text_short', array(), 'id');

// ICD9 External Definitions
define_external_table($code_external_tables, 4, 'icd9_dx_code', 'formatted_dx_code', 'long_desc', 'short_desc', array("active='1'"), 'revision DESC');
define_external_table($code_external_tables, 5, 'icd9_sg_code', 'formatted_sg_code', 'long_desc', 'short_desc', array("active='1'"), 'revision DESC');
//**** End ICD9 External Definitions

// SNOMED Definitions
// For generic SNOMED-CT, there is no need to join with the descriptions table to get a specific description Type

// For generic concepts, use the fully specified description (DescriptionType=3) so we can tell the difference between them.
define_external_table($code_external_tables, 7, 'sct_descriptions', 'ConceptId', 'Term', 'Term', array("DescriptionStatus=0","DescriptionType=3"), "");

// To determine codes, we need to evaluate data in both the sct_descriptions table, and the sct_concepts table.
// the base join with sct_concepts is the same for all types of SNOMED definitions, so we define the common part here
$SNOMED_joins = array(JOIN_TABLE => "sct_concepts",JOIN_FIELDS => array("sct_descriptions.ConceptId=sct_concepts.ConceptId"));

// For disorders, use the preferred term (DescriptionType=1)
define_external_table($code_external_tables, 2, 'sct_descriptions', 'ConceptId', 'Term', 'Term', array("DescriptionStatus=0","DescriptionType=1"), "", array($SNOMED_joins));
// Add the filter to choose only disorders. This filter happens as part of the join with the sct_concepts table
array_push($code_external_tables[2][EXT_JOINS][0][JOIN_FIELDS], "FullySpecifiedName like '%(disorder)'");

// SNOMED-PR definition
define_external_table($code_external_tables, 9, 'sct_descriptions', 'ConceptId', 'Term', 'Term', array("DescriptionStatus=0","DescriptionType=1"), "", array($SNOMED_joins));
// Add the filter to choose only procedures. This filter happens as part of the join with the sct_concepts table
array_push($code_external_tables[9][EXT_JOINS][0][JOIN_FIELDS], "FullySpecifiedName like '%(procedure)'");

// SNOMED RF2 definitions
define_external_table($code_external_tables, 11, 'sct2_description', 'conceptId', 'term', 'term', array("active=1"), "");
if (isSnomedSpanish()) {
    define_external_table($code_external_tables, 10, 'sct2_description', 'conceptId', 'term', 'term', array("active=1", "term LIKE '%(trastorno)'"), "");
    define_external_table($code_external_tables, 12, 'sct2_description', 'conceptId', 'term', 'term', array("active=1", "term LIKE '%(procedimiento)'"), "");
} else {
    define_external_table($code_external_tables, 10, 'sct2_description', 'conceptId', 'term', 'term', array("active=1", "term LIKE '%(disorder)'"), "");
    define_external_table($code_external_tables, 12, 'sct2_description', 'conceptId', 'term', 'term', array("active=1", "term LIKE '%(procedure)'"), "");
}

//**** End SNOMED Definitions

// ICD 10 Definitions
define_external_table($code_external_tables, 1, 'icd10_dx_order_code', 'formatted_dx_code', 'long_desc', 'short_desc', array("active='1'","valid_for_coding = '1'"), 'revision DESC');
define_external_table($code_external_tables, 6, 'icd10_pcs_order_code', 'pcs_code', 'long_desc', 'short_desc', array("active='1'","valid_for_coding = '1'"), 'revision DESC');
//**** End ICD 10 Definitions

/**
 * This array stores the external table options. See above for $code_types array
 * 'external' attribute  for explanation of the option listings.
 * @var array
 */
global $ct_external_options;
$ct_external_options = array(
  '0' => xl('No'),
  '4' => xl('ICD9 Diagnosis'),
  '5' => xl('ICD9 Procedure/Service'),
  '1' => xl('ICD10 Diagnosis'),
  '6' => xl('ICD10 Procedure/Service'),
  '2' => xl('SNOMED (RF1) Diagnosis'),
  '7' => xl('SNOMED (RF1) Clinical Term'),
  '9' => xl('SNOMED (RF1) Procedure'),
  '10' => xl('SNOMED (RF2) Diagnosis'),
  '11' => xl('SNOMED (RF2) Clinical Term'),
  '12' => xl('SNOMED (RF2) Procedure')
);

/**
 *  Checks to see if using spanish snomed
 */
function isSnomedSpanish()
{
    // See if most recent SNOMED entry is International:Spanish
    $sql = sqlQuery("SELECT `revision_version` FROM `standardized_tables_track` WHERE `name` = 'SNOMED' ORDER BY `id` DESC");
    if ((!empty($sql)) && ($sql['revision_version'] == "International:Spanish")) {
        return true;
    }
    return false;
}

/**
 * Checks is fee are applicable to any of the code types.
 *
 * @return boolean
 */
function fees_are_used()
{
    global $code_types;
    foreach ($code_types as $value) {
        if ($value['fee'] && $value['active']) {
            return true;
        }
    }

    return false;
}

/**
 * Checks if modifiers are applicable to any of the code types.
 * (If a code type is not set to show in the fee sheet, then is ignored)
 *
 * @param  boolean $fee_sheet Will ignore code types that are not shown in the fee sheet
 * @return boolean
 */
function modifiers_are_used($fee_sheet = false)
{
    global $code_types;
    foreach ($code_types as $value) {
        if ($fee_sheet && !empty($value['nofs'])) {
            continue;
        }

        if ($value['mod'] && $value['active']) {
            return true;
        }
    }

    return false;
}

/**
 * Checks if justifiers are applicable to any of the code types.
 *
 * @return boolean
 */
function justifiers_are_used()
{
    global $code_types;
    foreach ($code_types as $value) {
        if (!empty($value['just']) && $value['active']) {
            return true;
        }
    }

    return false;
}

/**
 * Checks is related codes are applicable to any of the code types.
 *
 * @return boolean
 */
function related_codes_are_used()
{
    global $code_types;
    foreach ($code_types as $value) {
        if ($value['rel'] && $value['active']) {
            return true;
        }
    }

    return false;
}

/**
 * Convert a code type id (ct_id) to the key string (ct_key)
 *
 * @param  integer $id
 * @return string
 */
function convert_type_id_to_key($id)
{
    global $code_types;
    foreach ($code_types as $key => $value) {
        if ($value['id'] == $id) {
            return $key;
        }
    }
}

/**
 * Checks to see if code allows justification (ct_just)
 *
 * @param   string   $key
 * @return  boolean
 */
function check_is_code_type_justify($key)
{
    global $code_types;

    if (!empty($code_types[$key]['just'])) {
          return true;
    } else {
        return false;
    }
}

/**
 * Checks if a key string (ct_key) is selected for an element/filter(s)
 *
 * @param   string   $key
 * @param   array    $filter (array of elements that can include 'active','fee','rel','nofs','diag','claim','proc','term','problem')
 * @return  boolean
 */
function check_code_set_filters($key, $filters = array())
{
    global $code_types;

    if (empty($filters)) {
        return false;
    }

    foreach ($filters as $filter) {
        if ($code_types[$key][$filter] != 1) {
            return false;
        }
    }

 // Filter was passed
    return true;
}

/**
 * Return listing of pertinent and active code types.
 *
 * Function will return listing (ct_key) of pertinent
 * active code types, such as diagnosis codes or procedure
 * codes in a chosen format. Supported returned formats include
 * as 1) an array and as 2) a comma-separated lists that has been
 * process by urlencode() in order to place into URL  address safely.
 *
 * @param  string       $category       category of code types('diagnosis', 'procedure', 'clinical_term', 'active' or 'medical_problem')
 * @param  string       $return_format  format or returned code types ('array' or 'csv')
 * @return string/array
 */
function collect_codetypes($category, $return_format = "array")
{
    global $code_types;

    $return = array();

    foreach ($code_types as $ct_key => $ct_arr) {
        if (!$ct_arr['active']) {
            continue;
        }

        if ($category == "diagnosis") {
            if ($ct_arr['diag']) {
                array_push($return, $ct_key);
            }
        } elseif ($category == "procedure") {
            if ($ct_arr['proc']) {
                array_push($return, $ct_key);
            }
        } elseif ($category == "clinical_term") {
            if ($ct_arr['term']) {
                array_push($return, $ct_key);
            }
        } elseif ($category == "active") {
            if ($ct_arr['active']) {
                array_push($return, $ct_key);
            }
        } elseif ($category == "medical_problem") {
            if ($ct_arr['problem']) {
                array_push($return, $ct_key);
            }
        } elseif ($category == "drug") {
            if ($ct_arr['drug']) {
                array_push($return, $ct_key);
            }
        } else {
            //return nothing since no supported category was chosen
        }
    }

    if ($return_format == "csv") {
        //return it as a csv string
        return csv_like_join($return);
    } else { //$return_format == "array"
        //return the array
        return $return;
    }
}

/**
 * Return the code information for a specific code.
 *
 * Function is able to search a variety of code sets. See the code type items in the comments at top
 * of this page for a listing of the code sets supported.
 *
 * @param  string    $form_code_type  code set key
 * @param  string    $code            code
 * @param  boolean   $active          if true, then will only return active entries (not pertinent for PROD code sets)
 * @return recordset                  will contain only one item (row).
 */
function return_code_information($form_code_type, $code, $active = true)
{
    return code_set_search($form_code_type, $code, false, $active, true);
}

/**
* The main code set searching function.
*
* It will work for searching one or numerous code sets simultaneously.
* Note that when searching numerous code sets, you CAN NOT search the PROD
* codes; the PROD codes can only be searched by itself.
*
* @param string/array  $form_code_type   code set key(s) (can either be one key in a string or multiple/one key(s) in an array
* @param string        $search_term      search term
* @param integer       $limit            Number of results to return (NULL means return all)
* @param string        $category         Category of code sets. This WILL OVERRIDE the $form_code_type setting (category options can be found in the collect_codetypes() function above)
* @param boolean       $active           if true, then will only return active entries
* @param array         $modes            Holds the search modes to process along with the order of processing (if NULL, then default behavior is sequential code then description search)
* @param boolean       $count            if true, then will only return the number of entries
* @param integer       $start            Query start limit (for pagination) (Note this setting will override the above $limit parameter)
* @param integer       $number           Query number returned (for pagination) (Note this setting will override the above $limit parameter)
* @param array         $filter_elements  Array that contains elements to filter
* @return recordset/integer              Will contain either a integer(if counting) or the results (recordset)
*/
function main_code_set_search($form_code_type, $search_term, $limit = null, $category = null, $active = true, $modes = null, $count = false, $start = null, $number = null, $filter_elements = array())
{

  // check for a category
    if (!empty($category)) {
        $form_code_type = collect_codetypes($category, "array");
    }

  // do the search
    if (!empty($form_code_type)) {
        if (is_array($form_code_type) && (count($form_code_type) > 1)) {
            // run the multiple code set search
            return multiple_code_set_search($form_code_type, $search_term, $limit, $modes, $count, $active, $start, $number, $filter_elements);
        }

        if (is_array($form_code_type) && (count($form_code_type) == 1)) {
            // prepare the variable (ie. convert the one array item to a string) for the non-multiple code set search
            $form_code_type = $form_code_type[0];
        }

        // run the non-multiple code set search
        return sequential_code_set_search($form_code_type, $search_term, $limit, $modes, $count, $active, $start, $number, $filter_elements);
    }
}

/**
 * Main "internal" code set searching function.
 *
 * Function is able to search a variety of code sets. See the 'external' items in the comments at top
 * of this page for a listing of the code sets supported. Also note that Products (using PROD as code type)
 * is also supported. (This function is not meant to be called directly)
 *
 * @param  string    $form_code_type  code set key (special keywords are PROD) (Note --ALL-- has been deprecated and should be run through the multiple_code_set_search() function instead)
 * @param  string    $search_term     search term
 * @param  boolean   $count           if true, then will only return the number of entries
 * @param  boolean   $active          if true, then will only return active entries (not pertinent for PROD code sets)
 * @param  boolean   $return_only_one if true, then will only return one perfect matching item
 * @param  integer   $start           Query start limit
 * @param  integer   $number          Query number returned
 * @param  array     $filter_elements Array that contains elements to filter
 * @param  integer   $limit           Number of results to return (NULL means return all); note this is ignored if set $start/number
 * @param  array     $mode            'default' mode searches code and description, 'code' mode only searches code, 'description' mode searches description (and separates words); note this is ignored if set $return_only_one to TRUE
 * @param  array     $return_query    This is a mode that will only return the query (everything except for the LIMIT is included) (returned as an array to include the query string and binding array)
 * @return recordset/integer/array
 */
function code_set_search($form_code_type, $search_term = "", $count = false, $active = true, $return_only_one = false, $start = null, $number = null, $filter_elements = array(), $limit = null, $mode = 'default', $return_query = false)
{
    global $code_types, $code_external_tables;

  // Figure out the appropriate limit clause
    $limit_query = limit_query_string($limit, $start, $number, $return_only_one);

  // build the filter_elements sql code
    $query_filter_elements = "";
    if (!empty($filter_elements)) {
        foreach ($filter_elements as $key => $element) {
            $query_filter_elements .= " AND codes." . add_escape_custom($key) . "=" . "'" . add_escape_custom($element)  . "' ";
        }
    }

    if ($form_code_type == 'PROD') { // Search for products/drugs
        if ($count) {
            $query = "SELECT count(dt.drug_id) as count ";
        } else {
            $query = "SELECT dt.drug_id, dt.selector, d.name ";
        }

         $query .= "FROM drug_templates AS dt, drugs AS d WHERE " .
                 "( d.name LIKE ? OR " .
                 "dt.selector LIKE ? ) " .
                 "AND d.drug_id = dt.drug_id " .
                 "ORDER BY d.name, dt.selector, dt.drug_id $limit_query";
        $res = sqlStatement($query, array("%" . $search_term . "%", "%" . $search_term . "%"));
    } else { // Start a codes search
        // We are looking up the external table id here.  An "unset" value gets treated as 0(zero) without this test.  This way we can differentiate between "unset" and explicitly zero.
        $table_id = isset($code_types[$form_code_type]['external']) ? intval(($code_types[$form_code_type]['external'])) : -9999 ;
        if ($table_id >= 0) { // We found a definition for the given code search, so start building the query
        // Place the common columns variable here since all check codes table
            $common_columns = " codes.id, codes.code_type, codes.modifier, codes.units, codes.fee, " .
                            "codes.superbill, codes.related_code, codes.taxrates, codes.cyp_factor, " .
                            "codes.active, codes.reportable, codes.financial_reporting, codes.revenue_code, ";
            $columns = $common_columns . "'" . add_escape_custom($form_code_type) . "' as code_type_name ";

            $active_query = '';
            if ($active) {
                // Only filter for active codes.  Only the active column in the joined table
                // is affected by this parameter.  Any filtering as a result of "active" status
                // in the external table itself is always applied. I am implementing the behavior
                // just as was done prior to the refactor
                // - Kevin Yeh
                // If there is no entry in codes sql table, then default to active
                //  (this is reason for including NULL below)
                if ($table_id == 0) {
                    // Search from default codes table
                    $active_query = " AND codes.active = 1 ";
                } else {
                    // Search from external tables
                    $active_query = " AND (codes.active = 1 || codes.active IS NULL) ";
                }
            }

            // Get/set the basic metadata information
            $table_info = $code_external_tables[$table_id];
            $table = $table_info[EXT_TABLE_NAME];
            $table_dot = $table . ".";
            $code_col = $table_info[EXT_COL_CODE];
            $code_text_col = $table_info[EXT_COL_DESCRIPTION];
            $code_text_short_col = $table_info[EXT_COL_DESCRIPTION_BRIEF];
            if ($table_id == 0) {
                $table_info[EXT_FILTER_CLAUSES] = array("code_type=" . $code_types[$form_code_type]['id']); // Add a filter for the code type
            }

            $code_external = $code_types[$form_code_type]['external'];

            // If the description is supposed to come from "joined" table instead of the "main",
            // the metadata defines a DISPLAY_DESCRIPTION element, and we use that to build up the query
            if ($table_info[DISPLAY_DESCRIPTION] != "") {
                $display_description = $table_info[DISPLAY_DESCRIPTION];
                $display_description_brief = $table_info[DISPLAY_DESCRIPTION];
            } else {
                $display_description = $table_dot . $code_text_col;
                $display_description_brief = $table_dot . $code_text_short_col;
            }

            // Ensure the external table exists
            $check_table = sqlQuery("SHOW TABLES LIKE '" . $table . "'");
            if ((empty($check_table))) {
                HelpfulDie("Missing table in code set search:" . $table);
            }

            $sql_bind_array = array();
            if ($count) {
                // only collecting a count
                $query = "SELECT count(" . $table_dot . $code_col . ") as count ";
            } else {
                $query = "SELECT '" . $code_external . "' as code_external, " .
                         $table_dot . $code_col . " as code, " .
                         $display_description . " as code_text, " .
                         $display_description_brief . " as code_text_short, " .
                         $columns . " ";
            }

            if ($table_id == 0) {
                // Search from default codes table
                $query .= " FROM " . $table . " ";
            } else {
                // Search from external tables
                $query .= " FROM " . $table .
                          " LEFT OUTER JOIN `codes` " .
                          " ON " . $table_dot . $code_col . " = codes.code AND codes.code_type = ? ";
                array_push($sql_bind_array, $code_types[$form_code_type]['id']);
            }

            foreach ($table_info[EXT_JOINS] as $join_info) {
                $join_table = $join_info[JOIN_TABLE];
                $check_table = sqlQuery("SHOW TABLES LIKE '" . $join_table . "'");
                if ((empty($check_table))) {
                    HelpfulDie("Missing join table in code set search:" . $join_table);
                }

                $query .= " INNER JOIN " . $join_table;
                $query .= " ON ";
                $not_first = false;
                foreach ($join_info[JOIN_FIELDS] as $field) {
                    if ($not_first) {
                        $query .= " AND ";
                    }

                    $query .= $field;
                    $not_first = true;
                }
            }

        // Setup the where clause based on MODE
            $query .= " WHERE ";
            if ($return_only_one) {
                $query .= $table_dot . $code_col . " = ? ";
                array_push($sql_bind_array, $search_term);
            } elseif ($mode == "code") {
                $query .= $table_dot . $code_col . " like ? ";
                array_push($sql_bind_array, $search_term . "%");
            } elseif ($mode == "description") {
                $description_keywords = preg_split("/ /", $search_term, -1, PREG_SPLIT_NO_EMPTY);
                $query .= "(1=1 ";
                foreach ($description_keywords as $keyword) {
                    $query .= " AND " . $table_dot . $code_text_col . " LIKE ? ";
                    array_push($sql_bind_array, "%" . $keyword . "%");
                }

                $query .= ")";
            } else { // $mode == "default"
                  $query .= "(" . $table_dot . $code_text_col . " LIKE ? OR " . $table_dot . $code_col . " LIKE ?) ";
                  array_push($sql_bind_array, "%" . $search_term . "%", "%" . $search_term . "%");
            }

        // Done setting up the where clause by mode

          // Add the metadata related filter clauses
            foreach ($table_info[EXT_FILTER_CLAUSES] as $filter_clause) {
                $query .= " AND ";
                $dot_location = strpos($filter_clause, ".");
                if ($dot_location !== false) {
                  // The filter clause already includes a table specifier, so don't add one
                    $query .= $filter_clause;
                } else {
                    $query .= $table_dot . $filter_clause;
                }
            }

            $query .= $active_query . $query_filter_elements;

            $query .= " ORDER BY " . $table_dot . $code_col . "+0," . $table_dot . $code_col;

            if ($return_query) {
                // Just returning the actual query without the LIMIT information in it. This
                // information can then be used to combine queries of different code types
                // via the mysql UNION command. Returning an array to contain the query string
                // and the binding parameters.
                return array('query' => $query,'binds' => $sql_bind_array);
            }

            $query .= $limit_query;

            $res = sqlStatement($query, $sql_bind_array);
        } else {
            HelpfulDie("Code type not active or not defined:" . $join_info[JOIN_TABLE]);
        }
    } // End specific code type search

    if (isset($res)) {
        if ($count) {
            // just return the count
            $ret = sqlFetchArray($res);
            return $ret['count'];
        } else {
            // return the data
            return $res;
        }
    }
}

/**
 * Lookup Code Descriptions for one or more billing codes.
 *
 * Function is able to lookup code descriptions from a variety of code sets. See the 'external'
 * items in the comments at top of this page for a listing of the code sets supported.
 *
 * @param  string $codes  Is of the form "type:code;type:code; etc.".
 * @param  string $desc_detail Can choose either the normal description('code_text') or the brief description('code_text_short').
 * @return string         Is of the form "description;description; etc.".
 */
function lookup_code_descriptions($codes, $desc_detail = "code_text")
{
    global $code_types, $code_external_tables;

  // ensure $desc_detail is set properly
    if (($desc_detail != "code_text") && ($desc_detail != "code_text_short")) {
        $desc_detail = "code_text";
    }

    $code_text = '';
    if (!empty($codes)) {
        $relcodes = explode(';', $codes);
        foreach ($relcodes as $codestring) {
            if ($codestring === '') {
                continue;
            }

            // added $modifier for HCPCS and other internal codesets so can grab exact entry in codes table
            $code_parts = explode(':', $codestring);
            $codetype = $code_parts[0] ?? null;
            $code = $code_parts[1] ?? null;
            $modifier = $code_parts[2] ?? null;
            // if we don't have the code types we can't do much here
            if (!isset($code_types[$codetype])) {
                // we can't do much so we will just continue here...
                continue;
            }

            $table_id = $code_types[$codetype]['external'] ?? '';
            if (!isset($code_external_tables[$table_id])) {
                //using an external code that is not yet supported, so skip.
                continue;
            }

            $table_info = $code_external_tables[$table_id];
            $table_name = $table_info[EXT_TABLE_NAME];
            $code_col = $table_info[EXT_COL_CODE];
            $desc_col = $table_info[DISPLAY_DESCRIPTION] == "" ? $table_info[EXT_COL_DESCRIPTION] : $table_info[DISPLAY_DESCRIPTION];
            $desc_col_short = $table_info[DISPLAY_DESCRIPTION] == "" ? $table_info[EXT_COL_DESCRIPTION_BRIEF] : $table_info[DISPLAY_DESCRIPTION];
            $sqlArray = array();
            $sql = "SELECT " . $desc_col . " as code_text," . $desc_col_short . " as code_text_short FROM " . $table_name;

            // include the "JOINS" so that we get the preferred term instead of the FullySpecifiedName when appropriate.
            foreach ($table_info[EXT_JOINS] as $join_info) {
                $join_table = $join_info[JOIN_TABLE];
                $check_table = sqlQuery("SHOW TABLES LIKE '" . $join_table . "'");
                if ((empty($check_table))) {
                    HelpfulDie("Missing join table in code set search:" . $join_table);
                }

                $sql .= " INNER JOIN " . $join_table;
                $sql .= " ON ";
                $not_first = false;
                foreach ($join_info[JOIN_FIELDS] as $field) {
                    if ($not_first) {
                        $sql .= " AND ";
                    }

                    $sql .= $field;
                    $not_first = true;
                }
            }

            $sql .= " WHERE ";


            // Start building up the WHERE clause

            // When using the external codes table, we have to filter by the code_type.  (All the other tables only contain one type)
            if ($table_id == 0) {
                $sql .= " code_type = '" . add_escape_custom($code_types[$codetype]['id']) . "' AND ";
            }

            // Specify the code in the query.
            $sql .= $table_name . "." . $code_col . "=? ";
            array_push($sqlArray, $code);

            // Add the modifier if necessary for CPT and HCPCS which differentiates code
            if ($modifier) {
                $sql .= " AND modifier = ? ";
                array_push($sqlArray, $modifier);
            }

            // We need to include the filter clauses
            // For SNOMED and SNOMED-CT this ensures that we get the Preferred Term or the Fully Specified Term as appropriate
            // It also prevents returning "inactive" results
            foreach ($table_info[EXT_FILTER_CLAUSES] as $filter_clause) {
                $sql .= " AND " . $filter_clause;
            }

            // END building the WHERE CLAUSE


            if ($table_info[EXT_VERSION_ORDER]) {
                $sql .= " ORDER BY " . $table_info[EXT_VERSION_ORDER];
            }

            $sql .= " LIMIT 1";
            $crow = sqlQuery($sql, $sqlArray);
            if (!empty($crow[$desc_detail])) {
                if ($code_text) {
                    $code_text .= '; ';
                }

                $code_text .= $crow[$desc_detail];
            }
        }
    }

    return $code_text;
}

/**
* Sequential code set "internal" searching function
*
* Function is basically a wrapper of the code_set_search() function to support
* a optimized searching models. The default mode will:
* Searches codes first; then if no hits, it will then search the descriptions
* (which are separated by each word in the code_set_search() function).
* (This function is not meant to be called directly)
*
* @param string $form_code_type code set key (special keyword is PROD) (Note --ALL-- has been deprecated and should be run through the multiple_code_set_search() function instead)
* @param string $search_term search term
* @param integer $limit Number of results to return (NULL means return all)
* @param array $modes Holds the search modes to process along with the order of processing (default behavior is described in above function comment)
* @param boolean $count if true, then will only return the number of entries
* @param boolean $active if true, then will only return active entries
* @param integer $start Query start limit (for pagination)
* @param integer $number Query number returned (for pagination)
* @param array $filter_elements Array that contains elements to filter
* @param string $is_hit_mode This is a mode that simply returns the name of the mode if results were found
* @return recordset/integer/string
*/
function sequential_code_set_search($form_code_type, $search_term, $limit = null, $modes = null, $count = false, $active = true, $start = null, $number = null, $filter_elements = array(), $is_hit_mode = false)
{
  // Set the default behavior that is described in above function comments
    if (empty($modes)) {
        $modes = array('code','description');
    }

  // Return the Search Results (loop through each mode in order)
    foreach ($modes as $mode) {
        $res = code_set_search($form_code_type, $search_term, $count, $active, false, $start, $number, $filter_elements, $limit, $mode);
        if (($count && $res > 0) || (!$count && sqlNumRows($res) > 0)) {
            if ($is_hit_mode) {
                // just return the mode
                return $mode;
            } else {
                // returns the count number if count is true or returns the data if count is false
                return $res;
            }
        }
    }
}

/**
* Code set searching "internal" function for when searching multiple code sets.
*
* It will also work for one code set search, although not meant for this.
* (This function is not meant to be called directly)
*
* @param array $form_code_types code set keys (will default to checking all active code types if blank)
* @param string $search_term search term
* @param integer $limit Number of results to return (NULL means return all)
* @param array $modes Holds the search modes to process along with the order of processing (default behavior is described in above function comment)
* @param boolean $count if true, then will only return the number of entries
* @param boolean $active if true, then will only return active entries
* @param integer $start Query start limit (for pagination)
* @param integer $number Query number returned (for pagination)
* @param array $filter_elements Array that contains elements to filter
* @return recordset/integer
*/
function multiple_code_set_search(array $form_code_types = null, $search_term, $limit = null, $modes = null, $count = false, $active = true, $start = null, $number = null, $filter_elements = array())
{

    if (empty($form_code_types)) {
        // Collect the active code types
        $form_code_types = collect_codetypes("active", "array");
    }

    if ($count) {
        //start the counter
        $counter = 0;
    } else {
        // Figure out the appropriate limit clause
        $limit_query = limit_query_string($limit, $start, $number);

        // Prepare the sql bind array
        $sql_bind_array = array();

        // Start the query string
        $query = "SELECT * FROM ((";
    }

  // Loop through each code type
    $flag_first = true;
    $flag_hit = false; //ensure there is a hit to avoid trying an empty query
    foreach ($form_code_types as $form_code_type) {
        // see if there is a hit
        $mode_hit = null;
        // only use the count method here, since it's much more efficient than doing the actual query
        $mode_hit = sequential_code_set_search($form_code_type, $search_term, null, $modes, true, $active, null, null, $filter_elements, true);
        if ($mode_hit) {
            if ($count) {
                // count the hits
                $count_hits = code_set_search($form_code_type, $search_term, $count, $active, false, null, null, $filter_elements, null, $mode_hit);
                // increment the counter
                $counter += $count_hits;
            } else {
                $flag_hit = true;
                // build the query
                $return_query = code_set_search($form_code_type, $search_term, $count, $active, false, null, null, $filter_elements, null, $mode_hit, true);
                if (!empty($sql_bind_array)) {
                    $sql_bind_array = array_merge($sql_bind_array, $return_query['binds']);
                } else {
                    $sql_bind_array = $return_query['binds'];
                }

                if (!$flag_first) {
                    $query .= ") UNION ALL (";
                }

                $query .= $return_query['query'];
            }

            $flag_first = false;
        }
    }

    if ($count) {
        //return the count
        return $counter;
    } else {
        // Finish the query string
        $query .= ")) as atari $limit_query";

        // Process and return the query (if there was a hit)
        if ($flag_hit) {
            return sqlStatement($query, $sql_bind_array);
        }
    }
}

/**
* Returns the limit to be used in the sql query for code set searches.
*
* @param  integer  $limit            Number of results to return (NULL means return all)
* @param  integer  $start            Query start limit (for pagination)
* @param  integer  $number           Query number returned (for pagination)
* @param  boolean  $return_only_one  if true, then will only return one perfect matching item
* @return recordset/integer
*/
function limit_query_string($limit = null, $start = null, $number = null, $return_only_one = false)
{
    if (!is_null($start) && !is_null($number)) {
        // For pagination of results
        $limit_query = " LIMIT " . escape_limit($start) . ", " . escape_limit($number) . " ";
    } elseif (!is_null($limit)) {
        $limit_query = " LIMIT " . escape_limit($limit) . " ";
    } else {
        // No pagination and no limit
        $limit_query = '';
    }

    if ($return_only_one) {
         // Only return one result (this is where only matching for exact code match)
         // Note this overrides the above limit settings
         $limit_query = " LIMIT 1 ";
    }

    return $limit_query;
}

// Recursive function to look up the IPPF2 (or other type) code, if any,
// for a given related code field.
//
function recursive_related_code($related_code, $typewanted = 'IPPF2', $depth = 0)
{
    global $code_types;
    // echo "<!-- related_code = '$related_code' depth = '$depth' -->\n"; // debugging
    if (++$depth > 4 || empty($related_code)) {
        return false; // protects against relation loops
    }
    $relcodes = explode(';', $related_code);
    foreach ($relcodes as $codestring) {
        if ($codestring === '') {
            continue;
        }
        list($codetype, $code) = explode(':', $codestring);
        if ($codetype === $typewanted) {
            // echo "<!-- returning '$code' -->\n"; // debugging
            return $code;
        }
        $row = sqlQuery(
            "SELECT related_code FROM codes WHERE " .
            "code_type = ? AND code = ? AND active = 1 " .
            "ORDER BY id LIMIT 1",
            array($code_types[$codetype]['id'], $code)
        );
        $tmp = recursive_related_code($row['related_code'], $typewanted, $depth);
        if ($tmp !== false) {
            return $tmp;
        }
    }
    return false;
}
