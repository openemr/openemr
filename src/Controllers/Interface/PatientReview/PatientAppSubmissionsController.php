<?php

/**
 * The staff screen for patient app submissions: FHIR writes held for review.
 *
 * Shows each pending write with the patient, the app and whether it is a confidential or a
 * public client, and lets staff approve it (written to the chart under the id the app was
 * given) or deny it with a reason (kept, never written).
 *
 * Viewing needs patients/med; approving or denying needs patients/med write, the same as a
 * clinician writing the record.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\PatientReview;

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Services\PatientReview\Exception\InvalidReviewTransitionException;
use OpenEMR\Services\PatientReview\Exception\ReviewRequestNotFoundException;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteClientType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteReviewItem;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteReviewQuery;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewQueueService;
use OpenEMR\Services\PatientReview\ReviewType;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;

final readonly class PatientAppSubmissionsController
{
    public const TEMPLATE = 'patient_review/patient_app_submissions.html.twig';

    public function __construct(
        private ReviewQueueService $queue,
        private PatientWriteReviewQuery $query,
        private Environment $twig,
        private SessionInterface $session,
        private LoggerInterface $logger,
    ) {
    }

    public function dispatchAction(Request $request): Response
    {
        if (!AclMain::aclCheckCore('patients', 'med')) {
            AccessDeniedHelper::denyWithTemplate(
                'ACL check failed for patients/med: Patient App Submissions',
                xl('Patient App Submissions')
            );
        }

        $notice = null;
        $error = null;
        if ($request->getMethod() === 'POST') {
            if (!CsrfUtils::verifyCsrfToken($request->request->getString('csrf_token_form'), $this->session)) {
                CsrfUtils::csrfNotVerified();
            }
            [$notice, $error] = $this->decide($request);
        }

        $pending = $this->query->pending();

        return new Response(
            $this->twig->render(self::TEMPLATE, [
                'items' => array_map(self::row(...), $pending['items']),
                'total' => $pending['total'],
                'canDecide' => self::canDecide(),
                'notice' => $notice,
                'error' => $error,
                'csrfToken' => CsrfUtils::collectCsrfToken($this->session),
            ]),
            Response::HTTP_OK,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }

    /**
     * Approve or deny one request. The CSRF token has already been checked.
     *
     * @return array{?string, ?string} notice, error
     */
    private function decide(Request $request): array
    {
        if (!self::canDecide()) {
            AccessDeniedHelper::denyWithTemplate(
                'ACL check failed for patients/med write: Patient App Submissions',
                xl('Patient App Submissions')
            );
        }
        $post = $request->request;
        $id = filter_var($post->getString('request_id'), FILTER_VALIDATE_INT);
        $action = $post->getString('action');
        $note = trim($post->getString('note'));
        if (!is_int($id) || $id <= 0 || !in_array($action, ['approve', 'deny'], true)) {
            return [null, xl('That submission could not be found.')];
        }
        if ($action === 'deny' && $note === '') {
            return [null, xl('Give a reason for denying the submission.')];
        }

        try {
            $held = $this->queue->get($id);
            if ($held->type !== ReviewType::FhirWrite) {
                return [null, xl('That submission could not be found.')];
            }
            $reviewer = $this->reviewer();
            if ($action === 'approve') {
                $this->queue->approve($id, $reviewer, $note === '' ? null : $note);
                return [xl('The submission was approved and added to the chart.'), null];
            }
            $this->queue->deny($id, $reviewer, $note);
            return [xl('The submission was denied. Nothing was added to the chart.'), null];
        } catch (ReviewRequestNotFoundException) {
            return [null, xl('That submission could not be found.')];
        } catch (InvalidReviewTransitionException) {
            return [null, xl('Someone else has already decided that submission.')];
        } catch (\RuntimeException | \LogicException $e) {
            $this->logger->error('A patient app submission could not be decided', [
                'request_id' => $id,
                'action' => $action,
                'exception' => $e,
            ]);
            return [null, xl('The submission could not be added to the chart. It is still waiting for review. See the system log for details.')];
        }
    }

    private function reviewer(): ReviewActor
    {
        $userId = $this->session->get('authUserID');
        $username = $this->session->get('authUser');
        $userId = is_int($userId) || (is_string($userId) && ctype_digit($userId)) ? intval($userId) : 0;
        if ($userId <= 0 || !is_string($username) || $username === '') {
            throw new \LogicException('No signed-in user to record as the reviewer');
        }

        return ReviewActor::user($userId, $username);
    }

    private static function canDecide(): bool
    {
        return AclMain::aclCheckCore('patients', 'med', '', 'write');
    }

    /**
     * @return array<string, mixed>
     */
    private static function row(PatientWriteReviewItem $item): array
    {
        return [
            'id' => $item->request->id,
            'received' => $item->request->createdAt->format('Y-m-d H:i'),
            'patientName' => $item->patientName,
            'pubpid' => $item->pubpid,
            'pid' => $item->request->pid,
            'resourceType' => $item->request->resourceType->value ?? '',
            'summary' => $item->request->summary,
            'appName' => $item->appName,
            'clientType' => match ($item->clientType) {
                PatientWriteClientType::Confidential => 'confidential',
                PatientWriteClientType::Public => 'public',
                null => null,
            },
            'duplicateOf' => $item->duplicateOf,
            'answers' => $item->answers,
            'resourceJson' => $item->resourceJson,
            'canApprove' => $item->canApprove,
            'questionnaireName' => $item->questionnaireName,
        ];
    }
}
