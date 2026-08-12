<?php

/**
 * ManageDuplicatePatientsController backs the Duplicate Patient Management report, which lists
 * charts that look like the same person and offers the actions that resolve them.
 *
 * Each group on the report is one cluster of suspected duplicates. An operator can declare the
 * cluster's chart unique, recompute its score, or hand a pair off to the Merge Patients page --
 * the merge actions are links, resolved in the browser, because merging is that page's job.
 *
 * The same report is also served as a spreadsheet; both views come from the same
 * {@see DuplicatePatientGroup} list so they cannot disagree.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @author    Ruth Moulton <ruth@muswell.me.uk>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2017-2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @copyright Copyright (c) 2026 Ruth Moulton <ruth@muswell.me.uk>
 * @copyright Copyright (c) 2026 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\PatientFile;

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\Patient\DuplicatePatientCsvWriter;
use OpenEMR\Services\Patient\DuplicatePatientGroup;
use OpenEMR\Services\Patient\DuplicatePatientService;
use Psr\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;

class ManageDuplicatePatientsController
{
    public const TEMPLATE = 'patient_file/manage_dup_patients.html.twig';

    /** Declare the group's chart not a duplicate. Handled here. */
    public const ACTION_MARK_UNIQUE = 'U';

    /** Rescore the group's chart on its own. Handled here. */
    public const ACTION_RECOMPUTE = 'R';

    /** Merge the group into this row's chart. Handled in the browser by a link to the merge page. */
    public const ACTION_MERGE_KEEP = 'MK';

    /** Merge this row's chart away into the group's chart. Also a link to the merge page. */
    public const ACTION_MERGE_DISCARD = 'MD';

    public function __construct(
        private readonly DuplicatePatientService $duplicatePatients,
        private readonly DuplicatePatientCsvWriter $csvWriter,
        private readonly Environment $twig,
        private readonly SessionInterface $session,
        private readonly ClockInterface $clock,
        private readonly string $instanceName,
    ) {
    }

    public function dispatchAction(Request $request): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);
        }

        if (!AclMain::aclCheckCore('admin', 'super')) {
            AccessDeniedHelper::denyWithTemplate(
                "ACL check failed for admin/super: Duplicate Patient Management",
                xl("Duplicate Patient Management")
            );
        }

        // Every load rescores the whole patient table. That is what makes the report trustworthy
        // after bulk imports, and it is why this page is slow on large installs.
        $this->duplicatePatients->recalculateAllScores();

        // Applied after the rescore so the operator's decision is what the report reflects.
        $this->applyRowAction($request);

        $groups = $this->duplicatePatients->findDuplicateGroups();

        if ($request->request->getString('form_csvexport') === 'CSV') {
            return $this->csvResponse($groups);
        }

        return $this->htmlResponse($groups);
    }

    private function applyRowAction(Request $request): void
    {
        $pid = $request->request->getInt('form_toppid');
        if ($pid <= 0) {
            return;
        }

        match ($request->request->getString('form_action')) {
            self::ACTION_MARK_UNIQUE => $this->duplicatePatients->markUnique($pid),
            self::ACTION_RECOMPUTE => $this->duplicatePatients->recalculateScore($pid),
            default => null,
        };
    }

    /**
     * @param list<DuplicatePatientGroup> $groups
     */
    private function htmlResponse(array $groups): Response
    {
        $html = $this->twig->render(self::TEMPLATE, [
            'csrfToken' => CsrfUtils::collectCsrfToken(session: $this->session),
            'siteId' => $this->session->get('site_id'),
            'groups' => $groups,
            // Shared with the page's JavaScript so the option values and the values this controller
            // dispatches on cannot drift apart.
            'actions' => [
                'markUnique' => self::ACTION_MARK_UNIQUE,
                'recompute' => self::ACTION_RECOMPUTE,
                'mergeKeep' => self::ACTION_MERGE_KEEP,
                'mergeDiscard' => self::ACTION_MERGE_DISCARD,
            ],
        ]);

        return new Response($html, Response::HTTP_OK, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    /**
     * @param list<DuplicatePatientGroup> $groups
     */
    private function csvResponse(array $groups): Response
    {
        $filename = $this->csvWriter->buildFilename(
            $this->instanceName,
            $this->clock->now()->format('YmdHi')
        );

        $response = new Response($this->csvWriter->write($groups), Response::HTTP_OK, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Description' => 'File Transfer',
            'Pragma' => 'public',
            'Expires' => '0',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        ]);

        // makeDisposition quotes and strips the filename, which matters because the instance name
        // is operator-supplied and ends up in this header.
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename)
        );

        return $response;
    }
}
