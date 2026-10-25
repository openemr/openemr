<?php

namespace GeneralDermatology\Services\Info\Processors;

use GeneralDermatology\Services\Info\AbstractProcedureInfoProcessor;

/**
 * GenericProcedureInfoProcessor
 *
 * Fallback processor for unknown or generic procedure types.
 * Returns empty extraction since we don't know what fields to extract.
 * This prevents the system from breaking if an unknown procedure type is encountered.
 */
class GenericProcedureInfoProcessor extends AbstractProcedureInfoProcessor
{
    /**
     * Extract fields - Generic implementation
     *
     * Since this is a fallback processor for unknown types, we return no fields.
     * The base plan description is already rendered by TreatmentPlanService,
     * so missing procedure-specific fields is acceptable.
     *
     * @param array $treatment The treatment data
     * @return array Empty array - generic processor has no specific fields
     */
    public function extractFields(array $treatment): array
    {
        // Return empty array - no specific fields to extract for generic/unknown procedure
        return [];
    }

    /**
     * Format extracted fields - Generic implementation
     *
     * Since extractFields returns empty array, formatForDisplay returns empty string.
     *
     * @param array $fields Empty array from extractFields
     * @return string Empty string
     */
    public function formatForDisplay(array $fields): string
    {
        // No fields to display
        return '';
    }
}
