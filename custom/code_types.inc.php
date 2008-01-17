<?
// This array provides abstraction of billing code types.  This is desirable
// because different countries or fields of practice use different methods for
// coding diagnoses, procedures and supplies.  Fees will not be relevant where
// medical care is socialized.  Attribues are:
//
// id   - the numeric identifier of this code type in the codes table
// fee  - 1 if fees are used, else 0
// mod  - the maximum length of a modifier, 0 if modifiers are not used
// just - the code type used for justification, empty if none
// rel  - a "related" code type, empty if none (for mapping to another coding system)
//
$code_types = array(

 // USA Clinics:
 'ICD9'  => array('id' => 2, 'fee' => 0, 'mod' => 2, 'just' => ''    , 'rel' => ''),
 'CPT4'  => array('id' => 1, 'fee' => 1, 'mod' => 2, 'just' => 'ICD9', 'rel' => ''),
 'HCPCS' => array('id' => 3, 'fee' => 1, 'mod' => 2, 'just' => 'ICD9', 'rel' => '')

 /* UK Sports Medicine:
 'OSICS10' => array('id' =>  9, 'fee' => 0, 'mod' => 4, 'just' => '', 'rel' => ''),
 'OPCS'    => array('id' =>  6, 'fee' => 0, 'mod' => 0, 'just' => '', 'rel' => ''),
 'PTCJ'    => array('id' =>  7, 'fee' => 0, 'mod' => 0, 'just' => '', 'rel' => ''),
 'CPT4'    => array('id' =>  1, 'fee' => 0, 'mod' => 0, 'just' => '', 'rel' => ''),
 'SMPC'    => array('id' => 10, 'fee' => 0, 'mod' => 0, 'just' => '', 'rel' => '') */
);

$default_search_type = 'ICD9'; // US
// $default_search_type = 'OSICS10'; // UK Sports

function fees_are_used() {
 global $code_types;
 foreach ($code_types as $value) { if ($value['fee']) return true; }
 return false;
}

function modifiers_are_used() {
 global $code_types;
 foreach ($code_types as $value) { if ($value['mod']) return true; }
 return false;
}

?>
