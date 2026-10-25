<?php

namespace GeneralDermatology\Services\Info;

/**
 * AbstractProcedureInfoProcessor
 *
 * Abstract base class providing shared utility methods for all procedure-specific processors.
 * Each procedure type (Laser, Biopsy, Cryotherapy, etc.) extends this class and implements
 * extractFields() and formatForDisplay() methods.
 *
 * Benefits of inheritance:
 * - DRY: Common utility methods shared across all processors
 * - Consistency: All processors use same formatting helpers
 * - Maintainability: Update utility methods once, benefits all processors
 */
abstract class AbstractProcedureInfoProcessor implements ProcedureInfoProcessor
{
    /**
     * Check if a field value is empty or null.
     *
     * Supports two calling patterns:
     *   isFieldEmpty($value)            — pass a resolved value directly
     *   isFieldEmpty($array, 'key')     — pass an array and a key to look up
     *
     * @param mixed       $valueOrArray A scalar value, or an array when $key is supplied
     * @param string|null $key          Optional array key; if supplied, value is read from $valueOrArray[$key]
     * @return bool True if the resolved value is null, empty-string, or '0'
     */
    protected function isFieldEmpty(mixed $valueOrArray, ?string $key = null): bool
    {
        $value = ($key !== null && is_array($valueOrArray))
            ? ($valueOrArray[$key] ?? null)
            : $valueOrArray;

        return $value === null || $value === '' || $value === '0';
    }

    /**
     * Extract a single field value safely
     *
     * @param array $data The data array to extract from
     * @param string $key The key to extract
     * @param mixed $default Default value if key doesn't exist
     * @return mixed The extracted value or default
     */
    protected function getField(array $data, string $key, $default = null)
    {
        return $data[$key] ?? $default;
    }

    /**
     * Extract boolean checkbox field (returns 1 or 0, true or false)
     *
     * @param array $data The data array to extract from
     * @param string $key The key to extract
     * @return bool True if value is 1, false otherwise
     */
    protected function getCheckboxField(array $data, string $key): bool
    {
        return isset($data[$key]) && $data[$key] == 1;
    }

    /**
     * Extract multiple related checkbox fields and return matching labels.
     *
     * Supports two calling patterns:
     *   extractCheckboxGroup($data, $prefix, $fields)   — original 3-arg form (prefix is accepted but not used)
     *   extractCheckboxGroup($data, $fields)            — 2-arg form used by newer processors
     *
     * The $prefix argument is retained for backward compatibility but has no effect;
     * all field keys in $fields are expected to already be fully qualified.
     *
     * @param array        $data           The data array to search
     * @param string|array $prefixOrFields A prefix string (ignored), or the fields array directly
     * @param array        $fields         Associative array of key => label pairs (used when prefix is a string)
     * @return array Array of selected labels
     */
    protected function extractCheckboxGroup(array $data, string|array $prefixOrFields, array $fields = []): array
    {
        if (is_array($prefixOrFields)) {
            $fields = $prefixOrFields;
        }
        // $prefix (when a string) is accepted for compatibility but not applied —
        // the keys in $fields are already fully qualified.
        $selected = [];
        foreach ($fields as $key => $label) {
            if ($this->getCheckboxField($data, $key)) {
                $selected[] = $label;
            }
        }
        return $selected;
    }

    /**
     * Safe text wrapper function (equivalent to text() helper in OpenEMR)
     *
     * @param string $text Text to escape
     * @return string Escaped text safe for HTML
     */
    protected function escapeText(string $text): string
    {
        if (function_exists('text')) {
            return text($text);
        }
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Format a single field for HTML display
     *
     * @param string $label The field label (e.g., "Laser Type")
     * @param mixed $value The field value
     * @return string HTML formatted field (e.g., "<p><strong>Laser Type:</strong> CO2</p>")
     */
    protected function formatField(string $label, $value): string
    {
        if ($this->isFieldEmpty($value)) {
            return '';
        }
        return "<p><strong>{$label}:</strong> " . $this->escapeText((string)$value) . "</p>";
    }

    /**
     * Format a list of items for HTML display.
     *
     * Supports two calling patterns:
     *   formatFieldList('Label', $items)  — returns "<p><strong>Label:</strong> a, b, c</p>"
     *   formatFieldList($items)           — returns "a, b, c" (plain comma-separated, no label wrapper)
     *
     * The label-less form is used when the caller passes the result directly into
     * formatSection(), which provides the heading via its own wrapper.
     *
     * @param string|array $labelOrItems A label string, or the items array directly when no label is needed
     * @param array        $items        Items array (used only when $labelOrItems is a string)
     * @return string HTML formatted list
     */
    protected function formatFieldList(string|array $labelOrItems, array $items = []): string
    {
        if (is_array($labelOrItems)) {
            $items = $labelOrItems;
            $label = '';
        } else {
            $label = $labelOrItems;
        }

        if (empty($items)) {
            return '';
        }

        $itemsHtml = implode(', ', array_map([$this, 'escapeText'], $items));

        if ($label === '') {
            return $itemsHtml;
        }

        return "<p><strong>{$label}:</strong> {$itemsHtml}</p>";
    }

    /**
     * Format a procedure details section with heading
     *
     * @param string $sectionTitle Title for the section (e.g., "Anesthetic Options")
     * @param string $content HTML content for the section
     * @return string HTML formatted section
     */
    protected function formatSection(string $sectionTitle, string $content): string
    {
        if (empty($content)) {
            return '';
        }
        return "<div class=\"procedure-section\">"
            . "<h5 style=\"margin: 10px 0 5px 0;\">{$sectionTitle}</h5>"
            . $content
            . "</div>";
    }

    /**
     * Wrap all procedure details in a container div
     *
     * @param string $html The procedure details HTML
     * @return string HTML wrapped in procedure-details container
     */
    protected function wrapProcedureDetails(string $html): string
    {
        if (empty($html)) {
            return '';
        }
        return "<div class=\"procedure-details\">" . $html . "</div>";
    }

    /**
     * Combine multiple sections with proper formatting.
     *
     * Supports two assembly patterns used across processors:
     *
     *   Associative array: ['Section Title' => $innerHtml, ...]  — title is the key, content is raw inner HTML
     *   Numeric array:     [$this->formatSection('Title', $html), ...]  — content is already fully formatted
     *
     * @param array $sections Associative or numeric array of section content
     * @return string Combined HTML sections
     */
    protected function combineSections(array $sections): string
    {
        $html = '';
        foreach ($sections as $key => $content) {
            if (empty($content)) {
                continue;
            }
            if (is_int($key)) {
                // Numeric key: content is already a fully formatted section block
                $html .= $content;
            } else {
                // String key: key is the section title, content is raw inner HTML
                $html .= $this->formatSection($key, $content);
            }
        }
        return $html;
    }
}
