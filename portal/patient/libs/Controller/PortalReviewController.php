<?php

/**
 * The portal review screens over the review queue.
 *
 * Replaces the two Phreeze stacks that read and wrote onsite_portal_activity directly
 * (OnsiteActivityView for the staff review list, OnsitePortalActivity for the per-document
 * activity the document screen keeps). The URLs and the JSON the existing JavaScript reads are
 * unchanged; behind them, every change goes through PortalReviewWorkflows and so through the
 * review queue's checked transitions, history and audit log.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\PortalPatientAccessGuard;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Services\PatientReview\Legacy\LegacyActivityRow;
use OpenEMR\Services\PatientReview\Legacy\ReviewListQuery;
use OpenEMR\Services\PatientReview\Portal\PortalReviewWorkflows;
use OpenEMR\Services\PatientReview\Portal\SessionReviewActor;
use OpenEMR\Services\PatientReview\ReviewQueueFactory;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewRequestMapper;
use OpenEMR\Services\PatientReview\ReviewType;

class PortalReviewController extends AppBasePortalController
{
    private ?PortalReviewWorkflows $workflows = null;

    /**
     * Staff review list page (Portal > Review Audits).
     */
    public function ListView(): void
    {
        $this->assertStaffReviewer();
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        if (!$session->has('authUser')) {
            header("refresh:5;url= ./provider");
            echo 'Redirecting in about 5 secs. Session shared with Onsite Portal<br /> Shared session not allowed!.';
            exit();
        }
        $this->Assign('cuser', $session->get('authUser'));
        $this->Render('OnsiteActivityViewListView');
    }

    /**
     * The staff review list as JSON: GET api/onsiteactivityviews.
     */
    public function Query(): void
    {
        try {
            $this->assertStaffReviewer();
            $activity = RequestUtil::Get('activity');
            $page = RequestUtil::Get('page');
            $orderBy = RequestUtil::Get('orderBy');
            $filter = RequestUtil::Get('filter');
            $output = (new ReviewListQuery(new ReviewRequestMapper()))->page(
                ReviewListQuery::statusFromLegacy(is_string(RequestUtil::Get('status')) ? RequestUtil::Get('status') : null),
                is_string($activity) ? ReviewType::tryFrom($activity) : null,
                is_string($filter) ? $filter : null,
                is_string($orderBy) ? $orderBy : null,
                RequestUtil::Get('orderDesc') != '',
                is_numeric($page) ? intval($page) : 1,
                $this->pageSize(),
            );
            $output['orderBy'] = $orderBy;
            $output['orderDesc'] = RequestUtil::Get('orderDesc') != '';

            $this->RenderJSON($output, $this->JSONPCallback());
        } catch (\RuntimeException | \LogicException $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * One request: GET api/onsiteactivityview/{id} (staff) and GET api/onsiteportalactivity/{id}
     * (the document screen, patient or staff).
     */
    public function Read(): void
    {
        try {
            $request = $this->requestFromUrl();
            PortalPatientAccessGuard::assertCanRead($request->pid);
            $this->RenderJSON(LegacyActivityRow::fromRequest($request)->toModel(), $this->JSONPCallback());
        } catch (\RuntimeException | \LogicException $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * The document screen looks up a document's review activity:
     * GET api/onsiteportalactivities?patientId=&activity=document&doc=.
     */
    public function DocumentQuery(): void
    {
        try {
            $pid = self::requirePositiveInt(RequestUtil::Get('patientId'), 'patient ID');
            PortalPatientAccessGuard::assertCanRead($pid);
            $documentId = filter_var(RequestUtil::Get('doc'), FILTER_VALIDATE_INT);
            $latest = is_int($documentId) && $documentId > 0 ? $this->workflows()->latestDocument($pid, $documentId) : null;
            $rows = $latest === null ? [] : [LegacyActivityRow::fromRequest($latest)->toModel()];

            $this->RenderJSON([
                'rows' => $rows,
                'totalResults' => count($rows),
                'totalPages' => 1,
                'pageSize' => count($rows),
                'currentPage' => 1,
            ], $this->JSONPCallback());
        } catch (\RuntimeException | \LogicException $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * The document screen reports a document's state: POST api/onsiteportalactivity and
     * PUT api/onsiteportalactivity/{id}. The status it sends says what happened:
     *
     *   waiting  the patient sent the document for review (or saved it while in review)
     *   closed   staff charted it
     *   deleted  the patient deleted it before it was charted
     *   editing  the patient is still editing; nothing is in review
     */
    public function DocumentChange(): void
    {
        try {
            $json = json_decode((string) RequestUtil::GetBody(), true);
            if (!is_array($json)) {
                throw new InvalidArgumentException('The request body does not contain valid JSON');
            }
            if (($json['activity'] ?? null) !== 'document') {
                throw new InvalidArgumentException('Only document activity is recorded here');
            }
            $pid = self::requirePositiveInt($json['patientId'] ?? ($json['pid'] ?? null), 'patient ID');
            PortalPatientAccessGuard::assertCanWrite($pid);
            $documentId = self::requirePositiveInt($json['tableArgs'] ?? null, 'document ID');
            $actor = SessionReviewActor::from(SessionWrapperFactory::getInstance()->getActiveSession());
            $workflows = $this->workflows();

            $status = $json['status'] ?? null;
            // Only a submission creates a request, so only it needs the document checked. Closing
            // or deleting acts on a request that was checked when it was submitted (and a deleted
            // document's row is already gone).
            if ($status === 'waiting' && !self::documentBelongsTo($documentId, $pid)) {
                throw new InvalidArgumentException('The document does not belong to this patient');
            }

            $result = match ($status) {
                'waiting' => $workflows->submitDocument(
                    $pid,
                    $documentId,
                    is_string($json['narrative'] ?? null) ? $json['narrative'] : '',
                    $actor,
                ),
                'closed' => $workflows->completeDocument($pid, $documentId, $actor),
                'deleted' => $workflows->cancelDocument(
                    $pid,
                    $documentId,
                    $actor,
                    is_string($json['narrative'] ?? null) ? $json['narrative'] : 'Patient deleted un-charted template',
                ),
                default => null,
            };
            $result ??= $workflows->latestDocument($pid, $documentId);

            // Backbone keeps the id it is given; with none it posts again next time, which is
            // what a document with nothing in review needs.
            $this->RenderJSON(
                $result === null ? ['id' => null] + $json : LegacyActivityRow::fromRequest($result)->toModel(),
                $this->JSONPCallback()
            );
        } catch (\RuntimeException | \LogicException $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    /**
     * DELETE api/onsiteportalactivity/{id}: cancels a document request still in review.
     */
    public function DocumentCancel(): void
    {
        try {
            $request = $this->requestFromUrl();
            PortalPatientAccessGuard::assertCanWrite($request->pid);
            if ($request->type !== ReviewType::Document) {
                throw new InvalidArgumentException('Not a document review request');
            }
            // Cancel this request, not whichever one is open for the document now.
            if (!$request->status->isOpen()) {
                throw new InvalidArgumentException('This review request is no longer open');
            }
            $actor = SessionReviewActor::from(SessionWrapperFactory::getInstance()->getActiveSession());
            ReviewQueueFactory::create()->cancel($request->id, $actor, 'Removed from review');

            $this->RenderJSON(new stdClass(), $this->JSONPCallback());
        } catch (\RuntimeException | \LogicException $ex) {
            $this->RenderExceptionJSON($ex);
        }
    }

    private static function documentBelongsTo(int $documentId, int $pid): bool
    {
        $owner = QueryUtils::fetchSingleValue('SELECT `pid` FROM `onsite_documents` WHERE `id` = ?', 'pid', [$documentId]);

        return is_numeric($owner) && intval($owner) === $pid;
    }

    private function pageSize(): int
    {
        $size = $this->GetDefaultPageSize();

        return is_int($size) && $size > 0 ? $size : 25;
    }

    private function workflows(): PortalReviewWorkflows
    {
        return $this->workflows ??= ReviewQueueFactory::portalWorkflows();
    }

    private function requestFromUrl(): ReviewRequest
    {
        $id = self::requirePositiveInt($this->GetRouter()->GetUrlParam('id'), 'review request ID');

        return ReviewQueueFactory::create()->find($id) ?? throw new RuntimeException('Not found');
    }

    private function assertStaffReviewer(): void
    {
        if (!AclMain::aclCheckCore('patientportal', 'portal') || !AclMain::aclCheckCore('patients', 'demo')) {
            AccessDeniedHelper::deny('Unauthorized access to onsite activity review');
        }
    }

    private static function requirePositiveInt(mixed $value, string $what): int
    {
        $int = filter_var($value, FILTER_VALIDATE_INT);
        if (!is_int($int) || $int <= 0) {
            throw new InvalidArgumentException('A valid ' . $what . ' is required');
        }
        return $int;
    }
}
