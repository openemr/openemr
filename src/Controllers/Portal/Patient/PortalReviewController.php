<?php

/**
 * Portal review requests: the staff review list (Portal > Review Audits) and the document
 * screen's review activity, over the patient review queue.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Portal\Patient;

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\PortalPatientAccessGuard;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Portal\Routing\PortalPrincipal;
use OpenEMR\Portal\Routing\RouteMatch;
use OpenEMR\Services\PatientReview\Legacy\LegacyActivityRow;
use OpenEMR\Services\PatientReview\Legacy\ReviewListQuery;
use OpenEMR\Services\PatientReview\Portal\PortalReviewWorkflows;
use OpenEMR\Services\PatientReview\Portal\SessionReviewActor;
use OpenEMR\Services\PatientReview\ReviewQueueFactory;
use OpenEMR\Services\PatientReview\ReviewRequest;
use OpenEMR\Services\PatientReview\ReviewRequestMapper;
use OpenEMR\Services\PatientReview\ReviewType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class PortalReviewController
{
    private const PAGE_SIZE = 25;

    public function __construct(private PortalPageRenderer $pages)
    {
    }

    /**
     * The staff review list page: GET onsiteactivityviews.
     */
    public function ListView(Request $request, RouteMatch $route, PortalPrincipal $caller): Response
    {
        self::assertStaffReviewer();
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        if (!$session->has('authUser')) {
            return new Response(
                'Redirecting in about 5 secs. Session shared with Onsite Portal<br /> Shared session not allowed!.',
                Response::HTTP_OK,
                ['refresh' => '5;url= ./provider'],
            );
        }

        return new Response($this->pages->render('OnsiteActivityViewListView', ['cuser' => $session->get('authUser')]));
    }

    /**
     * The staff review list as JSON: GET api/onsiteactivityviews.
     */
    public function Query(Request $request, RouteMatch $route, PortalPrincipal $caller): Response
    {
        try {
            self::assertStaffReviewer();
            $query = $request->query;
            $activity = $query->getString('activity');
            $page = $query->getString('page');
            $orderBy = $query->getString('orderBy');
            $filter = $query->getString('filter');
            $status = $query->getString('status');
            $orderDesc = $query->getString('orderDesc') !== '';
            // Empty values pass through as empty strings, as RequestUtil gave them before.
            $output = (new ReviewListQuery(new ReviewRequestMapper()))->page(
                ReviewListQuery::statusFromLegacy($status),
                ReviewType::tryFrom($activity),
                $filter,
                $orderBy,
                $orderDesc,
                is_numeric($page) ? intval($page) : 1,
                self::PAGE_SIZE,
            );
            $output['orderBy'] = $orderBy;
            $output['orderDesc'] = $orderDesc;

            return PortalJson::ok($output);
        } catch (\RuntimeException | \LogicException $ex) {
            return PortalJson::exception($ex);
        }
    }

    /**
     * One request: GET api/onsiteactivityview/{id} (staff) and GET api/onsiteportalactivity/{id}
     * (the document screen, patient or staff).
     */
    public function Read(Request $request, RouteMatch $route, PortalPrincipal $caller): Response
    {
        try {
            $review = self::requestFromUrl($route);
            PortalPatientAccessGuard::assertCanRead($review->pid);

            return PortalJson::ok(LegacyActivityRow::fromRequest($review)->toModel());
        } catch (\RuntimeException | \LogicException $ex) {
            return PortalJson::exception($ex);
        }
    }

    /**
     * The document screen looks up a document's review activity:
     * GET api/onsiteportalactivities?patientId=&activity=document&doc=.
     */
    public function DocumentQuery(Request $request, RouteMatch $route, PortalPrincipal $caller): Response
    {
        try {
            $pid = self::requirePositiveInt($request->query->getString('patientId'), 'patient ID');
            PortalPatientAccessGuard::assertCanRead($pid);
            $documentId = filter_var($request->query->getString('doc'), FILTER_VALIDATE_INT);
            $latest = is_int($documentId) && $documentId > 0 ? self::workflows()->latestDocument($pid, $documentId) : null;
            $rows = $latest === null ? [] : [LegacyActivityRow::fromRequest($latest)->toModel()];

            return PortalJson::ok([
                'rows' => $rows,
                'totalResults' => count($rows),
                'totalPages' => 1,
                'pageSize' => count($rows),
                'currentPage' => 1,
            ]);
        } catch (\RuntimeException | \LogicException $ex) {
            return PortalJson::exception($ex);
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
    public function DocumentChange(Request $request, RouteMatch $route, PortalPrincipal $caller): Response
    {
        try {
            $json = json_decode($request->getContent(), true);
            if (!is_array($json)) {
                throw new \InvalidArgumentException('The request body does not contain valid JSON');
            }
            if (($json['activity'] ?? null) !== 'document') {
                throw new \InvalidArgumentException('Only document activity is recorded here');
            }
            $pid = self::requirePositiveInt($json['patientId'] ?? ($json['pid'] ?? null), 'patient ID');
            PortalPatientAccessGuard::assertCanWrite($pid);
            $documentId = self::requirePositiveInt($json['tableArgs'] ?? null, 'document ID');
            $actor = SessionReviewActor::from(SessionWrapperFactory::getInstance()->getActiveSession());
            $workflows = self::workflows();
            $status = $json['status'] ?? null;
            // Only a submission creates a request, so only it needs the document checked. Closing
            // or deleting acts on a request that was checked when it was submitted (and a deleted
            // document's row is already gone).
            if ($status === 'waiting' && !self::documentBelongsTo($documentId, $pid)) {
                throw new \InvalidArgumentException('The document does not belong to this patient');
            }
            $narrative = $json['narrative'] ?? null;
            $result = match ($status) {
                'waiting' => $workflows->submitDocument($pid, $documentId, is_string($narrative) ? $narrative : '', $actor),
                'closed' => $workflows->completeDocument($pid, $documentId, $actor),
                'deleted' => $workflows->cancelDocument(
                    $pid,
                    $documentId,
                    $actor,
                    is_string($narrative) ? $narrative : 'Patient deleted un-charted template',
                ),
                default => null,
            };
            $result ??= $workflows->latestDocument($pid, $documentId);

            // The page keeps the id it is given; with none it posts again next time, which is
            // what a document with nothing in review needs.
            return PortalJson::ok($result === null ? ['id' => null] + $json : LegacyActivityRow::fromRequest($result)->toModel());
        } catch (\RuntimeException | \LogicException $ex) {
            return PortalJson::exception($ex);
        }
    }

    private static function documentBelongsTo(int $documentId, int $pid): bool
    {
        $owner = QueryUtils::fetchSingleValue('SELECT `pid` FROM `onsite_documents` WHERE `id` = ?', 'pid', [$documentId]);

        return is_numeric($owner) && intval($owner) === $pid;
    }

    private static function workflows(): PortalReviewWorkflows
    {
        return ReviewQueueFactory::portalWorkflows();
    }

    private static function requestFromUrl(RouteMatch $route): ReviewRequest
    {
        $id = self::requirePositiveInt($route->param('id'), 'review request ID');

        return ReviewQueueFactory::create()->find($id) ?? throw new \RuntimeException('Not found');
    }

    private static function assertStaffReviewer(): void
    {
        if (!AclMain::aclCheckCore('patientportal', 'portal') || !AclMain::aclCheckCore('patients', 'demo')) {
            AccessDeniedHelper::deny('Unauthorized access to onsite activity review');
        }
    }

    private static function requirePositiveInt(mixed $value, string $what): int
    {
        $int = filter_var($value, FILTER_VALIDATE_INT);
        if (!is_int($int) || $int <= 0) {
            throw new \InvalidArgumentException('A valid ' . $what . ' is required');
        }

        return $int;
    }
}
