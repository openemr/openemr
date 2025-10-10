<?php

/*
 *  package OpenEMR
 *  link    https://www.open-emr.org
 *  author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  Copyright (c) 2022.
 *  All Rights Reserved
 */

namespace Juggernaut\OpenEMR\Modules\PriorAuthModule\Controller;

use Juggernaut\OpenEMR\Modules\PriorAuthModule\Service\AuthorizationService;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Kernel;

class ReportsController
{
    private AuthorizationService $authorizationService;
    private TwigContainer $twig;

    public function __construct(?AuthorizationService $authorizationService = null, ?TwigContainer $twigContainer = null, ?Kernel $kernel = null)
    {
        $this->authorizationService = $authorizationService ?? new AuthorizationService();
        // Set up Twig with module template path
        $modulePath = dirname(__DIR__, 2) . '/templates';
        $this->twig = $twigContainer ?? new TwigContainer($modulePath, $kernel);
    }

    /**
     * Main list report action
     */
    public function listAction(): string
    {
        // Get filter parameter
        $hideExpired = ($_GET['hide_expired'] ?? '0') === '1';

        // Get authorization data
        $patients = $this->authorizationService->listPatientAuths();
        // Process data for view
        $processedData = $this->processAuthorizationData($patients, $hideExpired);

        // Prepare data for template
        $templateData = [
            'authorizations' => $processedData['authorizations'],
            'hideExpired' => $hideExpired,
            'totalCount' => $processedData['count'],
            'chartData' => $processedData['chartData'],
            'webroot' => $GLOBALS['webroot'] ?? '',
            'header_assets' => ['common', 'opener', 'chart']  // Added chart asset
        ];

        // Render template
        $twig = $this->twig->getTwig();
        return $twig->render('reports/list_report.html.twig', $templateData);
    }

    /**
     * Process authorization data for display
     */
    private function processAuthorizationData($patients, bool $hideExpired): array
    {
        $authorizations = [];
        $count = 0;
        $totalInitialUnits = 0;
        $totalUsedUnits = 0;
        $name = '';

        while ($iter = sqlFetchArray($patients)) {
            if (!empty($iter['pid'])) {
                $pid = $iter['pid'];
            } else {
                $pid = $iter['mrn'];
            }

            $isExpired = !empty($iter['end_date']) &&
                         $iter['end_date'] !== '0000-00-00' &&
                         $iter['end_date'] < date('Y-m-d');

            // Skip expired if filtering is enabled
            if ($hideExpired && $isExpired) {
                continue;
            }

            // Count usage for this authorization
            $usedUnits = AuthorizationService::countUsageOfAuthNumber(
                $iter['auth_num'],
                $pid,
                $iter['cpt'],
                $iter['start_date'],
                $iter['end_date']
            );

            // Get insurance information
            $insurance = AuthorizationService::insuranceName($pid);

            // Calculate remaining units and percentage
            $initialUnits = (int)$iter['init_units'];
            $remainingUnits = $initialUnits - $usedUnits;
            $percentRemaining = 0;
            if ($initialUnits > 0) {
                $percentRemaining = round(($remainingUnits / $initialUnits) * 100);
            }

            // Determine progress bar color
            $barColor = match (true) {
                ($percentRemaining <= 33) => '#dc3545', // Red: Empty
                ($percentRemaining <= 66) => '#ffc107', // Yellow: Getting low
                default => '#4CAF50', // Green: Full/Plenty remaining
            };

            // Check if this is a new patient row
            $isNewPatient = ($name !== $iter['fname'] . " " . $iter['lname']);

            $authorization = [
                'pid' => $pid,
                'mrn' => $iter['mrn'] ?? $pid,
                'fname' => $iter['fname'],
                'lname' => $iter['lname'],
                'fullName' => $iter['fname'] . " " . $iter['lname'],
                'insurance' => $insurance,
                'auth_num' => $iter['auth_num'],
                'cpt' => $iter['cpt'],
                'start_date' => $iter['start_date'],
                'end_date' => $iter['end_date'],
                'init_units' => $initialUnits,
                'used_units' => $usedUnits,
                'remaining_units' => $remainingUnits,
                'percent_remaining' => $percentRemaining,
                'bar_color' => $barColor,
                'is_expired' => $isExpired,
                'is_new_patient' => $isNewPatient
            ];

            $authorizations[] = $authorization;

            // Update totals for chart (only for non-expired with auth numbers)
            if (!$isExpired && !empty($iter['auth_num'])) {
                $totalInitialUnits += $initialUnits;
                $totalUsedUnits += $usedUnits;
            }

            $name = $iter['fname'] . " " . $iter['lname'];
            $count++;
        }

        // Prepare chart data
        $totalRemaining = $totalInitialUnits - $totalUsedUnits;
        $chartData = [
            'hasData' => $totalInitialUnits > 0,
            'totalInitial' => $totalInitialUnits,
            'totalUsed' => $totalUsedUnits,
            'totalRemaining' => $totalRemaining,
            'labels' => [xlt('Units Used'), xlt('Units Remaining')],
            'data' => [$totalUsedUnits, $totalRemaining],
            'backgroundColor' => [
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 99, 132, 0.8)'
            ]
        ];

        return [
            'authorizations' => $authorizations,
            'count' => $count,
            'chartData' => $chartData
        ];
    }
}
