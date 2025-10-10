<?php

/*
 *  package OpenEMR
 *  link    https://www.open-emr.org
 *  author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  Copyright (c) 2022-2024.
 *  All Rights Reserved
 */

namespace Juggernaut\OpenEMR\Modules\PriorAuthModule\Controller;

use Juggernaut\OpenEMR\Modules\PriorAuthModule\Service\AuthorizationService;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Kernel;

class PatientAuthManagerController
{
    private TwigContainer $twig;
    private AuthorizationService $authService;

    public function __construct(?TwigContainer $twigContainer = null)
    {
        // Set up Twig with module template path
        $modulePath = dirname(__DIR__, 2) . '/templates';
        $kernel = new Kernel();
        $this->twig = $twigContainer ?? new TwigContainer($modulePath, $kernel);
        $this->authService = new AuthorizationService();
    }

    /**
     * Main view action for patient authorization manager
     */
    public function view(): string
    {
        $templateData = $this->gatherPageVars();
        // Get Twig instance and render template
        $twig = $this->twig->getTwig();
        return $twig->render('patient_auth_manager.html.twig', $templateData);
    }

    /**
     * Process form submission and handle CRUD operations
     */
    public function processForm(): void
    {
        if (!empty($_POST['token'])) {
            if (!CsrfUtils::verifyCsrfToken($_POST["token"])) {
                CsrfUtils::csrfNotVerified();
            }

            $pid = $_SESSION['pid'] ?? null;
            if (!$pid) {
                return;
            }

            $postStartDate = $this->validateAndFormatDate($_POST['start_date']);
            $postEndDate = $this->validateAndFormatDate($_POST['end_date']);

            $this->authService->setId($_POST['id'] ?? null);
            $this->authService->setPid($pid);
            $this->authService->setAuthNum($_POST['authorization']);
            $this->authService->setInitUnits((int)$_POST['units']);
            $this->authService->setStartDate($postStartDate);
            $this->authService->setEndDate($postEndDate);
            $this->authService->setCpt($_POST['cpts']);
            $this->authService->storeAuthorizationInfo();
        }
    }

    /**
     * Gather all template variables needed for rendering
     */
    private function gatherPageVars(): array
    {
        $pid = $_SESSION['pid'] ?? null;
        // Get all authorizations for this patient
        $authorizations = [];
        if ($pid) {
            $listData = new ListAuthorizations();
            $listData->setPid($pid);
            $authList = $listData->getAllAuthorizations();
            $authorizations = $this->processAuthorizationData($authList);
        }

        return [
            'pid' => $pid,
            'authorizations' => $authorizations,
            'csrf_token' => CsrfUtils::collectCsrfToken(),
            'csrf_token_delete' => CsrfUtils::collectCsrfToken(),
            'header_assets' => ['common', 'datetime-picker'],
            'webroot' => $GLOBALS['webroot'] ?? ''
        ];
    }

    /**
     * Process authorization data for template display
     */
    private function processAuthorizationData($authList): array
    {
        $authorizations = [];
        if (!empty($authList)) {
            while ($iter = sqlFetchArray($authList)) {
                $editData = json_encode($iter);
                $used = AuthorizationService::getUnitsUsed(
                    $iter['auth_num'],
                    $iter['pid'],
                    $iter['cpt'],
                    $iter['start_date'],
                    $iter['end_date']
                );
                $remaining = $iter['init_units'] - $used;
                $initialUnits = (int)$iter['init_units'];
                if ($initialUnits > 0) {
                    $percentRemaining = round(($remaining / $initialUnits) * 100);
                } else {
                    $percentRemaining = 0;
                }

                $barColor = match (true) {
                    ($percentRemaining <= 33) => '#dc3545', // Red: Empty
                    ($percentRemaining <= 66) => '#ffc107', // Yellow: Getting low
                    default => '#4CAF50', // Green: Full/Plenty remaining
                };

                $authorizations[] = [
                    'id' => $iter['id'],
                    'auth_num' => $iter['auth_num'],
                    'init_units' => $iter['init_units'],
                    'remaining_units' => $remaining,
                    'start_date' => $iter['start_date'],
                    'end_date' => $iter['end_date'],
                    'cpt' => $iter['cpt'],
                    'percent_remaining' => $percentRemaining,
                    'bar_color' => $barColor,
                    'edit_data' => $editData
                ];
            }
        }
        return $authorizations;
    }

    /**
     * Validate and format date input
     */
    private function validateAndFormatDate($date): string
    {
        if (empty($date)) {
            return '';
        }

        $formattedDate = DateToYYYYMMDD($date);
        if ($this->isValidDate($formattedDate)) {
            return $formattedDate;
        }
        return $date; // Return original if formatting fails
    }

    /**
     * Check if date is valid
     */
    private function isValidDate($date, $format = 'Y-m-d'): bool
    {
        $dt = \DateTime::createFromFormat($format, $date);
        return $dt && $dt->format($format) === $date;
    }
}
