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
 * *  term     - 1 if this code type is used as a clinical term
 *  </pre>
 *
 * Copyright (C) 2006-2010 Rod Roark <rod@sunsetsystems.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Brady Miller <brady@sparmy.com>
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @link    http://www.open-emr.org
 */

require_once(dirname(__FILE__)."/../library/csv_like_join.php");

$code_types = array();
$default_search_type = '';
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
    'label'=> ( (empty($ctrow['ct_label'])) ? $ctrow['ct_key'] : $ctrow['ct_label'] ),
    'external'=> $ctrow['ct_external'],
    'claim' => $ctrow['ct_claim'],
    'proc' => $ctrow['ct_proc'],
    'term' => $ctrow['ct_term']
  );
  if ($default_search_type === '') $default_search_type = $ctrow['ct_key'];
}

/** This array contains metadata describing the arrangement of the external data
 *  tables for storing codes.
 */
$code_external_tables=array();
define('EXT_COL_CODE','code');
define('EXT_COL_DESCRIPTION','description');
define('EXT_TABLE_NAME','table');
define('EXT_FILTER_CLAUSES','filter_clause');
define('EXT_VERSION_ORDER','filter_version_order');
define('EXT_JOINS','joins');
define('JOIN_TABLE','join');
define('JOIN_FIELDS','fields');
define('DISPLAY_DESCRIPTION',"display_description");

/**
 * This is a helper function for defining the metadata that describes the tables
 * 
 * @param type $results             A reference to the global array which stores all the metadata
 * @param type $index               The external table ID.  This corresponds to the value in the code_types table in the ct_external column
 * @param type $table_name          The name of the table which stores the code informattion (e.g. icd9_dx_code
 * @param type $col_code            The name of the column which is the code
 * @param type $col_description     The name of the column which is the description
 * @param type $filter_clauses      An array of clauses to be included in the search "WHERE" clause that limits results
 * @param type $version_order       How to choose between different revisions of codes
 * @param type $joins               An array which describes additional tables to join as part of a code search.  
 */
function define_external_table(&$results, $index, $table_name,$col_code, $col_description,$filter_clauses=array(),$version_order="",$joins=array(),$display_desc="")
{
    $results[$index]=array(EXT_TABLE_NAME=>$table_name,
                           EXT_COL_CODE=>$col_code,
                           EXT_COL_DESCRIPTION=>$col_description,
                           EXT_FILTER_CLAUSES=>$filter_clauses,
                           EXT_JOINS=>$joins,
                           EXT_VERSION_ORDER=>$version_order,
                           DISPLAY_DESCRIPTION=>$display_desc
                           );
}
// In order to treat all the code types the same for lookup_code_descriptions, we include metadata for the original codes table
define_external_table($code_external_tables,0,'codes','code','code_text',array(),'id');

// ICD9 External Definitions
define_external_table($code_external_tables,4,'icd9_dx_code','formatted_dx_code','long_desc',array("active='1'"),'revision DESC');
define_external_table($code_external_tables,5,'icd9_sg_code','formatted_sg_code','long_desc',array("active='1'"),'revision DESC');
//**** End ICD9 External Definitions

// SNOMED Definitions
// For generic SNOMED-CT, there is no need to join with the descriptions table to get a specific description Type

// For generic concepts, use the fully specified description (DescriptionType=3) so we can tell the difference between them.
define_external_table($code_external_tables,7,'sct_descriptions','ConceptId','Term',array("DescriptionStatus=0","DescriptionType=3"),"");


// To determine codes, we need to evaluate data in both the sct_descriptions table, and the sct_concepts table.
// the base join with sct_concepts is the same for all types of SNOMED definitions, so we define the common part here
$SNOMED_joins=array(JOIN_TABLE=>"sct_concepts",JOIN_FIELDS=>array("sct_descriptions.ConceptId=sct_concepts.ConceptId"));

// For disorders, use the preferred term (DescriptionType=1)
define_external_table($code_external_tables,2,'sct_descriptions','ConceptId','Term',array("DescriptionStatus=0","DescriptionType=1"),"",array($SNOMED_joins));
// Add the filter to choose only disorders. This filter happens as part of the join with the sct_concepts table
array_push($code_external_tables[2][EXT_JOINS][0][JOIN_FIELDS],"FullySpecifiedName like '%(disorder)'");

// SNOMED-PR definition
define_external_table($code_external_tables,9,'sct_descriptions','ConceptId','Term',array("DescriptionStatus=0","DescriptionType=1"),"",array($SNOMED_joins));
// Add the filter to choose only procedures. This filter happens as part of the join with the sct_concepts table
array_push($code_external_tables[9][EXT_JOINS][0][JOIN_FIELDS],"FullySpecifiedName like '%(procedure)'");


//**** End SNOMED Definitions

// ICD 10 Definitions
define_external_table($code_external_tables,1,'icd10_dx_order_code','formatted_dx_code','long_desc',array("active='1'","valid_for_coding = '1'"),'revision DESC');
define_external_table($code_external_tables,6,'icd10_pcs_order_code','pcs_code','long_desc',array("active='1'","valid_for_coding = '1'"),'revision DESC');
//**** End ICD 10 Definitions

/**
 * This array stores the external table options. See above for $code_types array
 * 'external' attribute  for explanation of the option listings.
 * @var array
 */
$cd_external_options = array(
  '0' => xl('No'),
  '4' => xl('ICD9 Diagnosis'),
  '5' => xl('ICD9 Procedure/Service'),
  '1' => xl('ICD10 Diagnosis'),
  '6' => xl('ICD10 Procedure/Service'),
  '2' => xl('SNOMED (RF1) Diagnosis'),
  '3' => xl('SNOMED (RF2) Diagnosis'),
  '7' => xl('SNOMED (RF1) Clinical Term'),
  '9' => xl('SNOMED (RF1) Procedure')    
);

/**
 * Checks is fee are applicable to any of the code types.
 *
 * @return boolean
 */
function fees_are_used() {
 global $code_types;
 foreach ($code_types as $value) { if ($value['fee'] && $value['active']) return true; }
 return false;
}

/**
 * Checks is modifiers are applicable to any of the code types.
 * (If a code type is not set to show in the fee sheet, then is ignored)
 *
 * @param  boolean $fee_sheet Will ignore code types that are not shown in the fee sheet
 * @return boolean
 */
function modifiers_are_used($fee_sheet=false) {
 global $code_types;
 foreach ($code_types as $value) {
  if ($fee_sheet && !empty($value['nofs'])) continue;
  if ($value['mod'] && $value['active']) return true;
 }
 return false;
}

/**
 * Checks if justifiers are applicable to any of the code types.
 *
 * @return boolean
 */
function justifiers_are_used() {
 global $code_types;
 foreach ($code_types as $value) { if (!empty($value['just']) && $value['active']) return true; }
 return false;
}

/**
 * Checks is related codes are applicable to any of the code types.
 *
 * @return boolean
 */
function related_codes_are_used() {
 global $code_types;
 foreach ($code_types as $value) { if ($value['rel'] && $value['active']) return true; }
 return false;
}

/**
 * Convert a code type id (ct_id) to the key string (ct_key)
 *
 * @param  integer $id
 * @return string
 */
function convert_type_id_to_key($id) {
 global $code_types;
 foreach ($code_types as $key => $value) {
  if ($value['id'] == $id) return $key;
 } 
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
 * @param  string       $category       category of code types('diagnosis', 'procedure' or 'clinical_term')
 * @param  string       $return_format  format or returned code types ('array' or 'csv')
 * @return string/array
 */
function collect_codetypes($category,$return_format="array") {
 global $code_types;

 $return = array();

 foreach ($code_types as $ct_key => $ct_arr) {
  if (!$ct_arr['active']) continue;

  if ($category == "diagnosis") {
   if ($ct_arr['diag']) {
    array_push($return,$ct_key);
   }
  }
  else if ($category == "procedure") {
   if ($ct_arr['proc']) {
    array_push($return,$ct_key);
   }
  }
  else if ($category == "clinical_term") {
   if ($ct_arr['term']) {
    array_push($return,$ct_key);
   }
  }
  else {
   //return nothing since no supported category was chosen
  }
 }

 if ($return_format == "csv") {
  //return it as a csv string
  return csv_like_join($return);
 }
 else { //$return_format == "array"
  //return the array
  return $return;
 }
}

/**
 * Main code set searching function.
 *
 * Function is able to search a variety of code sets. See the 'external' items in the comments at top
 * of this page for a listing of the code sets supported. Also note that Products (using PROD as code type)
 * is also supported.
 *
 * @param  string    $form_code_type  code set key (special keywords are PROD and --ALL--)
 * @param  string    $search_term     search term
 * @param  boolean   $count           if true, then will only return the number of entries
 * @param  boolean   $active          if true, then will only return active entries (not pertinent for PROD code sets)
 * @param  boolean   $return_only_one if true, then will only return one perfect matching item
 * @param  integer   $start           Query start limit
 * @param  integer   $number          Query number returned
 * @param  array     $filter_elements Array that contains elements to filter
 * @param array      $limit           Number of results to return (NULL means return all); note this is ignored if set $start/number
 * @param array      $mode            'default' mode searches code and description, 'code' mode only searches code, 'description' mode searches description (and separates words); note this is ignored if set $return_only_one to TRUE
 * @return recordset 
 */
function code_set_search($form_code_type,$search_term="",$count=false,$active=true,$return_only_one=false,$start=NULL,$number=NULL,$filter_elements=array(),$limit=NULL,$mode='default') {
  global $code_types,$code_external_tables;

  // Figure out the appropriate limit clause
  if ( !is_null($start) && !is_null($number) ) {
    // For pagination of results
    $limit_query = " LIMIT $start, $number ";
  }
  else if (!is_null($limit)) {
    $limit_query = " LIMIT $limit ";
  }
  else {
    // No pagination and no limit
    $limit_query = '';
  }
  
  if ($return_only_one) {
     // Only return one result (this is where only matching for exact code match)
     // Note this overrides the above limit settings
     $limit_query = " LIMIT 1 ";
  }
  // End determining limit clause
  
  // build the filter_elements sql code
  $query_filter_elements="";
  if (!empty($filter_elements)) {
   foreach ($filter_elements as $key => $element) {
    $query_filter_elements .= " AND c." . add_escape_custom($key) . "=" . "'" . add_escape_custom($element)  . "' ";
   }
  }

  if ($form_code_type == 'PROD') { // Search for products/drugs
   $query = "SELECT dt.drug_id, dt.selector, d.name " .
            "FROM drug_templates AS dt, drugs AS d WHERE " .
            "( d.name LIKE ? OR " .
            "dt.selector LIKE ? ) " .
            "AND d.drug_id = dt.drug_id " .
            "ORDER BY d.name, dt.selector, dt.drug_id $limit_query";
   $res = sqlStatement($query, array("%".$search_term."%", "%".$search_term."%") );
  }
  else { // Start a codes search
      // We are looking up the external table id here.  An "unset" value gets treated as 0(zero) without this test.  This way we can differentiate between "unset" and explicitly zero.
      $table_id=isset($code_types[$form_code_type]['external']) ? intval(($code_types[$form_code_type]['external'])) : -9999 ;  
      if(($table_id>=0) || ($form_code_type == '--ALL--')) // Either we found a definition for the given code search or we are doing an "--ALL--" search, so start building the query
      {
        if ( $table_id==0  || ($form_code_type == '--ALL--') ) { // Search from default codes table.  --ALL-- only means all codes in the default tables
          if($table_id==0){ $table_info[EXT_FILTER_CLAUSES]=array("code_type=".$code_types[$form_code_type]['id']); } // Add a filter for the code type
          else {$table_info[EXT_FILTER_CLAUSES]=array();} // define empty filter array for "--ALL--"
          $table_dot="c.";              // $table_dot is used to prevent awkward looking concatenations when referring to columns in 
          $code_col="code";
          $code_text_col="code_text";

          $active_query = '';
             if ($active) {
              // Only filter for active codes
          $active_query=" AND c.active = 1 ";
          }
          $sql_bind_array = array();
          $query = "SELECT c.id, c.code_text, c.code_text_short, c.code, c.code_type, c.modifier, c.units, c.fee, " .
                  "c.superbill, c.related_code, c.taxrates, c.cyp_factor, c.active, c.reportable, c.financial_reporting, ";
          if($table_id==0)
          {
              // If code type is specified, then include the "constant" as part of the query results for consistency
              $query .= "'" . add_escape_custom($form_code_type) . "' as code_type_name " .
                        "FROM `codes` as c ";

          }
          else if($form_code_type=='--ALL--')
          {
             // For an "--ALL--" search we need to join with the code_types table to get the string representation of each returned code.
             $query .=  "ct.ct_key as code_type_name " .
                        " FROM `codes` as c " .
                        " LEFT OUTER JOIN `code_types` as ct " .
                        " ON c.code_type = ct.ct_id ";
          }
        }
        else if ($code_types[$form_code_type]['external'] > 0 ) { // Search from an external table with defined metadata
            $common_columns=",c.id, c.code_type, c.modifier, c.units, c.fee, " .
                     "c.superbill, c.related_code, c.taxrates, c.cyp_factor, c.active, c.reportable, c.financial_reporting, " .
                     "'" . add_escape_custom($form_code_type) . "' as code_type_name " .

            $active_query = '';
            if ($active) {
                // Only filter for active codes.  Only the active column in the joined table
                // is affected by this parameter.  Any filtering as a result of "active" status
                // in the external table itself is always applied. I am implementing the behavior
                // just as was done prior to the refactor
                // - Kevin Yeh
                // If there is no entry in codes sql table, then default to active
                //  (this is reason for including NULL below)
                $active_query=" AND (c.active = 1 || c.active IS NULL) ";
            }
      
            $table_info=$code_external_tables[$table_id];
            $table=$table_info[EXT_TABLE_NAME];
            $table_dot=$table.".";
            $code_col=$table_info[EXT_COL_CODE];
            $code_text_col=$table_info[EXT_COL_DESCRIPTION];
            if($table_info[DISPLAY_DESCRIPTION]!="")
            {
                $display_description=$table_info[DISPLAY_DESCRIPTION];
            }
            else
            {
                $display_description=$table_dot.$code_text_col;
            }
            // Ensure the external table exists
            $check_table = sqlQuery("SHOW TABLES LIKE '".$table."'");
            if ( (empty($check_table)) ) {HelpfulDie("Missing table in code set search:".$table);}
            
            $sql_bind_array = array();
            $query = "SELECT ".$table_dot.$code_col . " as code, " . 
                     $display_description . " as code_text " .        
                     $common_columns .             
             " FROM ".$table.
             " LEFT OUTER JOIN `codes` as c " .
             " ON ".$table_dot.$code_col." = c.code AND c.code_type = ? ";
            array_push($sql_bind_array,$code_types[$form_code_type]['id']);

            foreach($table_info[EXT_JOINS] as $join_info)
            {
              $join_table=$join_info[JOIN_TABLE];
              $check_table = sqlQuery("SHOW TABLES LIKE '".$join_table."'");
              if ( (empty($check_table)) ) {HelpfulDie("Missing join table in code set search:".$join_table);}
              $query.=" INNER JOIN ". $join_table;
              $query.=" ON ";
              $not_first=false;
              foreach($join_info[JOIN_FIELDS] as $field)
              {
                  if($not_first)
                  {
                      $query.=" AND ";
                  }
                  $query.=$field;
                  $not_first=true;                
              }
            }
      } // End of block for handling external_id>0
      
      // Setup the where clause based on MODE
      $query.= " WHERE ";
      if ($return_only_one) {
          $query .= $table_dot.$code_col." = ? ";
          array_push($sql_bind_array,$search_term);
      }
      else if($mode=="code") {
          $query.= $table_dot.$code_col." like ? ";
          array_push($sql_bind_array,$search_term."%");
      }
      else if($mode=="description"){
          $description_keywords=preg_split("/ /",$search_term,-1,PREG_SPLIT_NO_EMPTY);
          $not_first=false;
          $query.="(";
          foreach($description_keywords as $keyword)
          {
              if($not_first) { $query.= " AND "; }
              $query.= $table_dot.$code_text_col." LIKE ? ";
              array_push($sql_bind_array,"%".$keyword."%");          
              $not_first=true;
          }
          $query.=")";
        }
      else { // $mode == "default"
        $query .= "(".$table_dot.$code_text_col. " LIKE ? OR ".$table_dot.$code_col. " LIKE ?) ";
        array_push($sql_bind_array,"%".$search_term."%","%".$search_term."%");
      }
      // Done setting up the where clause by mode
      
        // Add the metadata related filter clauses
        foreach($table_info[EXT_FILTER_CLAUSES] as $filter_clause)
        {
            $query .= " AND ".$table_dot.$filter_clause;
        }
        
        $query .=$active_query . $query_filter_elements;

        $query .= " ORDER BY ".$table_dot.$code_col."+0,".$table_dot.$code_col; 
        $query .= $limit_query;
        
        $res = sqlStatement($query,$sql_bind_array);         
      }   
    else
    {
        HelpfulDie("Code type not active or not defined:".$join_info[JOIN_TABLE]);
    }
  } // End specific code type search

  if (isset($res)) {
   if ($count) {
    // just return the count
    return sqlNumRows($res);
   }
   else {
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
 * @return string         Is of the form "description;description; etc.".
 */
function lookup_code_descriptions($codes) {
  global $code_types, $code_external_tables;
  $code_text = '';
  if (!empty($codes)) {
    $relcodes = explode(';', $codes);
    foreach ($relcodes as $codestring) {
      if ($codestring === '') continue;
      list($codetype, $code) = explode(':', $codestring);
      $table_id=$code_types[$codetype]['external'];
      if(isset($code_external_tables[$table_id]))
      {
        $table_info=$code_external_tables[$table_id];
        $table_name=$table_info[EXT_TABLE_NAME];
        $code_col=$table_info[EXT_COL_CODE];
        $desc_col= $table_info[DISPLAY_DESCRIPTION]=="" ? $table_info[EXT_COL_DESCRIPTION] : $table_info[DISPLAY_DESCRIPTION];
        $sqlArray = array();
        $sql = "SELECT ".$desc_col." as code_text FROM ".$table_name;
        
        // include the "JOINS" so that we get the preferred term instead of the FullySpecifiedName when appropriate.
        foreach($table_info[EXT_JOINS] as $join_info)
        {
          $join_table=$join_info[JOIN_TABLE];
          $check_table = sqlQuery("SHOW TABLES LIKE '".$join_table."'");
          if ( (empty($check_table)) ) {HelpfulDie("Missing join table in code set search:".$join_table);}
          $sql.=" INNER JOIN ". $join_table;
          $sql.=" ON ";
          $not_first=false;
          foreach($join_info[JOIN_FIELDS] as $field)
          {
              if($not_first)
              {
                  $sql.=" AND ";
              }
              $sql.=$field;
              $not_first=true;                
          }
        }
                
        $sql.=" WHERE ";

        
        // Start building up the WHERE clause

        // When using the external codes table, we have to filter by the code_type.  (All the other tables only contain one type)  
        if ($table_id==0) { $sql .= " code_type = '".add_escape_custom($code_types[$codetype]['id'])."' AND ";   }      

        // Specify the code in the query.
        $sql .= $table_name.".".$code_col."=? ";
        array_push($sqlArray,$code);
       
        // We need to include the filter clauses 
        // For SNOMED and SNOMED-CT this ensures that we get the Preferred Term or the Fully Specified Term as appropriate
        // It also prevents returning "inactive" results
        foreach($table_info[EXT_FILTER_CLAUSES] as $filter_clause)
        {
            $sql.= " AND ".$filter_clause;
        }
        // END building the WHERE CLAUSE
        
        
        if($table_info[EXT_VERSION_ORDER]){$sql .= " ORDER BY ".$table_info[EXT_VERSION_ORDER];}

        $sql .= " LIMIT 1";
        $crow = sqlQuery($sql,$sqlArray);
        if (!empty($crow["code_text"])) {
          if ($code_text) $code_text .= '; ';
          $code_text .= $crow["code_text"];
        }
      }

      else {
        //using an external code that is not yet supported, so skip. 
      }
    }
  }
  return $code_text;
}

/**
* Sequential code set searching function (algorithm contributed by yehster)
*
* Function is basically a wrapper of the code_set_search() function to support
* an optimized searching model. Model searches codes first; then if no hits, it
* will then search the descriptions (which are separated by each word in the
* code_set_search() function).
*
* @param string $form_code_type code set key (special keywords are PROD and --ALL--)
* @param string $search_term search term
* @param array $limit Number of results to return (NULL means return all)
* @param boolean $count if true, then will only return the number of entries
* @param boolean $active if true, then will only return active entries
* @param integer $start Query start limit (for pagination)
* @param integer $number Query number returned (for pagination)
* @param array $filter_elements Array that contains elements to filter
* @return recordset
*/
function sequential_code_set_search($form_code_type,$search_term,$limit=NULL,$count=false,$active=true,$start=NULL,$number=NULL,$filter_elements=array()) {

  // Return the Search Results
  // Search the code first
  $res_code = code_set_search($form_code_type,$search_term,false,$active,false,$start,$number,$filter_elements,$limit,'code');
  if(sqlNumRows($res_code)>0) {
   if ($count) {
    // just return the count
    return sqlNumRows($res_code);
   }
   else {
    // return the data
    return $res_code;
   }
  }
  else {
   // no codes found, so search the descriptions;
   $res_desc = code_set_search($form_code_type,$search_term,false,$active,false,$start,$number,$filter_elements,$limit,'description');
   if(sqlNumRows($res_desc)>0) {
    if ($count) {
     // just return the count
     return sqlNumRows($res_desc);
    }
    else {
     // return the data
     return $res_desc;
    }
   }
  }
}
?>
