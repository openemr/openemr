<?php
include_once(dirname(__FILE__) . '/sql.inc'); // fixes vulnerability with register_globals
require_once(dirname(__FILE__) . '/formdata.inc.php');

function getSessionLanguage() {
  if (!empty($_SESSION['language_choice'])) {
    return $_SESSION['language_choice'];
  }
  else {
    return 1; // English
  }
}

function shouldSkipTranslation() {
  $session_language = getSessionLanguage();
  if ($session_language == 1 && !empty($GLOBALS['skip_english_translation'])) {
    return true;
  }
  return false;
}

function cleanTranslatedPhrase($phrase) {
  // TODO: Not sure why we have to do this, can't you just keep
  // the data in the DB clean?

  $patterns = array ('/\n/','/\r/','/"/',"/'/");
  $replace = array (' ','','`','`');
  $cleaned_phrase = preg_replace($patterns, $replace, $phrase);
  return $cleaned_phrase;
}

function cleanConstant($constant) {
  // TODO: Again, why?
  $patterns = array ('/\n/','/\r/');
  $replace = array (' ','');
  $constant = preg_replace($patterns, $replace, $constant);
  return $constant;
}

function precacheTranslation() {
  $translation_cache = array();
  $session_language = getSessionLanguage();
  $lang_cache_query = "SELECT * FROM lang_definitions JOIN lang_constants " .
    "ON lang_definitions.cons_id = lang_constants.cons_id WHERE " .
    "lang_id = ?";
  $lang_cache_result = sqlStatementNoLog($lang_cache_query, [$session_language]);

  while($translation_row = SqlFetchArray($lang_cache_result)) {
    $translated_phrase = $translation_row["definition"];
    if($translated_phrase == "") {
      continue;
    }
    $translated_phrase = cleanTranslatedPhrase($translatedPhrase);
    $constant_name = cleanConstant($translation_row["constant_name"]);
    $translation_cache[$constant_name] = $translated_phrase;
  }
  return $translation_cache;
}

function getTranslationCache() {
  if (!array_key_exists("__translation_cache", $GLOBALS)) {
    // Cache not yet initialized... circular imports?
    $GLOBALS["__translation_cache"] = precacheTranslation();
  }
  return $GLOBALS["__translation_cache"];
}

function translationCacheLookup($cleaned_constant) {
  $cache = getTranslationCache();
  if (!array_key_exists($cleaned_constant, $cache)) {
    return null;
  }
  return $cache[$cleaned_constant];
}

function translateSingle($constant) {
  if (shouldSkipTranslation()) {
    return $constant;
  }

  // Try the cache
  $constant = cleanConstant($constant);
  $cache_hit = translationCacheLookup($constant);
  if ($cache_hit != null) {
    return $cache_hit;
  }

  return $constant;
}

// Translation function
// This is the translation engine
//  Note that it is recommended to no longer use the mode, prepend, or append
//  parameters, since this is not compatible with the htmlspecialchars() php
//  function.
function xl($constant,$mode='r',$prepend='',$append='') {
  $translation = translateSingle($constant);
  $string = $prepend . $translation . $append;
  if ($mode=='e') {
    echo $string;
  } else {
    return $string;
  }
}

// ----------- xl() function wrappers ------------------------------
//
// Use above xl() function the majority of time for translations. The
//  below wrappers are only for specific situations in order to support
//  granular control of translations in certain parts of OpenEMR.
//  Wrappers:
//    xl_list_label()
//    xl_layout_label()
//    xl_gacl_group()
//    xl_form_title()
//    xl_document_category()
//    xl_appt_category()
//
// Added 5-09 by BM for translation of list labels (when applicable)
// Only translates if the $GLOBALS['translate_lists'] is set to true.
function xl_list_label($constant,$mode='r',$prepend='',$append='') {
  if ($GLOBALS['translate_lists']) {
    // TRANSLATE
    if ($mode == "e") {
      xl($constant,$mode,$prepend,$append);
    }
    else {
      return xl($constant,$mode,$prepend,$append);
    }
  }
  else {
    // DO NOT TRANSLATE
    if ($mode == "e") {
      echo $prepend.$constant.$append;
    }
    else {
      return $prepend.$constant.$append;
    }
  }
}
// Added 5-09 by BM for translation of layout labels (when applicable)
// Only translates if the $GLOBALS['translate_layout'] is set to true.
function xl_layout_label($constant,$mode='r',$prepend='',$append='') {
  if ($GLOBALS['translate_layout']) {
    // TRANSLATE
    if ($mode == "e") {
      xl($constant,$mode,$prepend,$append);
    }
    else {
      return xl($constant,$mode,$prepend,$append);
    }
  }
  else {
    // DO NOT TRANSLATE
    if ($mode == "e") {
      echo $prepend.$constant.$append;
    }
    else {
      return $prepend.$constant.$append;
    }
  }
}
// Added 6-2009 by BM for translation of access control group labels 
//  (when applicable)
// Only translates if the $GLOBALS['translate_gacl_groups'] is set to true.
function xl_gacl_group($constant,$mode='r',$prepend='',$append='') {
  if ($GLOBALS['translate_gacl_groups']) {
    // TRANSLATE
    if ($mode == "e") {
      xl($constant,$mode,$prepend,$append);
    }
    else {
      return xl($constant,$mode,$prepend,$append);
    }
  }
  else {
    // DO NOT TRANSLATE
    if ($mode == "e") {
      echo $prepend.$constant.$append;
    }
    else {
      return $prepend.$constant.$append;
    }
  }
}
// Added 6-2009 by BM for translation of patient form (notes) titles
//  (when applicable)
// Only translates if the $GLOBALS['translate_form_titles'] is set to true.
function xl_form_title($constant,$mode='r',$prepend='',$append='') {
  if ($GLOBALS['translate_form_titles']) {
    // TRANSLATE
    if ($mode == "e") {
      xl($constant,$mode,$prepend,$append);
    }
    else {
      return xl($constant,$mode,$prepend,$append);
    }
  }
  else {
    // DO NOT TRANSLATE
    if ($mode == "e") {
      echo $prepend.$constant.$append;
    }
    else {
      return $prepend.$constant.$append;
    }
  }
}
//
// Added 6-2009 by BM for translation of document categories
//  (when applicable)
// Only translates if the $GLOBALS['translate_document_categories'] is set to true.
function xl_document_category($constant,$mode='r',$prepend='',$append='') {
  if ($GLOBALS['translate_document_categories']) {
    // TRANSLATE
    if ($mode == "e") {
      xl($constant,$mode,$prepend,$append);
    }
    else {
      return xl($constant,$mode,$prepend,$append);
    }
  }
  else {
    // DO NOT TRANSLATE
    if ($mode == "e") {
      echo $prepend.$constant.$append;
    }
    else {
      return $prepend.$constant.$append;
    }
  }
}
//
// Added 6-2009 by BM for translation of appointment categories
//  (when applicable)
// Only translates if the $GLOBALS['translate_appt_categories'] is set to true.
function xl_appt_category($constant,$mode='r',$prepend='',$append='') {
  if ($GLOBALS['translate_appt_categories']) {
    // TRANSLATE
    if ($mode == "e") {
      xl($constant,$mode,$prepend,$append);
    }
    else {
      return xl($constant,$mode,$prepend,$append);
    }
  }
  else {
    // DO NOT TRANSLATE
    if ($mode == "e") {
      echo $prepend.$constant.$append;
    }
    else {
      return $prepend.$constant.$append;
    }
  }
}
// ---------------------------------------------------------------------------

// ---------------------------------
// Miscellaneous language translation functions

// Function to return the title of a language from the id
// @param integer (language id)
// return string (language title)
function getLanguageTitle($val) {

 // validate language id
 if (!empty($val)) {
   $lang_id = $val;
 }
 else {
   $lang_id = 1;
 }
 
 // get language title
 $res = sqlStatement("select lang_description from lang_languages where lang_id = '".$lang_id."'");
 for ($iter = 0;$row = sqlFetchArray($res);$iter++) $result[$iter] = $row;
 $languageTitle = $result[0]{"lang_description"};   
 return $languageTitle;    
}

//----------------------------------

// ----------------------------------------------------------------------------
/**
HEADER HTML

shows some informations for pages html header 

@param none
@return void
*/
function html_header_show() {
    
    // Below line was commented by the UTF-8 project on 05-2009 by BM.
    //  We commented this out since we are now standardizing encoding
    //  in the globals.php file.
    // echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> '."\n";
}


// ----------------------------------------------------------------------------
/**
* Returns a string padded to a certain length with another string.
*
* This method behaves exactly like str_pad but is multibyte safe.
*
* @param string $input    The string to be padded.
* @param int $length      The length of the resulting string.
* @param string $pad      The string to pad the input string with. Must
*                         be in the same charset like the input string.
* @param const $type      The padding type. One of STR_PAD_LEFT,
*                         STR_PAD_RIGHT, or STR_PAD_BOTH.
* @param string $charset  The charset of the input and the padding
*                         strings.
*
* @return string  The padded string.
*/
function mb_strpad($input, $length, $pad = ' ', $type = STR_PAD_RIGHT, $charset = 'UTF-8') {
    mb_internal_encoding($charset);
    $mb_length = mb_strlen($input, $charset);
    $sb_length = strlen($input);
    $pad_length = mb_strlen($pad, $charset);

    /* Return if we already have the length. */
    if ($mb_length >= $length) {
        return $input;
    }

    /* Shortcut for single byte strings. */
    if ($mb_length == $sb_length && $pad_length == strlen($pad)) {
        return str_pad($input, $length, $pad, $type);
    }

    switch ($type) {
        case STR_PAD_LEFT:
            $left = $length - $mb_length;
            $output = mb_substr(str_repeat($pad, ceil($left / $pad_length)), 0, $left, $charset) . $input;
        break;
        case STR_PAD_BOTH:
            $left = floor(($length - $mb_length) / 2);
            $right = ceil(($length - $mb_length) / 2);
            $output = mb_substr(str_repeat($pad, ceil($left / $pad_length)), 0, $left, $charset) .
            $input .
            mb_substr(str_repeat($pad, ceil($right / $pad_length)), 0, $right, $charset);
        break;
        case STR_PAD_RIGHT:
            $right = $length - $mb_length;
            $output = $input . mb_substr(str_repeat($pad, ceil($right / $pad_length)), 0, $right, $charset);
        break;
    }

return $output;
}
?>
