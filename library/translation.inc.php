<?php

/**
 * Translation engine: xl() and its wrappers.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2009-2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Translation\ApcuTranslationStore;
use OpenEMR\Common\Translation\TranslationCache;
use OpenEMR\Common\Translation\TranslationDefinitionsLoader;
use OpenEMR\Common\Translation\TranslationPreloadMode;
use OpenEMR\Common\Translation\TranslationPreloadPolicy;
use OpenEMR\Core\OEGlobalsBag;

if (!(function_exists('xlLanguageId'))) {
    /**
     * The session's language id, or 1 (English) when none is set.
     */
    function xlLanguageId(): int
    {
        $choice = SessionWrapperFactory::getInstance()->getActiveSession()->get('language_choice');
        if (is_int($choice)) {
            return $choice > 0 ? $choice : 1;
        }
        if (is_string($choice) && ctype_digit($choice) && (int) $choice > 0) {
            return (int) $choice;
        }
        return 1;
    }
}

if (!(function_exists('xlWarmCache'))) {
    /**
     * Load all translations for the current language into the translation
     * cache when the translation_preload_cache global calls for it (Always,
     * or Auto for a language with few definitions such as English, or for
     * any language when APCu is available). With APCu the language is read
     * from the shared cache, and the database only on a miss. Called early in
     * each request from interface/globals.php.
     */
    function xlWarmCache(): void
    {
        $globals = OEGlobalsBag::getInstance();
        if ($globals->getBoolean('disable_translation')) {
            return;
        }
        $mode = TranslationPreloadMode::fromGlobal($globals->getString('translation_preload_cache'));
        if ($mode === TranslationPreloadMode::Off) {
            return;
        }

        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $lang_id = xlLanguageId();

        $shared = ApcuTranslationStore::isAvailable() ? new ApcuTranslationStore() : null;
        $policy = new TranslationPreloadPolicy(TranslationCache::countDefinitions(...));
        $decision = $policy->decide(
            $mode,
            $lang_id,
            $session->get(TranslationPreloadPolicy::SESSION_KEY),
            $shared !== null
        );
        if ($decision->remember !== null) {
            SessionUtil::setSession(TranslationPreloadPolicy::SESSION_KEY, $decision->remember);
        }
        if (!$decision->preload || TranslationCache::isWarmed($lang_id)) {
            return;
        }
        $loader = new TranslationDefinitionsLoader(
            $shared,
            TranslationDefinitionsLoader::keyPrefix(
                $globals->getString('OE_SITE_DIR'),
                $globals->getString(TranslationCache::VERSION_GLOBAL)
            ),
            TranslationCache::loadDefinitions(...)
        );
        TranslationCache::warmWith($lang_id, $loader->load($lang_id));
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
     * The parameter is typed `literal-string` on purpose: translatable text is
     * looked up by exact match against the lang_constants table, so it must be a
     * source-code literal that the string-extraction tooling can collect. Passing
     * a dynamic value (a database column, request input, a concatenation) cannot
     * be translated and signals that the value should have been narrowed to a
     * known string at the call site instead of handed to xl().
     *
     * @param literal-string $constant The text constant to translate
     * @return string The translated string
     */
    #[NoDiscard]
    function xl($constant): string
    {
        // Translation engine disabled: skip the cache/DB lookup for performance,
        // but still run xlCleanup() so {{context}} markers (and newline/quote
        // normalization) are stripped rather than leaking to the UI.
        if (OEGlobalsBag::getInstance()->getBoolean('disable_translation') || !empty(OEGlobalsBag::getInstance()->get('temp_skip_translations'))) {
            return xlCleanup($constant);
        }
        $lang_id = xlLanguageId();

        // TRANSLATE
        // first, clean lines
        // convert new lines to spaces and remove windows end of lines
        $patterns =  ['/\n/','/\r/'];
        $replace =  [' ',''];
        $constant = preg_replace($patterns, $replace, $constant) ?? $constant;

        // Check cache first
        if (TranslationCache::has($lang_id, $constant)) {
            $string = TranslationCache::get($lang_id, $constant);
        } elseif (TranslationCache::isWarmed($lang_id)) {
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
            $definition = $row['definition'] ?? '';
            $string = is_string($definition) ? $definition : '';
            // Cache for future lookups this request
            TranslationCache::set($lang_id, $constant, $string);
        }

        if ($string == '') {
            $string = "$constant";
        }

        return xlCleanup($string);
    }
}

if (!(function_exists('xlCleanup'))) {
    /**
     * Normalize a translated (or untranslated) string for display: collapse
     * newlines to spaces, drop carriage returns, and strip {{context}}
     * disambiguation markers. Unless translate_no_safe_apostrophe is set, quotes
     * and apostrophes are also converted to backticks. Shared by both the
     * translated and the disable_translation paths of xl().
     */
    #[NoDiscard]
    function xlCleanup(string $string): string
    {
        if (OEGlobalsBag::getInstance()->getBoolean('translate_no_safe_apostrophe')) {
            $patterns =  ['/\n/','/\r/','/\{\{.*\}\}/'];
            $replace =  [' ','',''];
        } else {
            // convert apostrophes and quotes to safe apostrophe
            $patterns =  ['/\n/','/\r/','/"/',"/'/",'/\{\{.*\}\}/'];
            $replace =  [' ','','`','`',''];
        }

        return preg_replace($patterns, $replace, $string) ?? $string;
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
#[NoDiscard]
function xl_list_label($constant)
{
    // @phpstan-ignore argument.type (intentionally accepts dynamic content)
    return OEGlobalsBag::getInstance()->getBoolean('translate_lists') ? xl($constant) : $constant;
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
#[NoDiscard]
function xl_layout_label($constant)
{
    // @phpstan-ignore argument.type (intentionally accepts dynamic content)
    return OEGlobalsBag::getInstance()->getBoolean('translate_layout') ? xl($constant) : $constant;
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
#[NoDiscard]
function xl_gacl_group($constant)
{
    // @phpstan-ignore argument.type (intentionally accepts dynamic content)
    return OEGlobalsBag::getInstance()->getBoolean('translate_gacl_groups') ? xl($constant) : $constant;
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
#[NoDiscard]
function xl_form_title($constant)
{
    // @phpstan-ignore argument.type (intentionally accepts dynamic content)
    return OEGlobalsBag::getInstance()->getBoolean('translate_form_titles') ? xl($constant) : $constant;
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
#[NoDiscard]
function xl_document_category($constant)
{
    // @phpstan-ignore argument.type (intentionally accepts dynamic content)
    return OEGlobalsBag::getInstance()->getBoolean('translate_document_categories') ? xl($constant) : $constant;
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
#[NoDiscard]
function xl_appt_category($constant)
{
    // @phpstan-ignore argument.type (intentionally accepts dynamic content)
    return OEGlobalsBag::getInstance()->getBoolean('translate_appt_categories') ? xl($constant) : $constant;
}
// ---------------------------------------------------------------------------

// ---------------------------------
// Miscellaneous language translation functions

// Function to return the title of a language from the id
// @param integer (language id)
// return string (language title)
#[NoDiscard]
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
#[NoDiscard]
function getLanguageDir($lang_id): string
{
    // validate language id
    $lang_id = empty($lang_id) ? 1 : $lang_id;
    // get language code
    $row = sqlQuery('SELECT * FROM lang_languages WHERE lang_id = ?', [$lang_id]);

    return !empty($row['lang_is_rtl']) ? 'rtl' : 'ltr';
}
