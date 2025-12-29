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

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;

/**
 * Main translator implementation.
 *
 * Handles translation with caching and configuration awareness.
 * Uses QueryUtils for database access and OEGlobalsBag for configuration.
 */
class Translator implements TranslatorInterface
{
    /** @var array<int, array<string, string>> Internal cache indexed by language ID */
    private array $cache = [];

    /** @var bool Whether the cache has been fully warmed */
    private bool $warmed = false;

    /** @var int|null Cached language ID for the current request */
    private ?int $languageId = null;

    /** @var OEGlobalsBag|null Configuration bag */
    private ?OEGlobalsBag $globalsBag;

    /** @var callable|null Session accessor for testing */
    private $sessionAccessor;

    /**
     * @param OEGlobalsBag|null $globalsBag Configuration bag (uses $GLOBALS fallback if null)
     * @param callable|null $sessionAccessor Session value accessor for testing
     */
    public function __construct(
        ?OEGlobalsBag $globalsBag = null,
        ?callable $sessionAccessor = null
    ) {
        $this->globalsBag = $globalsBag;
        $this->sessionAccessor = $sessionAccessor;
    }

    public function translate(string $constant): string
    {
        if ($this->isDisabled()) {
            return $constant;
        }

        $langId = $this->getLanguageId();

        // Normalize: convert newlines to spaces, remove carriage returns
        $constant = preg_replace(['/\n/', '/\r/'], [' ', ''], $constant);

        // Check cache first
        if (isset($this->cache[$langId][$constant])) {
            $string = $this->cache[$langId][$constant];
        } elseif ($this->warmed) {
            // Cache is warmed but constant not found - no translation exists
            $string = '';
        } else {
            // Query database and cache result
            $string = $this->fetchTranslation($langId, $constant);
            if (!isset($this->cache[$langId])) {
                $this->cache[$langId] = [];
            }
            $this->cache[$langId][$constant] = $string;
        }

        if ($string === '') {
            $string = $constant;
        }

        return $this->sanitizeOutput($string);
    }

    public function translateText(string $constant): string
    {
        return htmlspecialchars($this->translate($constant), ENT_NOQUOTES);
    }

    public function translateAttribute(string $constant): string
    {
        return htmlspecialchars($this->translate($constant), ENT_QUOTES);
    }

    public function translateJavaScript(string $constant): string
    {
        return json_encode($this->translate($constant));
    }

    public function translateListLabel(string $constant): string
    {
        return $this->getGlobalValue('translate_lists') ? $this->translate($constant) : $constant;
    }

    public function translateLayoutLabel(string $constant): string
    {
        return $this->getGlobalValue('translate_layout') ? $this->translate($constant) : $constant;
    }

    public function translateGaclGroup(string $constant): string
    {
        return $this->getGlobalValue('translate_gacl_groups') ? $this->translate($constant) : $constant;
    }

    public function translateFormTitle(string $constant): string
    {
        return $this->getGlobalValue('translate_form_titles') ? $this->translate($constant) : $constant;
    }

    public function translateDocumentCategory(string $constant): string
    {
        return $this->getGlobalValue('translate_document_categories') ? $this->translate($constant) : $constant;
    }

    public function translateApptCategory(string $constant): string
    {
        return $this->getGlobalValue('translate_appt_categories') ? $this->translate($constant) : $constant;
    }

    public function warmCache(): void
    {
        if ($this->warmed) {
            return;
        }

        $langId = $this->getLanguageId();

        $sql = <<<'SQL'
            SELECT lang_constants.constant_name,
                   lang_definitions.definition
              FROM lang_definitions
              JOIN lang_constants USING (cons_id)
             WHERE lang_definitions.lang_id = ?
            SQL;

        $rows = QueryUtils::fetchRecordsNoLog($sql, [$langId]);
        $this->cache[$langId] = array_column($rows, 'definition', 'constant_name');
        $this->warmed = true;
    }

    public function isDisabled(): bool
    {
        return $this->getGlobalValue('disable_translation')
            || $this->getGlobalValue('temp_skip_translations');
    }

    public function getLanguageId(): int
    {
        if ($this->languageId !== null) {
            return $this->languageId;
        }

        if ($this->sessionAccessor !== null) {
            $value = ($this->sessionAccessor)('language_choice');
            $this->languageId = !empty($value) ? (int)$value : 1;
        } else {
            $this->languageId = !empty($_SESSION['language_choice'])
                ? (int)$_SESSION['language_choice']
                : 1;
        }

        return $this->languageId;
    }

    /**
     * Reset internal state.
     *
     * Clears cache and resets language ID. Useful for testing.
     */
    public function reset(): void
    {
        $this->cache = [];
        $this->warmed = false;
        $this->languageId = null;
    }

    /**
     * Set language ID explicitly.
     *
     * Useful for API/CLI contexts where session is not available.
     *
     * @param int $langId The language ID to use
     */
    public function setLanguageId(int $langId): void
    {
        $this->languageId = $langId;
    }

    /**
     * Check if cache has been warmed.
     *
     * @return bool True if cache is warmed
     */
    public function isWarmed(): bool
    {
        return $this->warmed;
    }

    /**
     * Fetch a single translation from the database.
     *
     * @param int $langId Language ID
     * @param string $constant The text constant to translate
     * @return string The translation or empty string if not found
     */
    private function fetchTranslation(int $langId, string $constant): string
    {
        $sql = <<<'SQL'
            SELECT lang_definitions.definition
              FROM lang_definitions
              JOIN lang_constants USING (cons_id)
             WHERE lang_definitions.lang_id = ?
               AND lang_constants.constant_name = ?
             LIMIT 1
            SQL;

        $result = QueryUtils::fetchRecordsNoLog($sql, [$langId, $constant]);

        return $result[0]['definition'] ?? '';
    }

    /**
     * Sanitize translated string for output.
     *
     * Removes dangerous characters and optionally converts quotes to backticks.
     *
     * @param string $string The string to sanitize
     * @return string The sanitized string
     */
    private function sanitizeOutput(string $string): string
    {
        if ($this->getGlobalValue('translate_no_safe_apostrophe')) {
            // Only remove newlines and mustache-style comments
            return preg_replace(['/\n/', '/\r/', '/\{\{.*\}\}/'], [' ', '', ''], $string);
        }

        // Convert apostrophes/quotes to safe backtick, remove comments
        return preg_replace(
            ['/\n/', '/\r/', '/"/', "/'/", '/\{\{.*\}\}/'],
            [' ', '', '`', '`', ''],
            $string
        );
    }

    /**
     * Get a configuration value from globals.
     *
     * @param string $key The configuration key
     * @return mixed The configuration value or false if not set
     */
    private function getGlobalValue(string $key): mixed
    {
        if ($this->globalsBag !== null) {
            return $this->globalsBag->get($key, false);
        }

        return $GLOBALS[$key] ?? false;
    }
}
