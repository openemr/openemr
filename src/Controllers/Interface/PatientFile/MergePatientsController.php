<?php

/**
 * MergePatientsController backs the Merge Patients page, which folds a duplicate patient chart
 * into the chart that is being kept.
 *
 * The page is reached two ways. On its own it presents two chart pickers and requires the SSN and
 * DOB of both charts to match before it will run. Reached from the duplicate manager, those checks
 * are skipped, because that report has already scored the two charts as the same person -- a pair it
 * surfaces can legitimately differ on SSN.
 *
 * That exemption is decided from the session, not from the URL. The duplicate manager records which
 * charts its report actually listed; only a pair drawn from that set skips the identity checks. The
 * pids on the query string merely prefill the pickers.
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

    /** The access control a user must hold to merge charts. */
    public const DEFAULT_ACL = ['patients', 'merge'];

    /**
     * @param array{0: string, 1: string} $requiredAcl ACL section and value gating this page.
     *                                                 Superusers pass regardless, via the
     *                                                 admin/super short circuit in aclCheckCore().
     */
    public function __construct(
        private readonly PatientMergeService $mergeService,
        private readonly Environment $twig,
        private readonly SessionInterface $session,
        private readonly array $requiredAcl = self::DEFAULT_ACL,
    ) {
    }

    public function dispatchAction(Request $request): Response
    {
        [$aclSection, $aclValue] = $this->requiredAcl;
        if (!AclMain::aclCheckCore($aclSection, $aclValue)) {
            AccessDeniedHelper::denyWithTemplate(
                "ACL check failed for $aclSection/$aclValue: Merge Patients",
                xl("Merge Patients")
            );
        }

        // Query pids prefill the pickers only. They are user supplied, so they never decide whether
        // the identity checks apply.
        $pid1 = $request->query->getInt('pid1');
        $pid2 = $request->query->getInt('pid2');

        if ($request->request->getString('form_submit') !== '') {
            CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

            $targetPid = $request->request->getInt('form_target_pid');
            $sourcePid = $request->request->getInt('form_source_pid');

            $mergeResult = $this->mergeService->merge(new PatientMergeRequest(
                targetPid: $targetPid,
                sourcePid: $sourcePid,
                skipIdentityChecks: $this->wasScoredAsDuplicate($targetPid, $sourcePid),
            ));

            // The source chart is gone once a merge succeeds, so the page reports what happened
            // instead of offering the form again.
            return $this->render(['mergeResult' => $mergeResult]);
        }

        return $this->renderForm($pid1, $pid2, $this->wasScoredAsDuplicate($pid1, $pid2));
    }

    /**
     * Were both charts listed by the duplicate report this session?
     *
     * The report writes the pids it displayed into the session
     * ({@see ManageDuplicatePatientsController::SESSION_SCORED_PIDS}). Only a pair drawn from there
     * skips the SSN/DOB safeguard, so an operator cannot bypass it for two arbitrary charts by
     * editing the query string. Anyone arriving at this page directly gets the full checks.
     */
    private function wasScoredAsDuplicate(int $targetPid, int $sourcePid): bool
    {
        if ($targetPid <= 0 || $sourcePid <= 0) {
            return false;
        }

        $scored = $this->session->get(ManageDuplicatePatientsController::SESSION_SCORED_PIDS);
        if (!is_array($scored)) {
            return false;
        }

        return in_array($targetPid, $scored, true) && in_array($sourcePid, $scored, true);
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
