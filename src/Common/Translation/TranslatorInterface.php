<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @link      https://opencoreemr.com
 * @author    Michael Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Translation;

/**
 * Interface for translation operations.
 *
 * Allows for dependency injection and mocking in tests. New code should
 * accept this interface as a constructor parameter rather than using
 * the global xl() function directly.
 */
interface TranslatorInterface
{
    /**
     * Translate a string constant.
     *
     * Core translation method - returns translated string or original if not found.
     *
     * @param string $constant The text constant to translate
     * @return string The translated string
     */
    public function translate(string $constant): string;

    /**
     * Translate and escape for HTML text nodes.
     *
     * Equivalent to xlt() - escapes &, <, >
     *
     * @param string $constant The text constant to translate
     * @return string The translated and escaped string
     */
    public function translateText(string $constant): string;

    /**
     * Translate and escape for HTML attributes.
     *
     * Equivalent to xla() - escapes &, <, >, ", '
     *
     * @param string $constant The text constant to translate
     * @return string The translated and escaped string
     */
    public function translateAttribute(string $constant): string;

    /**
     * Translate and escape for JavaScript literals.
     *
     * Equivalent to xlj() - JSON encodes the result.
     *
     * @param string $constant The text constant to translate
     * @return string The translated and JS-escaped string
     */
    public function translateJavaScript(string $constant): string;

    /**
     * Conditionally translate list labels.
     *
     * Only translates if list translation is enabled in configuration.
     *
     * @param string $constant The text constant to translate
     * @return string The translated or original string
     */
    public function translateListLabel(string $constant): string;

    /**
     * Conditionally translate layout labels.
     *
     * Only translates if layout translation is enabled in configuration.
     *
     * @param string $constant The text constant to translate
     * @return string The translated or original string
     */
    public function translateLayoutLabel(string $constant): string;

    /**
     * Conditionally translate GACL group names.
     *
     * Only translates if GACL group translation is enabled in configuration.
     *
     * @param string $constant The text constant to translate
     * @return string The translated or original string
     */
    public function translateGaclGroup(string $constant): string;

    /**
     * Conditionally translate form titles.
     *
     * Only translates if form title translation is enabled in configuration.
     *
     * @param string $constant The text constant to translate
     * @return string The translated or original string
     */
    public function translateFormTitle(string $constant): string;

    /**
     * Conditionally translate document categories.
     *
     * Only translates if document category translation is enabled in configuration.
     *
     * @param string $constant The text constant to translate
     * @return string The translated or original string
     */
    public function translateDocumentCategory(string $constant): string;

    /**
     * Conditionally translate appointment categories.
     *
     * Only translates if appointment category translation is enabled in configuration.
     *
     * @param string $constant The text constant to translate
     * @return string The translated or original string
     */
    public function translateApptCategory(string $constant): string;

    /**
     * Warm the translation cache by loading all translations for the current language.
     *
     * Call this early in the request lifecycle for best performance.
     *
     * @return void
     */
    public function warmCache(): void;

    /**
     * Check if translation is currently disabled.
     *
     * @return bool True if translation is disabled
     */
    public function isDisabled(): bool;

    /**
     * Get the current language ID.
     *
     * @return int The language ID
     */
    public function getLanguageId(): int;
}
