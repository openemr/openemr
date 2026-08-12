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
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Events\Patient\DuplicatePatientReportColumnsEvent;
use OpenEMR\Services\Patient\DuplicatePatientAction;
use OpenEMR\Services\Patient\DuplicatePatientColumn;
use OpenEMR\Services\Patient\DuplicatePatientCsvWriter;
use OpenEMR\Services\Patient\DuplicatePatientGroup;
use OpenEMR\Services\Patient\DuplicatePatientService;
use Psr\Clock\ClockInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;

class ManageDuplicatePatientsController
{
    public const TEMPLATE = 'patient_file/manage_dup_patients.html.twig';

    /**
     * Session key holding the pids this report last listed.
     *
     * {@see MergePatientsController} reads it to decide whether a pair may skip the SSN/DOB
     * safeguard: only charts this report actually scored as duplicates qualify.
     */
    public const SESSION_SCORED_PIDS = 'duplicate_patient_scored_pids';

    /** The access control a user must hold to work through duplicate charts. */
    public const DEFAULT_ACL = ['patients', 'merge'];

    /**
     * @param array{0: string, 1: string} $requiredAcl ACL section and value gating this page.
     *                                                 Superusers pass regardless, via the
     *                                                 admin/super short circuit in aclCheckCore().
     */
    public function __construct(
        private readonly DuplicatePatientService $duplicatePatients,
        private readonly DuplicatePatientCsvWriter $csvWriter,
        private readonly Environment $twig,
        private readonly SessionInterface $session,
        private readonly ClockInterface $clock,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly string $instanceName,
        private readonly array $requiredAcl = self::DEFAULT_ACL,
        private readonly bool $rescoreOnLoad = true,
    ) {
    }

    public function dispatchAction(Request $request): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);
        }

        [$aclSection, $aclValue] = $this->requiredAcl;
        if (!AclMain::aclCheckCore($aclSection, $aclValue)) {
            AccessDeniedHelper::denyWithTemplate(
                "ACL check failed for $aclSection/$aclValue: Duplicate Patient Management",
                xl("Duplicate Patient Management")
            );
        }

        // Rescoring the whole patient table is what makes the report trustworthy after a bulk
        // import, and it is why this page is slow on large installs. A deployment that keeps scores
        // current on demographics changes can turn it off and drive it from the Recalculate button.
        //
        // The pass parks every non-unique chart at SCORE_PENDING before it starts working through
        // them, so a request killed by max_execution_time would leave those charts below the
        // display threshold and silently missing from the report. Lifting the limit keeps the pass
        // atomic from the operator's point of view -- the same thing merge_patients.php does for
        // the same reason.
        if ($this->rescoreOnLoad) {
            set_time_limit(0);
            $this->duplicatePatients->recalculateAllScores();
        }

        // Applied after the rescore so the operator's decision is what the report reflects.
        $this->applyRowAction($request);

        $columns = $this->resolveColumns();
        $groups = $this->duplicatePatients->findDuplicateGroups($columns);
        $this->rememberScoredPids($groups);

        if ($request->request->getString('form_csvexport') === 'CSV') {
            return $this->csvResponse($groups, $columns);
        }

        return $this->htmlResponse($groups, $columns);
    }

    private function applyRowAction(Request $request): void
    {
        $pid = $request->request->getInt('form_toppid');
        if ($pid <= 0) {
            return;
        }

        match (DuplicatePatientAction::tryFrom($request->request->getString('form_action'))) {
            DuplicatePatientAction::MarkUnique => $this->duplicatePatients->markUnique($pid),
            DuplicatePatientAction::Recompute => $this->duplicatePatients->recalculateScore($pid),
            // The merge actions never reach the server: the page turns them into links.
            DuplicatePatientAction::MergeKeep,
            DuplicatePatientAction::MergeDiscard,
            null => null,
        };
    }

    /**
     * Record which charts this report listed, so the merge page can tell a genuine duplicate pair
     * from two charts an operator simply named in the URL.
     *
     * @param list<DuplicatePatientGroup> $groups
     */
    private function rememberScoredPids(array $groups): void
    {
        $pids = [];
        foreach ($groups as $group) {
            foreach ($group->getRows() as $row) {
                $pids[] = $row->pid;
            }
        }

        // SessionUtil rather than $session->set(): OpenEMR serves pages with the session closed for
        // reading, so a direct write is silently dropped -- and the merge page's safeguard depends
        // on this landing.
        SessionUtil::setSession(self::SESSION_SCORED_PIDS, array_values(array_unique($pids)));
    }

    /**
     * Let modules add, remove and reorder the report's columns.
     *
     * @return list<DuplicatePatientColumn>
     */
    private function resolveColumns(): array
    {
        $event = new DuplicatePatientReportColumnsEvent(DuplicatePatientColumn::defaults());
        $this->eventDispatcher->dispatch($event, DuplicatePatientReportColumnsEvent::EVENT_NAME);

        return $event->getColumns();
    }

    /**
     * @param list<DuplicatePatientGroup>  $groups
     * @param list<DuplicatePatientColumn> $columns
     */
    private function htmlResponse(array $groups, array $columns): Response
    {
        $html = $this->twig->render(self::TEMPLATE, [
            'csrfToken' => CsrfUtils::collectCsrfToken(session: $this->session),
            'siteId' => $this->session->get('site_id'),
            'groups' => $groups,
            'columns' => $columns,
            // Shared with the page's JavaScript so the option values and the values this controller
            // dispatches on cannot drift apart.
            'actions' => DuplicatePatientAction::forTemplate(),
        ]);

        return new Response($html, Response::HTTP_OK, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    /**
     * @param list<DuplicatePatientGroup>  $groups
     * @param list<DuplicatePatientColumn> $columns
     */
    private function csvResponse(array $groups, array $columns): Response
    {
        $filename = $this->csvWriter->buildFilename(
            $this->instanceName,
            $this->clock->now()->format('YmdHi')
        );

        $response = new Response($this->csvWriter->write($groups, $columns), Response::HTTP_OK, [
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
