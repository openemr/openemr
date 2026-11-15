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

use Doctrine\DBAL\Exception as DbalException;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Services\PatientReview\Exception\InvalidReviewTransitionException;
use OpenEMR\Services\PatientReview\Exception\ReviewRequestChangedException;
use OpenEMR\Services\PatientReview\Exception\ReviewRequestNotFoundException;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteClientType;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteReviewItem;
use OpenEMR\Services\PatientReview\FhirWrite\PatientWriteReviewQuery;
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewQueueService;
use OpenEMR\Services\PatientReview\ReviewType;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;

final readonly class PatientAppSubmissionsController
{
    public const TEMPLATE = 'patient_review/patient_app_submissions.html.twig';

    /** Where a decision's outcome waits in the session for the page the browser is sent back to. */
    public const SESSION_OUTCOME = 'patient_app_submissions_outcome';

    /** User setting, '1' or '0': the submissions open expanded. The page saves it on Expand all or Collapse all. */
    public const SETTING_EXPANDED = 'patient_app_submissions_expand';

    /** User setting, '1' or '0': what the app sent opens shown. The page saves it when the user opens or closes it. */
    public const SETTING_SENT_SHOWN = 'patient_app_submissions_sent_expand';

    /**
     * @param bool $expanded the user's saved choice: submissions open expanded
     * @param bool $sentShown the user's saved choice: what the app sent opens shown
     */
    public function __construct(
        private ReviewQueueService $queue,
        private PatientWriteReviewQuery $query,
        private Environment $twig,
        private SessionInterface $session,
        private LoggerInterface $logger,
        private bool $expanded = true,
        private bool $sentShown = false,
    ) {
    }

    /**
     * Renders the list. A posted approve or deny is applied and then redirected back here, so
     * reloading the page doesn't post the decision again; its outcome is shown once.
     */
    public function dispatchAction(Request $request): Response
    {
        if (!AclMain::aclCheckCore('patients', 'med')) {
            AccessDeniedHelper::denyWithTemplate(
                'ACL check failed for patients/med: Patient App Submissions',
                xl('Patient App Submissions')
            );
        }

        if ($request->getMethod() === 'POST') {
            if (!CsrfUtils::verifyCsrfToken($request->request->getString('csrf_token_form'), $this->session)) {
                CsrfUtils::csrfNotVerified();
            }
            SessionUtil::setSession(self::SESSION_OUTCOME, $this->decide($request));

            return new RedirectResponse($request->getRequestUri(), Response::HTTP_SEE_OTHER);
        }
        [$notice, $error] = $this->takeOutcome();

        $pending = $this->query->pending();

        return new Response(
            $this->twig->render(self::TEMPLATE, [
                'items' => array_map(self::row(...), $pending['items']),
                'total' => $pending['total'],
                'canDecide' => self::canDecide(),
                'notice' => $notice,
                'error' => $error,
                'csrfToken' => CsrfUtils::collectCsrfToken($this->session),
                'expanded' => $this->expanded,
                'sentShown' => $this->sentShown,
                'settingExpanded' => self::SETTING_EXPANDED,
                'settingSentShown' => self::SETTING_SENT_SHOWN,
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
        // The version of the submission the page showed; an app may replace it while it waits.
        $revision = $post->getString('revision');
        if (!is_int($id) || $id <= 0 || !in_array($action, ['approve', 'deny'], true)) {
            return [null, xl('That submission could not be found.')];
        }
        if ($action === 'deny' && $note === '') {
            return [null, xl('Give a reason for denying the submission.')];
        }

        $reviewer = $this->reviewer();
        if ($reviewer === null) {
            return [null, xl('Your session has no signed-in user. Sign in again and retry.')];
        }

        $failed = $action === 'approve'
            ? xl('The submission could not be added to the chart. It is still waiting for review. See the system log for details.')
            : xl('The submission could not be denied. It is still waiting for review. See the system log for details.');
        try {
            $held = $this->queue->get($id);
            if ($held->type !== ReviewType::FhirWrite) {
                return [null, xl('That submission could not be found.')];
            }
            if ($action === 'approve') {
                $this->queue->approve($id, $reviewer, $note === '' ? null : $note, $revision);
                return [xl('The submission was approved and added to the chart.'), null];
            }
            $this->queue->deny($id, $reviewer, $note, $revision);
            return [xl('The submission was denied. Nothing was added to the chart.'), null];
        } catch (ReviewRequestNotFoundException) {
            return [null, xl('That submission could not be found.')];
        } catch (InvalidReviewTransitionException) {
            return [null, xl('That submission has already been decided.')];
        } catch (ReviewRequestChangedException) {
            return [null, xl('The app replaced that submission after this page was loaded. Nothing was decided. Review the new version below.')];
        } catch (\RuntimeException | \LogicException | DbalException $e) {
            // The transaction has rolled back, so the request is still pending.
            $this->logger->error('A patient app submission could not be decided', [
                'request_id' => $id,
                'action' => $action,
                'exception' => $e,
            ]);
            return [null, $failed];
        } catch (\Throwable $e) {
            // Anything else, an \Error included, is not turned into a message
            // (openemr.forbiddenCatchType): it is logged here with the request, then goes on to
            // the global error handler. The transaction has rolled back, so it is still pending.
            $this->logger->error('A patient app submission could not be decided', [
                'request_id' => $id,
                'action' => $action,
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * The outcome of the decision posted before the redirect, once; nulls when there is none.
     *
     * @return array{?string, ?string} notice, error
     */
    private function takeOutcome(): array
    {
        $outcome = $this->session->get(self::SESSION_OUTCOME);
        SessionUtil::unsetSession(self::SESSION_OUTCOME);
        if (!is_array($outcome)) {
            return [null, null];
        }
        $notice = $outcome[0] ?? null;
        $error = $outcome[1] ?? null;

        return [is_string($notice) ? $notice : null, is_string($error) ? $error : null];
    }

    /**
     * The signed-in user, as the queue records who decided; null when the session has none.
     */
    private function reviewer(): ?ReviewActor
    {
        $userId = $this->session->get('authUserID');
        $username = $this->session->get('authUser');
        $userId = is_int($userId) || (is_string($userId) && ctype_digit($userId)) ? intval($userId) : 0;
        if ($userId <= 0 || !is_string($username) || $username === '') {
            return null;
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
            'revision' => $item->request->revision(),
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
            'answersTooDeep' => $item->answersTooDeep,
            'resourceJson' => $item->resourceJson,
            'canApprove' => $item->canApprove,
            'questionnaireName' => $item->questionnaireName,
        ];
    }
}
