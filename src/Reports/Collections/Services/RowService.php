<?php

/**
 * Collections Report Row Service
 * Built with Warp Terminal
 * Prepares individual invoice row data for rendering in the Collections Report.
 * Handles patient demographics, financial calculations, aging buckets, and conditional fields.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\Collections\Services;

class RowService
{
    /**
     * Prepare row data for template rendering
     *
     * @param array $rowData Raw row data from database query
     * @param array $filterConfig Filter configuration settings
     * @param bool $isFirstRow Whether this is the first row for this patient
     * @return array Prepared row data for template
     */
    public function prepareRow(array $rowData, array $filterConfig, bool $isFirstRow = true): array
    {
        $prepared = [];

        // Basic identifiers
        $prepared['id'] = $rowData['id'] ?? '';
        $prepared['pid'] = $rowData['pid'] ?? '';
        $prepared['encounter'] = $rowData['encounter'] ?? '';
        $prepared['is_first_row'] = $isFirstRow;

        // Patient name
        $prepared['patient_name'] = $this->formatPatientName($rowData);

        // Demographic fields
        $prepared['ssn'] = $rowData['ss'] ?? '';
        $prepared['dob'] = $this->formatDate($rowData['DOB'] ?? '');
        $prepared['pubpid'] = $rowData['pubpid'] ?? '';
        $prepared['phone'] = $rowData['phone_home'] ?? '';
        $prepared['city'] = $rowData['city'] ?? '';

        // Insurance and policy
        $prepared['primary_insurance'] = $rowData['ins1'] ?? '';
        $prepared['policy'] = $rowData['policy'] ?? '';
        $prepared['group_number'] = $rowData['groupnumber'] ?? '';

        // Provider and referrer
        $prepared['provider'] = $rowData['provider_id'] ?? '';
        $prepared['referrer'] = $rowData['referrer'] ?? '';

        // Invoice information
        $prepared['invoice_number'] = $this->getInvoiceNumber($rowData);
        $prepared['service_date'] = $this->formatDate($rowData['dos'] ?? $rowData['date'] ?? '');
        $prepared['dos'] = $prepared['service_date']; // Alias for template compatibility

        // Financial fields
        $charges = (float)($rowData['charges'] ?? 0);
        $adjustments = (float)($rowData['adjustments'] ?? 0);
        $paid = (float)($rowData['paid'] ?? 0);
        $balance = $charges + $adjustments - $paid;

        $prepared['charges'] = $charges;
        $prepared['adjustments'] = $adjustments;
        $prepared['paid'] = $paid;
        $prepared['balance'] = $balance;

        // Formatted financial values
        $prepared['charges_formatted'] = $this->formatMoney($charges);
        $prepared['adjustments_formatted'] = $this->formatMoney($adjustments);
        $prepared['paid_formatted'] = $this->formatMoney($paid);
        $prepared['balance_formatted'] = $this->formatMoney($balance);

        // Aging information
        $prepared['activity_date'] = $this->formatDate($rowData['aging_date'] ?? '');
        $prepared['aging_days'] = (int)($rowData['inactive_days'] ?? 0);

        // Aging buckets (if aging columns are enabled)
        $prepared['aging_buckets'] = $this->calculateAgingBuckets(
            $rowData,
            $balance,
            $filterConfig
        );

        // Collections status
        $prepared['is_in_collections'] = $this->isInCollections($rowData);

        // Statement count
        $prepared['statement_count'] = (int)($rowData['duncount'] ?? 0);

        // Billing error
        $prepared['billing_error'] = $rowData['billing_errmsg'] ?? '';

        // Visibility flags
        $prepared['show_ssn'] = $filterConfig['form_cb_ssn'] ?? false;
        $prepared['show_dob'] = $filterConfig['form_cb_dob'] ?? false;
        $prepared['show_pubpid'] = $filterConfig['form_cb_pubpid'] ?? false;
        $prepared['show_policy'] = $filterConfig['form_cb_policy'] ?? false;
        $prepared['show_group_number'] = $filterConfig['form_cb_group_number'] ?? false;
        $prepared['show_phone'] = $filterConfig['form_cb_phone'] ?? false;
        $prepared['show_city'] = $filterConfig['form_cb_city'] ?? false;
        // Insurance column only shows in "Due Ins" mode
        $prepared['show_insurance'] = $filterConfig['is_due_ins'] ?? false;
        // Primary Insurance column shows when form_cb_ins1 OR form_payer_id is set
        $prepared['show_primary_insurance'] = ($filterConfig['form_cb_ins1'] ?? false) || !empty($filterConfig['form_payer_id']);
        $prepared['show_provider'] = !empty($filterConfig['form_provider']);
        $prepared['show_referrer'] = $filterConfig['form_cb_referrer'] ?? false;
        $prepared['show_activity_date'] = $filterConfig['form_cb_adate'] ?? false;
        $prepared['show_aging_days'] = $filterConfig['form_cb_idays'] ?? false;
        $prepared['show_error'] = $filterConfig['form_cb_err'] ?? false;

        return $prepared;
    }

    /**
     * Format patient name as "Last, First M"
     *
     * @param array $rowData Row data containing name fields
     * @return string Formatted patient name
     */
    private function formatPatientName(array $rowData): string
    {
        $lname = $rowData['lname'] ?? '';
        $fname = $rowData['fname'] ?? '';
        $mname = $rowData['mname'] ?? '';

        $name = $lname . ', ' . $fname;

        if (!empty($mname)) {
            $name .= ' ' . substr($mname, 0, 1);
        }

        return $name;
    }

    /**
     * Format date from YYYY-MM-DD to MM/DD/YYYY
     *
     * @param string $date Date in YYYY-MM-DD format
     * @return string Formatted date or empty string
     */
    private function formatDate(string $date): string
    {
        if (empty($date) || $date === '0000-00-00') {
            return '';
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $date, $matches)) {
            return $matches[2] . '/' . $matches[3] . '/' . $matches[1];
        }

        return $date;
    }

    /**
     * Get invoice number (prefer reference number over internal number)
     *
     * @param array $rowData Row data
     * @return string Invoice number
     */
    private function getInvoiceNumber(array $rowData): string
    {
        if (!empty($rowData['irnumber'])) {
            return $rowData['irnumber'];
        }

        return $rowData['invnumber'] ?? '';
    }

    /**
     * Format money value with thousands separator and 2 decimal places
     *
     * @param float $amount Amount to format
     * @return string Formatted money value
     */
    private function formatMoney(float $amount): string
    {
        // Format with 2 decimal places
        $formatted = number_format(abs($amount), 2, '.', ',');

        // Add negative sign if needed
        if ($amount < 0) {
            return '-' . $formatted;
        }

        return $formatted;
    }

    /**
     * Calculate aging buckets for balance distribution
     *
     * @param array $rowData Row data
     * @param float $balance Total balance
     * @param array $filterConfig Filter configuration
     * @return array Array of aging bucket values
     */
    private function calculateAgingBuckets(array $rowData, float $balance, array $filterConfig): array
    {
        $ageCols = (int)($filterConfig['form_age_cols'] ?? 0);
        $ageInc = (int)($filterConfig['form_age_inc'] ?? 30);

        if ($ageCols <= 0) {
            return [];
        }

        // Initialize buckets
        $buckets = array_fill(0, $ageCols, 0.0);

        // Determine age date
        $ageBy = $filterConfig['form_ageby'] ?? 'Service Date';
        $ageDate = (strpos($ageBy, 'Last') !== false)
            ? ($rowData['aging_date'] ?? $rowData['dos'] ?? '')
            : ($rowData['dos'] ?? '');

        if (empty($ageDate)) {
            return $buckets;
        }

        // Calculate days old
        $ageTime = strtotime($ageDate);
        if ($ageTime === false) {
            return $buckets;
        }

        $days = floor((time() - $ageTime) / (60 * 60 * 24));

        // Determine which bucket
        $bucketIndex = min($ageCols - 1, max(0, floor($days / $ageInc)));

        // Put entire balance in the appropriate bucket
        $buckets[$bucketIndex] = $balance;

        return $buckets;
    }

    /**
     * Determine if invoice is in collections
     *
     * @param array $rowData Row data
     * @return bool True if in collections
     */
    private function isInCollections(array $rowData): bool
    {
        // Check in_collection flag
        if (($rowData['in_collection'] ?? 0) == 1) {
            return true;
        }

        // Check billing note for "IN COLLECTIONS" text
        $billnote = $rowData['billnote'] ?? '';
        if (stripos($billnote, 'IN COLLECTIONS') !== false) {
            return true;
        }

        return false;
    }
}
