<?php

// Translation function
// This is the translation engine
//  Note that it is recommended to no longer use the mode, prepend, or append
//  parameters, since this is not compatible with the htmlspecialchars() php
//  function.
//
//  Note there are cases in installation where this function has already been
//   declared, so check to ensure has not been declared yet.
//
if (!(function_exists('xl'))) {
    function xl($constant, $mode = 'r', $prepend = '', $append = '')
    {
        if (!empty($GLOBALS['temp_skip_translations'])) {
            return $constant;
        }

        // set language id
        if (!empty($_SESSION['language_choice'])) {
             $lang_id = $_SESSION['language_choice'];
        } else {
             $lang_id = 1;
        }

        // TRANSLATE
        // first, clean lines
        // convert new lines to spaces and remove windows end of lines
        $patterns = array ('/\n/','/\r/');
        $replace = array (' ','');
        $constant = preg_replace($patterns, $replace, $constant);
        // second, attempt translation
        $sql = "SELECT * FROM lang_definitions JOIN lang_constants ON " .
        "lang_definitions.cons_id = lang_constants.cons_id WHERE " .
        "lang_id=? AND constant_name = ? LIMIT 1";
        $res = sqlStatementNoLog($sql, array($lang_id,$constant));
        $row = SqlFetchArray($res);
        $string = $row['definition'] ?? '';
        if ($string == '') {
            $string = "$constant";
        }
        // remove dangerous characters and remove comments
        if ($GLOBALS['translate_no_safe_apostrophe']) {
            $patterns = array ('/\n/','/\r/','/\{\{.*\}\}/');
            $replace = array (' ','','');
            $string = preg_replace($patterns, $replace, $string);
        } else {
            // convert apostrophes and quotes to safe apostrophe
            $patterns = array ('/\n/','/\r/','/"/',"/'/",'/\{\{.*\}\}/');
            $replace = array (' ','','`','`','');
            $string = preg_replace($patterns, $replace, $string);
        }

        $string = "$prepend" . "$string" . "$append";
        if ($mode == 'e') {
             echo $string;
        } else {
             return $string;
        }
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
function xl_list_label($constant, $mode = 'r', $prepend = '', $append = '')
{
    if ($GLOBALS['translate_lists']) {
        // TRANSLATE
        if ($mode == "e") {
            xl($constant, $mode, $prepend, $append);
        } else {
            return xl($constant, $mode, $prepend, $append);
        }
    } else {
        // DO NOT TRANSLATE
        if ($mode == "e") {
            echo $prepend . $constant . $append;
        } else {
            return $prepend . $constant . $append;
        }
    }
}
// Added 5-09 by BM for translation of layout labels (when applicable)
// Only translates if the $GLOBALS['translate_layout'] is set to true.
function xl_layout_label($constant, $mode = 'r', $prepend = '', $append = '')
{
    if ($GLOBALS['translate_layout']) {
        // TRANSLATE
        if ($mode == "e") {
            xl($constant, $mode, $prepend, $append);
        } else {
            return xl($constant, $mode, $prepend, $append);
        }
    } else {
        // DO NOT TRANSLATE
        if ($mode == "e") {
            echo $prepend . $constant . $append;
        } else {
            return $prepend . $constant . $append;
        }
    }
}
// Added 6-2009 by BM for translation of access control group labels
//  (when applicable)
// Only translates if the $GLOBALS['translate_gacl_groups'] is set to true.
function xl_gacl_group($constant, $mode = 'r', $prepend = '', $append = '')
{
    if ($GLOBALS['translate_gacl_groups']) {
        // TRANSLATE
        if ($mode == "e") {
            xl($constant, $mode, $prepend, $append);
        } else {
            return xl($constant, $mode, $prepend, $append);
        }
    } else {
        // DO NOT TRANSLATE
        if ($mode == "e") {
            echo $prepend . $constant . $append;
        } else {
            return $prepend . $constant . $append;
        }
    }
}
// Added 6-2009 by BM for translation of patient form (notes) titles
//  (when applicable)
// Only translates if the $GLOBALS['translate_form_titles'] is set to true.
function xl_form_title($constant, $mode = 'r', $prepend = '', $append = '')
{
    if ($GLOBALS['translate_form_titles']) {
        // TRANSLATE
        if ($mode == "e") {
            xl($constant, $mode, $prepend, $append);
        } else {
            return xl($constant, $mode, $prepend, $append);
        }
    } else {
        // DO NOT TRANSLATE
        if ($mode == "e") {
            echo $prepend . $constant . $append;
        } else {
            return $prepend . $constant . $append;
        }
    }
}
//
// Added 6-2009 by BM for translation of document categories
//  (when applicable)
// Only translates if the $GLOBALS['translate_document_categories'] is set to true.
function xl_document_category($constant, $mode = 'r', $prepend = '', $append = '')
{
    if ($GLOBALS['translate_document_categories']) {
        // TRANSLATE
        if ($mode == "e") {
            xl($constant, $mode, $prepend, $append);
        } else {
            return xl($constant, $mode, $prepend, $append);
        }
    } else {
        // DO NOT TRANSLATE
        if ($mode == "e") {
            echo $prepend . $constant . $append;
        } else {
            return $prepend . $constant . $append;
        }
    }
}
//
// Added 6-2009 by BM for translation of appointment categories
//  (when applicable)
// Only translates if the $GLOBALS['translate_appt_categories'] is set to true.
function xl_appt_category($constant, $mode = 'r', $prepend = '', $append = '')
{
    if ($GLOBALS['translate_appt_categories']) {
        // TRANSLATE
        if ($mode == "e") {
            xl($constant, $mode, $prepend, $append);
        } else {
            return xl($constant, $mode, $prepend, $append);
        }
    } else {
        // DO NOT TRANSLATE
        if ($mode == "e") {
            echo $prepend . $constant . $append;
        } else {
            return $prepend . $constant . $append;
        }
    }
}
// ---------------------------------------------------------------------------

// ---------------------------------
// Miscellaneous language translation functions

// Function to return the title of a language from the id
// @param integer (language id)
// return string (language title)
function getLanguageTitle($val)
{

 // validate language id
    if (!empty($val)) {
         $lang_id = $val;
    } else {
         $lang_id = 1;
    }

 // get language title
    $res = sqlStatement("select lang_description from lang_languages where lang_id =?", array($lang_id));
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $result[$iter] = $row;
    };
    $languageTitle = $result[0]["lang_description"];
    return $languageTitle;
}




/**
 * Returns language directionality as string 'rtl' or 'ltr'
 * @param int $lang_id language code
 * @return string 'ltr' 'rtl'
 * @author Amiel <amielel@matrix.co.il>
 */
function getLanguageDir($lang_id)
{
    // validate language id
    $lang_id = empty($lang_id) ? 1 : $lang_id;
    // get language code
    $row = sqlQuery('SELECT * FROM lang_languages WHERE lang_id = ?', array($lang_id));

    return !empty($row['lang_is_rtl']) ? 'rtl' : 'ltr';
}
