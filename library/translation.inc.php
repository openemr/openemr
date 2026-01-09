<?php

use OpenEMR\Common\Translation\TranslationCache;

if (!(function_exists('xlWarmCache'))) {
    /**
     * Warm the translation cache by loading all translations for the current language.
     * Call this early in the request lifecycle for best performance.
     */
    function xlWarmCache(): void
    {
        $lang_id = !empty($_SESSION['language_choice']) ? (int)$_SESSION['language_choice'] : 1;
        TranslationCache::warm($lang_id);
    }
}

if (!(function_exists('xl'))) {
    /**
     * Translation function - the translation engine for OpenEMR
     *
     * Translates a given constant string into the current session language.
     * Note: In some installation scenarios this function may already be declared,
     * so we check to ensure it hasn't been declared yet.
     *
     * @param string $constant The text constant to translate
     * @return string The translated string
     */
    function xl($constant)
    {
        if (!empty($GLOBALS['disable_translation']) || !empty($GLOBALS['temp_skip_translations'])) {
            return $constant;
        }

        // set language id
        $lang_id = !empty($_SESSION['language_choice']) ? $_SESSION['language_choice'] : 1;

        // TRANSLATE
        // first, clean lines
        // convert new lines to spaces and remove windows end of lines
        $patterns =  ['/\n/','/\r/'];
        $replace =  [' ',''];
        $constant = preg_replace($patterns, $replace, $constant);

        // Check cache first
        if (TranslationCache::has($lang_id, $constant)) {
            $string = TranslationCache::get($lang_id, $constant);
        } elseif (TranslationCache::isWarmed()) {
            // Cache is warmed but constant not found - no translation exists
            $string = '';
        } else {
            // Cache not warmed, query database
            $sql = <<<'SQL'
                SELECT lang_definitions.definition
                  FROM lang_definitions
                  JOIN lang_constants
                 USING (cons_id)
                 WHERE lang_definitions.lang_id = ?
                   AND lang_constants.constant_name = ?
                 LIMIT 1
                SQL;
            $res = sqlStatementNoLog($sql, [$lang_id, $constant]);
            $row = sqlFetchArray($res);
            $string = $row['definition'] ?? '';
            // Cache for future lookups this request
            TranslationCache::set($lang_id, $constant, $string);
        }

        if ($string == '') {
            $string = "$constant";
        }
        // remove dangerous characters and remove comments
        if (!empty($GLOBALS['translate_no_safe_apostrophe'])) {
            $patterns =  ['/\n/','/\r/','/\{\{.*\}\}/'];
            $replace =  [' ','',''];
            $string = preg_replace($patterns, $replace, (string) $string);
        } else {
            // convert apostrophes and quotes to safe apostrophe
            $patterns =  ['/\n/','/\r/','/"/',"/'/",'/\{\{.*\}\}/'];
            $replace =  [' ','','`','`',''];
            $string = preg_replace($patterns, $replace, (string) $string);
        }

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
/**
 * Conditionally translates list labels based on global setting
 *
 * Only translates if $GLOBALS['translate_lists'] is set to true.
 * Added 5-09 by BM.
 *
 * @param string $constant The text constant to translate
 * @return string The translated or original string
 */
function xl_list_label($constant)
{
    return $GLOBALS['translate_lists'] ? xl($constant) : $constant;
}

/**
 * Conditionally translates layout labels based on global setting
 *
 * Only translates if $GLOBALS['translate_layout'] is set to true.
 * Added 5-09 by BM.
 *
 * @param string $constant The text constant to translate
 * @return string The translated or original string
 */
function xl_layout_label($constant)
{
    return $GLOBALS['translate_layout'] ? xl($constant) : $constant;
}

/**
 * Conditionally translates access control group labels based on global setting
 *
 * Only translates if $GLOBALS['translate_gacl_groups'] is set to true.
 * Added 6-2009 by BM.
 *
 * @param string $constant The text constant to translate
 * @return string The translated or original string
 */
function xl_gacl_group($constant)
{
    return $GLOBALS['translate_gacl_groups'] ? xl($constant) : $constant;
}

/**
 * Conditionally translates patient form (notes) titles based on global setting
 *
 * Only translates if $GLOBALS['translate_form_titles'] is set to true.
 * Added 6-2009 by BM.
 *
 * @param string $constant The text constant to translate
 * @return string The translated or original string
 */
function xl_form_title($constant)
{
    return $GLOBALS['translate_form_titles'] ? xl($constant) : $constant;
}

/**
 * Conditionally translates document categories based on global setting
 *
 * Only translates if $GLOBALS['translate_document_categories'] is set to true.
 * Added 6-2009 by BM.
 *
 * @param string $constant The text constant to translate
 * @return string The translated or original string
 */
function xl_document_category($constant)
{
    return $GLOBALS['translate_document_categories'] ? xl($constant) : $constant;
}

/**
 * Conditionally translates appointment categories based on global setting
 *
 * Only translates if $GLOBALS['translate_appt_categories'] is set to true.
 * Added 6-2009 by BM.
 *
 * @param string $constant The text constant to translate
 * @return string The translated or original string
 */
function xl_appt_category($constant)
{
    return $GLOBALS['translate_appt_categories'] ? xl($constant) : $constant;
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
    $lang_id = !empty($val) ? $val : 1;

 // get language title
    $res = sqlStatement("select lang_description from lang_languages where lang_id =?", [$lang_id]);
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
    $row = sqlQuery('SELECT * FROM lang_languages WHERE lang_id = ?', [$lang_id]);

    return !empty($row['lang_is_rtl']) ? 'rtl' : 'ltr';
}
