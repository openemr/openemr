<?php

/**
 * MergePatientsController backs the Merge Patients page, which folds a duplicate patient chart
 * into the chart that is being kept.
 *
 * The page is reached two ways. On its own it presents two chart pickers and requires the SSN and
 * DOB of both charts to match before it will run. Reached from the duplicate manager, which passes
 * both pids on the query string, those checks are skipped because that tool has already decided the
 * charts are the same person.
 *
 * The merge itself lives in {@see PatientMergeService}; this controller only parses the request,
 * enforces access, and renders what the service reports.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2013-2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\PatientFile;

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\Patient\PatientMergeRequest;
use OpenEMR\Services\Patient\PatientMergeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;

class MergePatientsController
{
    public const TEMPLATE = 'patient_file/merge_patients.html.twig';

    public function __construct(
        private readonly PatientMergeService $mergeService,
        private readonly Environment $twig,
        private readonly SessionInterface $session,
    ) {
    }

    public function dispatchAction(Request $request): Response
    {
        if (!AclMain::aclCheckCore('admin', 'super')) {
            AccessDeniedHelper::denyWithTemplate(
                "ACL check failed for admin/super: Merge Patients",
                xl("Merge Patients")
            );
        }

        // The duplicate manager passes both charts on the query string, and the form posts back to
        // the same URL so they survive the round trip. Their presence is what tells us the SSN/DOB
        // comparison has already been made elsewhere.
        $pid1 = $request->query->getInt('pid1');
        $pid2 = $request->query->getInt('pid2');
        $fromDuplicateManager = $pid1 > 0 && $pid2 > 0;

        if ($request->request->getString('form_submit') !== '') {
            CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

            $mergeResult = $this->mergeService->merge(new PatientMergeRequest(
                targetPid: $request->request->getInt('form_target_pid'),
                sourcePid: $request->request->getInt('form_source_pid'),
                skipIdentityChecks: $fromDuplicateManager,
            ));

            // The source chart is gone once a merge succeeds, so the page reports what happened
            // instead of offering the form again.
            return $this->render(['mergeResult' => $mergeResult]);
        }

        return $this->renderForm($pid1, $pid2, $fromDuplicateManager);
    }

    private function renderForm(int $pid1, int $pid2, bool $fromDuplicateManager): Response
    {
        $targetLabel = $this->mergeService->describePatient($pid1);
        $sourceLabel = $this->mergeService->describePatient($pid2);
        $placeholder = xl('Click to select');

        return $this->render([
            'csrfToken' => CsrfUtils::collectCsrfToken(session: $this->session),
            'pid1' => $pid1,
            'pid2' => $pid2,
            // A pid we cannot name is not a chart we should quietly submit, so it drops back to the
            // unselected state rather than being shown as a bare number.
            'targetPid' => $targetLabel === null ? 0 : $pid1,
            'sourcePid' => $sourceLabel === null ? 0 : $pid2,
            'targetLabel' => $targetLabel ?? $placeholder,
            'sourceLabel' => $sourceLabel ?? $placeholder,
            'dryRun' => !$this->mergeService->isProduction(),
            // Standalone use is the only time the page enforces the identity match itself.
            'requireIdentityMatch' => !$fromDuplicateManager,
            'mergeResult' => null,
        ]);
    }

    /**
     * @param array<string, mixed> $templateVariables
     */
    private function render(array $templateVariables): Response
    {
        return new Response(
            $this->twig->render(self::TEMPLATE, $templateVariables),
            Response::HTTP_OK,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }
}
