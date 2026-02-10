<?php

/**
 * AMC Tracking Controller
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenEMR <dev@open-emr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\AmcTracking;

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;

class AmcTrackingController
{
    private readonly OEGlobalsBag $globalsBag;

    public function __construct(?OEGlobalsBag $globalsBag = null)
    {
        // Use provided OEGlobalsBag or get singleton with compatibility mode
        $this->globalsBag = $globalsBag ?? OEGlobalsBag::getInstance(true);
    }
    /**
     * Get form parameters from POST request with defaults
     *
     * @return array
     */
    public function getFormParameters(): array
    {
        return [
            'begin_date' => isset($_POST['form_begin_date'])
                ? DateTimeToYYYYMMDDHHMMSS(trim((string) $_POST['form_begin_date']))
                : '',
            'end_date' => isset($_POST['form_end_date'])
                ? DateTimeToYYYYMMDDHHMMSS(trim((string) $_POST['form_end_date']))
                : '',
            'rule' => isset($_POST['form_rule'])
                ? trim((string) $_POST['form_rule'])
                : '',
            'provider' => trim($_POST['form_provider'] ?? ''),
        ];
    }

    /**
     * Get list of authorized providers
     *
     * @return array
     */
    public function getProviders(): array
    {
        $query = "SELECT id, lname, fname FROM users
                  WHERE authorized = 1
                  ORDER BY lname, fname";

        $results = QueryUtils::fetchRecords($query);
        $providers = [];

        foreach ($results as $row) {
            $providers[] = [
                'id' => $row['id'],
                'lname' => $row['lname'],
                'fname' => $row['fname'],
                'display_name' => $row['lname'] . ', ' . $row['fname']
            ];
        }

        return $providers;
    }

    /**
     * Get tracking results based on rule and filters
     *
     * @param string $rule
     * @param string $begin_date
     * @param string $end_date
     * @param string $provider
     * @return array
     */
    public function getTrackingResults(
        string $rule,
        string $begin_date,
        string $end_date,
        string $provider
    ): array {
        // Use the existing amcTrackingRequest function
        $srcdir = $this->globalsBag->get('srcdir');
        require_once($srcdir . '/amc.php');

        $results = amcTrackingRequest($rule, $begin_date, $end_date, $provider);

        // Format the results for template consumption
        return $this->formatResults($results);
    }

    /**
     * Format results for template display
     *
     * @param array $results
     * @return array
     */
    private function formatResults(array $results): array
    {
        $formatted = [];

        foreach ($results as $result) {
            $formatted[] = [
                'pid' => $result['pid'],
                'lname' => $result['lname'],
                'fname' => $result['fname'],
                'date' => oeFormatDateTime($result['date'], 'global', true),
                'id' => $result['id'] ?? null,
            ];
        }

        return $formatted;
    }

    /**
     * Get rule display name
     *
     * @param string $rule
     * @return string
     */
    public function getRuleDisplayName(string $rule): string
    {
        return match ($rule) {
            'send_sum_amc' => xl('Send Summaries with Referrals'),
            'provide_rec_pat_amc' => xl('Patient Requested Medical Records'),
            'provide_sum_pat_amc' => xl('Provide Records to Patient for Visit'),
            default => xl('Unknown Rule'),
        };
    }

    /**
     * Get column header for date based on rule
     *
     * @param string $rule
     * @return string
     */
    public function getDateColumnHeader(string $rule): string
    {
        return match ($rule) {
            'send_sum_amc' => xl('Referral Date'),
            'provide_rec_pat_amc' => xl('Record Request Date'),
            'provide_sum_pat_amc' => xl('Encounter Date'),
            default => xl('Date'),
        };
    }

    /**
     * Get column header for ID based on rule
     *
     * @param string $rule
     * @return string
     */
    public function getIdColumnHeader(string $rule): string
    {
        return match ($rule) {
            'send_sum_amc' => xl('Referral ID'),
            'provide_rec_pat_amc' => '',
            'provide_sum_pat_amc' => xl('Encounter ID'),
            default => xl('ID'),
        };
    }

    /**
     * Get checkbox column header based on rule
     *
     * @param string $rule
     * @return string
     */
    public function getCheckboxColumnHeader(string $rule): string
    {
        return match ($rule) {
            'provide_rec_pat_amc' => xl('Medical Records Sent'),
            'send_sum_amc' => xl('Summary of Care Sent'),
            'provide_sum_pat_amc' => xl('Medical Summary Given'),
            default => xl('Status'),
        };
    }

    /**
     * Prepare data for Twig template
     *
     * @param array $params
     * @param bool $showResults
     * @return array
     */
    public function prepareTemplateData(array $params, bool $showResults = false): array
    {
        $data = [
            'csrf_token' => CsrfUtils::collectCsrfToken(),
            'csrf_token_raw' => CsrfUtils::collectCsrfToken(),
            'begin_date' => isset($params['begin_date'])
                ? oeFormatDateTime($params['begin_date'], 'global', true)
                : '',
            'end_date' => isset($params['end_date'])
                ? oeFormatDateTime($params['end_date'], 'global', true)
                : '',
            'rule' => $params['rule'] ?? '',
            'provider' => $params['provider'] ?? '',
            'providers' => $this->getProviders(),
            'show_results' => $showResults,
            'results' => [],
            'oemrUiSettings' => [
                'heading_title' => xl('AMC Tracking Report'),
                'include_patient_name' => false,
                'expandable' => false,
                'expandable_files' => [],
                'action' => 'conceal',
                'action_title' => '',
                'action_href' => 'amc_tracking.php',
                'show_help_icon' => false,
                'help_file_name' => ''
            ]
        ];

        // Get results if showing
        if ($showResults && !empty($params['rule'])) {
            $data['results'] = $this->getTrackingResults(
                $params['rule'],
                $params['begin_date'],
                $params['end_date'],
                $params['provider']
            );
        }

        return $data;
    }
}
