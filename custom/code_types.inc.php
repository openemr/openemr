<?php
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
//
if ($GLOBALS['ippf_specific']) {
 // IPPF:
  $code_types = array(
    'ICD9'  => array('id' =>  2, 'fee' => 0, 'mod' => 0, 'just' => '', 'rel' => 0, 'nofs' => 0),
    'MA'    => array('id' => 12, 'fee' => 1, 'mod' => 0, 'just' => '', 'rel' => 1, 'nofs' => 0),
    'IPPF'  => array('id' => 11, 'fee' => 0, 'mod' => 0, 'just' => '', 'rel' => 0, 'nofs' => 1),
    'CPT4'  => array('id' =>  1, 'fee' => 0, 'mod' => 0, 'just' => '', 'rel' => 0, 'nofs' => 1),
    'ACCT'  => array('id' => 13, 'fee' => 0, 'mod' => 0, 'just' => '', 'rel' => 0, 'nofs' => 1),
  );
  $default_search_type = 'MA';
}
else if ($GLOBALS['athletic_team']) {
 // UK Sports Medicine:
  $code_types = array(
    'OSICS10' => array('id' =>  9, 'fee' => 0, 'mod' => 4, 'just' => '', 'rel' => 0, 'nofs' => 0),
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
    'ICD9'  => array('id' => 2, 'fee' => 0, 'mod' => 2, 'just' => ''    , 'rel' => 0, 'nofs' => 0),
    'CPT4'  => array('id' => 1, 'fee' => 1, 'mod' => 2, 'just' => 'ICD9', 'rel' => 0, 'nofs' => 0),
    'HCPCS' => array('id' => 3, 'fee' => 1, 'mod' => 2, 'just' => 'ICD9', 'rel' => 0, 'nofs' => 0),
  );
  $default_search_type = 'ICD9';
}

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
      $wheretype = "";
      if (empty($code)) {
        $code = $codetype;
      } else {
        $wheretype = "code_type = '" . $code_types[$codetype]['id'] . "' AND ";
      }
      $crow = sqlQuery("SELECT code_text FROM codes WHERE " .
        "$wheretype code = '$code' ORDER BY id LIMIT 1");
      if (!empty($crow['code_text'])) {
        if ($code_text) $code_text .= '; ';
        $code_text .= $crow['code_text'];
      }
    }
  }
  return $code_text;
}
?>
