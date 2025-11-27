<?php

/**
 * Collections Report Header Service
 * Built with Warp Terminal
 * Generates table header configuration for the Collections Report based on filter settings.
 * This service prepares header data with Bootstrap styling for modern UI presentation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\Collections\Services;

class HeaderService
{
    /**
     * Generate table header configuration based on filter settings
     *
     * @param array $filterConfig Configuration array with filter settings
     * @return array Array of header column definitions
     */
    public function generateHeaders(array $filterConfig): array
    {
        $headers = [];
        $isInsSummary = $filterConfig['is_ins_summary'] ?? false;
        $isDueIns = $filterConfig['is_due_ins'] ?? false;
        $formAgeCols = (int)($filterConfig['form_age_cols'] ?? 0);
        $formAgeInc = (int)($filterConfig['form_age_inc'] ?? 30);

        // Insurance column (only for "Due Ins" mode)
        if ($isDueIns) {
            $headers[] = $this->createHeader('Insurance', 'left', ['text-left']);
        }

        // Name column (not in insurance summary mode)
        if (!$isInsSummary) {
            $headers[] = $this->createHeader('Name', 'left', ['text-left']);
        }

        // Optional demographic columns
        if ($filterConfig['form_cb_ssn'] ?? false) {
            $headers[] = $this->createHeader('SSN', 'left', ['text-left']);
        }

        if ($filterConfig['form_cb_dob'] ?? false) {
            $headers[] = $this->createHeader('DOB', 'left', ['text-left']);
        }

        if ($filterConfig['form_cb_pubpid'] ?? false) {
            $headers[] = $this->createHeader('ID', 'left', ['text-left']);
        }

        if ($filterConfig['form_cb_policy'] ?? false) {
            $headers[] = $this->createHeader('Policy', 'left', ['text-left']);
        }

        if ($filterConfig['form_cb_group_number'] ?? false) {
            $headers[] = $this->createHeader('Group Number', 'left', ['text-left']);
        }

        if ($filterConfig['form_cb_phone'] ?? false) {
            $headers[] = $this->createHeader('Phone', 'left', ['text-left']);
        }

        if ($filterConfig['form_cb_city'] ?? false) {
            $headers[] = $this->createHeader('City', 'left', ['text-left']);
        }

        if (($filterConfig['form_cb_ins1'] ?? false) || ($filterConfig['form_payer_id'] ?? null)) {
            $headers[] = $this->createHeader('Primary Ins', 'left', ['text-left']);
        }

        if ($filterConfig['form_provider'] ?? null) {
            $headers[] = $this->createHeader('Provider', 'left', ['text-left']);
        }

        if ($filterConfig['form_cb_referrer'] ?? false) {
            $headers[] = $this->createHeader('Referrer', 'left', ['text-left']);
        }

        // Invoice and date columns (not in insurance summary mode)
        if (!$isInsSummary) {
            $headers[] = $this->createHeader('Invoice', 'left', ['text-left']);
            $headers[] = $this->createHeader('Svc Date', 'left', ['text-left']);

            if ($filterConfig['form_cb_adate'] ?? false) {
                $headers[] = $this->createHeader('Act Date', 'left', ['text-left']);
            }
        }

        // Financial columns (always present)
        $headers[] = $this->createHeader('Charge', 'left', ['text-left']);
        $headers[] = $this->createHeader('Adjust', 'left', ['text-left']);
        $headers[] = $this->createHeader('Paid', 'left', ['text-left']);

        // Aging columns OR Balance column
        if ($formAgeCols > 0) {
            // Generate aging column headers
            for ($c = 0; $c < $formAgeCols; $c++) {
                $start = $formAgeInc * $c;

                if ($c < $formAgeCols - 1) {
                    // Not the last column: show range (e.g., "0-29", "30-59")
                    $end = ($formAgeInc * ($c + 1)) - 1;
                    $label = "{$start}-{$end}";
                } else {
                    // Last column: show "+" (e.g., "60+", "135+")
                    $label = "{$start}+";
                }

                $headers[] = $this->createHeader($label, 'left', ['text-left']);
            }
        } else {
            // Single balance column
            $headers[] = $this->createHeader('Balance', 'right', ['text-right']);
        }

        // Aging Days column
        if ($filterConfig['form_cb_idays'] ?? false) {
            $headers[] = $this->createHeader('Aging Days', 'left', ['text-left']);
        }

        // Prv and Sel columns (not in insurance summary mode)
        if (!$isInsSummary) {
            $headers[] = $this->createHeader('Prv', 'center', ['text-center']);
            $headers[] = $this->createHeader('Sel', 'center', ['text-center']);
        }

        // Error column
        if ($filterConfig['form_cb_err'] ?? false) {
            $headers[] = $this->createHeader('Error', 'left', ['text-left']);
        }

        return $headers;
    }

    /**
     * Create a header column definition
     *
     * @param string $label Column label
     * @param string $align Alignment (left, right, center)
     * @param array $cssClasses Bootstrap CSS classes
     * @return array Header definition
     */
    private function createHeader(string $label, string $align, array $cssClasses): array
    {
        return [
            'label' => $label,
            'align' => $align,
            'css_classes' => $cssClasses,
        ];
    }
}
