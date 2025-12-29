<?php

/**
 * Translation Functions
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @link      https://opencoreemr.com
 * @author    Michael Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Translation\TranslatorFactory;

if (!(function_exists('xlWarmCache'))) {
    /**
     * Warm the translation cache by loading all translations for the current language.
     * Call this early in the request lifecycle for best performance.
     */
    function xlWarmCache(): void
    {
        TranslatorFactory::getInstance()->warmCache();
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
    function xl(string $constant): string
    {
        return TranslatorFactory::getInstance()->translate($constant);
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
function xl_list_label(string $constant): string
{
    return TranslatorFactory::getInstance()->translateListLabel($constant);
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
function xl_layout_label(string $constant): string
{
    return TranslatorFactory::getInstance()->translateLayoutLabel($constant);
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
function xl_gacl_group(string $constant): string
{
    return TranslatorFactory::getInstance()->translateGaclGroup($constant);
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
function xl_form_title(string $constant): string
{
    return TranslatorFactory::getInstance()->translateFormTitle($constant);
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
function xl_document_category(string $constant): string
{
    return TranslatorFactory::getInstance()->translateDocumentCategory($constant);
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
function xl_appt_category(string $constant): string
{
    return TranslatorFactory::getInstance()->translateApptCategory($constant);
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
