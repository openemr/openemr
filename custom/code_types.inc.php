<?php
// Copyright (C) 2006-2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This array provides abstraction of billing code types.  This is desirable
// because different countries or fields of practice use different methods for
// coding diagnoses, procedures and supplies.  Fees will not be relevant where
// medical care is socialized.  Attribues are:
//
// id   - the numeric identifier of this code type in the codes table
// fee  - 1 if fees are used, else 0
// mod  - the maximum length of a modifier, 0 if modifiers are not used
// just - the code type used for justification, empty if none
// rel  - 1 if other billing codes may be "related" to this code type
// nofs - 1 if this code type should NOT appear in the Fee Sheet
// diag - 1 if this code type is for diagnosis
// active - 1 if this code type is activated
// label - label used for code type
// external - 0 for storing codes in the code table
//            1 for storing codes in external ICD10 tables
//            2 for storing codes in external SNOMED (RF1) tables
//            3 for storing codes in external SNOMED (RF2) tables
//
/*********************************************************************
if ($GLOBALS['ippf_specific']) {
 // IPPF:
  $code_types = array(
    'ICD9'  => array('id' =>  2, 'fee' => 0, 'mod' => 0, 'just' => '', 'rel' => 0, 'nofs' => 0, 'diag' => TRUE),
    'MA'    => array('id' => 12, 'fee' => 1, 'mod' => 0, 'just' => '', 'rel' => 1, 'nofs' => 0),
    'IPPF'  => array('id' => 11, 'fee' => 0, 'mod' => 0, 'just' => '', 'rel' => 0, 'nofs' => 1),
    'ACCT'  => array('id' => 13, 'fee' => 0, 'mod' => 0, 'just' => '', 'rel' => 0, 'nofs' => 1),
  );
  $default_search_type = 'MA';
}
else if ($GLOBALS['athletic_team']) {
 // UK Sports Medicine:
  $code_types = array(
    'OSICS10' => array('id' =>  9, 'fee' => 0, 'mod' => 4, 'just' => '', 'rel' => 0, 'nofs' => 0, 'diag' => TRUE),
    'OPCS'    => array('id' =>  6, 'fee' => 0, 'mod' => 0, 'just' => '', 'rel' => 0, 'nofs' => 0),
    'PTCJ'    => array('id' =>  7, 'fee' => 0, 'mod' => 0, 'just' => '', 'rel' => 0, 'nofs' => 0),
    'CPT4'    => array('id' =>  1, 'fee' => 0, 'mod' => 0, 'just' => '', 'rel' => 0, 'nofs' => 0),
    'SMPC'    => array('id' => 10, 'fee' => 0, 'mod' => 0, 'just' => '', 'rel' => 0, 'nofs' => 0),
  );
  $default_search_type = 'OSICS10';
}
else {
 // USA Clinics:
  $code_types = array(
    'ICD9'  => array('id' => 2, 'fee' => 0, 'mod' => 2, 'just' => ''    , 'rel' => 0, 'nofs' => 0, 'diag' => TRUE),
    'CPT4'  => array('id' => 1, 'fee' => 1, 'mod' => 2, 'just' => 'ICD9', 'rel' => 0, 'nofs' => 0),
    'HCPCS' => array('id' => 3, 'fee' => 1, 'mod' => 2, 'just' => 'ICD9', 'rel' => 0, 'nofs' => 0),
  );
  $default_search_type = 'ICD9';
}
*********************************************************************/

// Code types are now stored in the database.
//
$code_types = array();
$default_search_type = '';
$ctres = sqlStatement("SELECT * FROM code_types WHERE ct_active=1 ORDER BY ct_seq, ct_key");
while ($ctrow = sqlFetchArray($ctres)) {
  $code_types[$ctrow['ct_key']] = array(
    'id'   => $ctrow['ct_id'  ],
    'fee'  => $ctrow['ct_fee' ],
    'mod'  => $ctrow['ct_mod' ],
    'just' => $ctrow['ct_just'],
    'rel'  => $ctrow['ct_rel' ],
    'nofs' => $ctrow['ct_nofs'],
    'diag' => $ctrow['ct_diag'],
    'mask' => $ctrow['ct_mask'],
    'label'=> ( (empty($ctrow['ct_label'])) ? $ctrow['ct_key'] : $ctrow['ct_label'] ),
    'external'=> $ctrow['ct_external']
  );
  if ($default_search_type === '') $default_search_type = $ctrow['ct_key'];
}

/********************************************************************/

function fees_are_used() {
 global $code_types;
 foreach ($code_types as $value) { if ($value['fee']) return true; }
 return false;
}

function modifiers_are_used($fee_sheet=false) {
 global $code_types;
 foreach ($code_types as $value) {
  if ($fee_sheet && !empty($value['nofs'])) continue;
  if ($value['mod']) return true;
 }
 return false;
}

function related_codes_are_used() {
 global $code_types;
 foreach ($code_types as $value) { if ($value['rel']) return true; }
 return false;
}

// Convert a code type id to a key
function convert_type_id_to_key($id) {
 global $code_types;
 foreach ($code_types as $key => $value) {
  if ($value['id'] == $id) return $key;
 } 
}

// Main code set searching function
// $form_code_type - code set key (special keywords are PROD and --ALL--)
// $search_term - search term
// $count - if true, then will only return the number of entries
// $active - if true, then will only return active entries (not pertinent for PROD or external code sets)
// $start - Query start limit
// $end - Query end limit
function code_set_search($form_code_type,$search_term="",$count=false,$active=true,$start=NULL,$number=NULL) {
  global $code_types;

  $active_query = '';
  if ($active) {
   // Only filter for active codes
   // Note this is not use in PROD or in the external code sets
   $active_query=" AND active = 1 ";
  }

  $limit_query = '';
  if ( !is_null($start) && !is_null($number) ) {
    $limit_query = " LIMIT $start, $number ";
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
  else if ($form_code_type == '--ALL--') { // Search all codes from the default codes table
   // Note this will not search the external code sets
   $query = "SELECT * FROM codes " .
            "WHERE (code_text LIKE ? OR " .
            "code LIKE ?) " .
            " $active_query " .
            "ORDER BY code_type,code+0,code $limit_query";
   $res = sqlStatement($query, array("%".$search_term."%", "%".$search_term."%") );
  }
  else if ( !($code_types[$form_code_type]['external']) ) { // Search from default codes table
   $query = "SELECT * FROM codes " .
            "WHERE (code_text LIKE ? OR " .
            "code LIKE ?) " .
            "AND code_type = ? $active_query " .
            "ORDER BY code+0,code $limit_query";
   $res = sqlStatement($query, array("%".$search_term."%", "%".$search_term."%", $code_types[$form_code_type]['id']) );
  }
  else if ($code_types[$form_code_type]['external'] == 1 ) { // Search from ICD10 codeset tables
   //placeholder
  }
  else if ($code_types[$form_code_type]['external'] == 2 ) { // Search from SNOMED (RF1) codeset tables
   // Ensure the sct_concepts sql table exists
   $check_table = sqlQuery("SHOW TABLES LIKE 'sct_concepts'");
   if ( !(empty($check_table)) ) {
    $query = "SELECT `ConceptId` as code, `FullySpecifiedName` as code_text FROM `sct_concepts` " .
             "WHERE ( `FullySpecifiedName` LIKE ? OR (`ConceptId` LIKE ? AND `FullySpecifiedName` LIKE '%(disorder)') ) " .
             "AND `ConceptStatus` = 0 " .
             "ORDER BY `ConceptId` $limit_query";
    $res = sqlStatement($query, array("%".$search_term."%(disorder)", "%".$search_term."%") );
   }
  }
  else if ($code_types[$form_code_type]['external'] == 3 ) { // Search from SNOMED (RF2) codeset tables
   //placeholder
  }
  else {
   //using an external code that is not yet supported, so skip.
  }
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

// Look up descriptions for one or more billing codes.  Input is of the
// form "type:code;type:code; etc.".
//
function lookup_code_descriptions($codes) {
  global $code_types;
  $code_text = '';
  if (!empty($codes)) {
    $relcodes = explode(';', $codes);
    foreach ($relcodes as $codestring) {
      if ($codestring === '') continue;
      list($codetype, $code) = explode(':', $codestring);
      if ( !($code_types[$codetype]['external']) ) { // Collect from default codes table
        $wheretype = "";
        $sqlArray = array();
        if (empty($code)) {
          $code = $codetype;
        } else {
          $wheretype = "code_type = ? AND ";
          array_push($sqlArray,$code_types[$codetype]['id']);
        }
        $sql = "SELECT code_text FROM codes WHERE " .
          "$wheretype code = ? ORDER BY id LIMIT 1";
        array_push($sqlArray,$code);
        $crow = sqlQuery($sql,$sqlArray);
        if (!empty($crow['code_text'])) {
          if ($code_text) $code_text .= '; ';
          $code_text .= $crow['code_text'];
        }
      }
      else if ($code_types[$codetype]['external'] == 1) { // Collect from ICD10 codeset tables
        //placeholder
      }
      else if ($code_types[$codetype]['external'] == 2) { // Collect from SNOMED (RF1) codeset tables
        // Ensure the sct_concepts sql table exists
        $check_table = sqlQuery("SHOW TABLES LIKE 'sct_concepts'");
        if ( !(empty($check_table)) ) {
          if ( !(empty($code)) ) {
            $sql = "SELECT `FullySpecifiedName` FROM `sct_concepts` " .
                   "WHERE `ConceptId` = ? AND `ConceptStatus` = 0 LIMIT 1";
            $crow = sqlQuery($sql, array($code) );
            if (!empty($crow['FullySpecifiedName'])) {
              if ($code_text) $code_text .= '; ';
              $code_text .= $crow['FullySpecifiedName'];
            }
          }
        }
      }
      else if ($code_types[$codetype]['external'] == 3) { // Collect from SNOMED (RF2) codeset tables
        //placeholder
      }
      else {
        //using an external code that is not yet supported, so skip. 
      }
    }
  }
  return $code_text;
}
?>
