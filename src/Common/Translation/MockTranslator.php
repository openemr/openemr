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
 * Mock translator for testing purposes.
 *
 * Returns pre-configured translations without database access.
 * Useful for unit testing components that depend on TranslatorInterface.
 */
class MockTranslator implements TranslatorInterface
{
    /** @var array<string, string> Map of constant => translation */
    private array $translations;

    /** @var array<string, mixed> Configuration settings */
    private array $config;

    /**
     * @param array<string, string> $translations Map of constant => translation
     * @param array<string, mixed> $config Configuration overrides
     */
    public function __construct(array $translations = [], array $config = [])
    {
        $this->translations = $translations;
        $this->config = array_merge([
            'disabled' => false,
            'language_id' => 1,
            'translate_lists' => true,
            'translate_layout' => true,
            'translate_gacl_groups' => true,
            'translate_form_titles' => true,
            'translate_document_categories' => true,
            'translate_appt_categories' => true,
        ], $config);
    }

    public function translate(string $constant): string
    {
        if ($this->isDisabled()) {
            return $constant;
        }

        return $this->translations[$constant] ?? $constant;
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
        return $this->config['translate_lists'] ? $this->translate($constant) : $constant;
    }

    public function translateLayoutLabel(string $constant): string
    {
        return $this->config['translate_layout'] ? $this->translate($constant) : $constant;
    }

    public function translateGaclGroup(string $constant): string
    {
        return $this->config['translate_gacl_groups'] ? $this->translate($constant) : $constant;
    }

    public function translateFormTitle(string $constant): string
    {
        return $this->config['translate_form_titles'] ? $this->translate($constant) : $constant;
    }

    public function translateDocumentCategory(string $constant): string
    {
        return $this->config['translate_document_categories'] ? $this->translate($constant) : $constant;
    }

    public function translateApptCategory(string $constant): string
    {
        return $this->config['translate_appt_categories'] ? $this->translate($constant) : $constant;
    }

    public function warmCache(): void
    {
        // No-op for mock - no cache to warm
    }

    public function isDisabled(): bool
    {
        return $this->config['disabled'];
    }

    public function getLanguageId(): int
    {
        return $this->config['language_id'];
    }

    /**
     * Set translations for testing.
     *
     * @param array<string, string> $translations Map of constant => translation
     */
    public function setTranslations(array $translations): void
    {
        $this->translations = $translations;
    }

    /**
     * Add a single translation.
     *
     * @param string $constant The text constant
     * @param string $translation The translation
     */
    public function addTranslation(string $constant, string $translation): void
    {
        $this->translations[$constant] = $translation;
    }

    /**
     * Update configuration settings.
     *
     * @param array<string, mixed> $config Configuration overrides
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }
}
