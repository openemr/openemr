<?php

/**
 * AMC Tracking Controller
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenEMR <dev@open-emr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\AmcTracking;

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Utils\DateFormatterUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AmcTrackingController
{
    private readonly OEGlobalsBag $globalsBag;

    public function __construct(?OEGlobalsBag $globalsBag = null)
    {
        // Use provided OEGlobalsBag or get singleton with compatibility mode
        $this->globalsBag = $globalsBag ?? OEGlobalsBag::getInstance();
    }

    /**
     * Get form parameters from the request body with defaults.
     *
     * @return array{begin_date: string, end_date: string, rule: string, provider: string}
     */
    public function getFormParameters(?Request $request = null): array
    {
        $request ??= CurrentRequest::get();
        $beginDate = trim($request->request->getString('form_begin_date'));
        $endDate = trim($request->request->getString('form_end_date'));
        $rule = trim($request->request->getString('form_rule'));
        $provider = trim($request->request->getString('form_provider'));

        $beginConverted = $beginDate !== '' ? DateTimeToYYYYMMDDHHMMSS($beginDate) : '';
        $endConverted = $endDate !== '' ? DateTimeToYYYYMMDDHHMMSS($endDate) : '';

        return [
            'begin_date' => is_string($beginConverted) ? $beginConverted : '',
            'end_date' => is_string($endConverted) ? $endConverted : '',
            'rule' => $rule,
            'provider' => $provider,
        ];
    }

    /**
     * Get list of authorized providers.
     *
     * @return list<array{id: int|string, lname: string, fname: string, display_name: string}>
     */
    public function getProviders(): array
    {
        $query = "SELECT id, lname, fname FROM users
                  WHERE authorized = 1
                  ORDER BY lname, fname";

        $results = QueryUtils::fetchRecords($query);
        $providers = [];

        foreach ($results as $row) {
            $lname = is_string($row['lname'] ?? null) ? $row['lname'] : '';
            $fname = is_string($row['fname'] ?? null) ? $row['fname'] : '';
            $id = $row['id'] ?? '';
            if (!is_int($id) && !is_string($id)) {
                $id = '';
            }

            $providers[] = [
                'id' => $id,
                'lname' => $lname,
                'fname' => $fname,
                'display_name' => $lname . ', ' . $fname,
            ];
        }

        return $providers;
    }

    /**
     * Get tracking results based on rule and filters.
     *
     * @return list<array{pid: mixed, lname: string, fname: string, date: string, id: mixed}>
     */
    public function getTrackingResults(
        string $rule,
        string $begin_date,
        string $end_date,
        string $provider
    ): array {
        // Use the existing amcTrackingRequest function
        $srcdir = $this->globalsBag->getSrcDir();
        require_once($srcdir . '/amc.php');

        $rawResults = amcTrackingRequest($rule, $begin_date, $end_date, $provider);

        // Format the results for template consumption
        /** @var list<array<string, mixed>> $results */
        $results = is_array($rawResults) ? array_values($rawResults) : [];

        return $this->formatResults($results);
    }

    /**
     * Format results for template display.
     *
     * @param list<array<string, mixed>> $results
     * @return list<array{pid: mixed, lname: string, fname: string, date: string, id: mixed}>
     */
    private function formatResults(array $results): array
    {
        $formatted = [];

        foreach ($results as $result) {
            $dateRaw = $result['date'] ?? '';
            $date = is_string($dateRaw) ? $dateRaw : '';

            $formatted[] = [
                'pid' => $result['pid'] ?? null,
                'lname' => is_string($result['lname'] ?? null) ? $result['lname'] : '',
                'fname' => is_string($result['fname'] ?? null) ? $result['fname'] : '',
                'date' => DateFormatterUtils::oeFormatDateTime($date, 'global', true),
                'id' => $result['id'] ?? null,
            ];
        }

        return $formatted;
    }

    /**
     * Get rule display name.
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
     * Get column header for date based on rule.
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
     * Get column header for ID based on rule.
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
     * Get checkbox column header based on rule.
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
     * Prepare data for Twig template.
     *
     * @param array{begin_date?: string, end_date?: string, rule?: string, provider?: string} $params
     * @param SessionInterface|null $session Active session for CSRF token generation;
     *                                       defaults to the current session via SessionWrapperFactory.
     * @return array{
     *     csrf_token: string,
     *     csrf_token_raw: string,
     *     begin_date: string,
     *     end_date: string,
     *     rule: string,
     *     provider: string,
     *     providers: list<array{id: int|string, lname: string, fname: string, display_name: string}>,
     *     show_results: bool,
     *     results: list<array{pid: mixed, lname: string, fname: string, date: string, id: mixed}>,
     *     oemrUiSettings: array<string, mixed>
     * }
     */
    public function prepareTemplateData(array $params, bool $showResults = false, ?SessionInterface $session = null): array
    {
        $session ??= SessionWrapperFactory::getInstance()->getActiveSession();
        $csrfToken = CsrfUtils::collectCsrfToken($session);

        $beginDate = $params['begin_date'] ?? '';
        $endDate = $params['end_date'] ?? '';
        $rule = $params['rule'] ?? '';
        $provider = $params['provider'] ?? '';

        $data = [
            'csrf_token' => $csrfToken,
            'csrf_token_raw' => $csrfToken,
            'begin_date' => $beginDate !== ''
                ? DateFormatterUtils::oeFormatDateTime($beginDate, 'global', true)
                : '',
            'end_date' => $endDate !== ''
                ? DateFormatterUtils::oeFormatDateTime($endDate, 'global', true)
                : '',
            'rule' => $rule,
            'provider' => $provider,
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
                'help_file_name' => '',
            ],
        ];

        // Get results if showing
        if ($showResults && $rule !== '') {
            $data['results'] = $this->getTrackingResults(
                $rule,
                $beginDate,
                $endDate,
                $provider
            );
        }

        return $data;
    }
}
