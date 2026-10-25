<?php

namespace GeneralDermatology\Services\Info;

/**
 * ProcedureInfoProcessor Interface
 *
 * Defines the contract that all procedure-specific info processors must implement.
 * This interface enables polymorphic behavior where each procedure type (Laser, Biopsy, etc.)
 * can implement the same interface with different extraction and formatting logic.
 */
interface ProcedureInfoProcessor
{
    /**
     * Extract fields from treatment data
     *
     * Parses the treatment JSON data and extracts procedure-specific fields,
     * handling null/empty values gracefully.
     *
     * @param array $treatment The treatment data from the form
     * @return array Associative array of extracted fields with human-readable keys
     */
    public function extractFields(array $treatment): array;

    /**
     * Format extracted fields for PDF display
     *
     * Takes the extracted fields and formats them as HTML suitable for PDF output.
     * Returns a string ready to be appended to the treatment plan HTML.
     *
     * @param array $fields The extracted fields from extractFields()
     * @return string HTML formatted procedure details ready for PDF
     */
    public function formatForDisplay(array $fields): string;
}
