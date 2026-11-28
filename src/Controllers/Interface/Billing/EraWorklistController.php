<?php

/**
 * The ERA worklist: what unattended posting posted from the 835s that were received, the
 * claim payments it left for a person, and the files it did not post.
 *
 * Viewing and marking need acct/eob write, as the posting pages it links to do.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\Billing;

use OpenEMR\Billing\EraAutoPost\AutoPostFileStatus;
use OpenEMR\Billing\EraWorklist\Worklist;
use OpenEMR\Billing\EraWorklist\WorklistFilter;
use OpenEMR\Billing\EraWorklist\WorklistUser;
use OpenEMR\Billing\EraWorklist\WorklistView;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use Psr\Clock\ClockInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;

final readonly class EraWorklistController
{
    public const TEMPLATE = 'billing/era_worklist.html.twig';

    /** The most rows a view lists; the page says so when more match. */
    public const REVIEW_LIMIT = 500;
    public const CHECK_LIMIT = 50;
    public const FILE_LIMIT = 200;

    public function __construct(
        private Worklist $worklist,
        private Environment $twig,
        private SessionInterface $session,
        private ClockInterface $clock,
    ) {
    }

    /**
     * Renders the view asked for. A posted mark is stored and then redirected back here,
     * so reloading the page does not post it again.
     */
    public function dispatchAction(Request $request): Response
    {
        if (!AclMain::aclCheckCore('acct', 'eob', '', 'write')) {
            AccessDeniedHelper::denyWithTemplate('ACL check failed for acct/eob: ERA Worklist', xl('ERA Worklist'));
        }

        if ($request->isMethod(Request::METHOD_POST)) {
            if (!CsrfUtils::verifyCsrfToken($request->request->getString('csrf_token_form'), $this->session)) {
                CsrfUtils::csrfNotVerified();
            }
            $this->mark($request);

            return new RedirectResponse($request->getRequestUri(), Response::HTTP_SEE_OTHER);
        }

        return $this->show($request);
    }

    /**
     * Set or clear the "worked" mark the posted form names, or take the file it names from
     * unattended posting. Access and the CSRF token are already checked.
     *
     * @return bool false when nothing was changed: the form named no row, the row is not on
     *              the worklist, or it was already as asked
     */
    public function mark(Request $request): bool
    {
        $post = $request->request;
        $id = filter_var($post->getString('id'), FILTER_VALIDATE_INT);
        $userId = $this->session->get('authUserID');
        $userId = is_int($userId) || (is_string($userId) && ctype_digit($userId)) ? intval($userId) : 0;
        if (!is_int($id) || $id <= 0 || $userId <= 0) {
            return false;
        }
        $by = new WorklistUser($userId);

        return match ([$post->getString('kind'), $post->getString('action')]) {
            ['claim', 'worked'] => $this->worklist->markClaimWorked($id, $by, $this->clock->now()),
            ['claim', 'clear'] => $this->worklist->clearClaimWorked($id),
            ['file', 'worked'] => $this->markFile($id, $by, $post->getString('state'), $post->getString('held')),
            ['file', 'clear'] => $this->worklist->clearFileWorked($id),
            ['file', 'take'] => $this->takeFile($id, $post->getString('state')),
            default => false,
        };
    }

    /**
     * @param string $state the unattended posting state the file was shown in, '' when it had none
     * @param string $held  how many held checks the file was shown with
     */
    private function markFile(int $id, WorklistUser $by, string $state, string $held): bool
    {
        $seen = AutoPostFileStatus::tryFrom($state);
        $heldSeen = filter_var($held, FILTER_VALIDATE_INT);
        if (($seen === null && $state !== '') || !is_int($heldSeen) || $heldSeen < 0) {
            return false;
        }

        return $this->worklist->markFileWorked($id, $by, $this->clock->now(), $seen, $heldSeen);
    }

    /**
     * @param string $state the unattended posting state the file was shown in
     */
    private function takeFile(int $id, string $state): bool
    {
        $seen = AutoPostFileStatus::tryFrom($state);

        return $seen !== null && $this->worklist->takeFromUnattended($id, $seen);
    }

    /**
     * Render the view the query string asks for. Access is already checked.
     */
    public function show(Request $request): Response
    {
        $query = $request->query;
        $view = WorklistView::tryFrom($query->getString('view')) ?? WorklistView::Review;
        $filter = WorklistFilter::fromQuery($query);
        $limit = match ($view) {
            WorklistView::Review => self::REVIEW_LIMIT,
            WorklistView::Posted => self::CHECK_LIMIT,
            WorklistView::Files => self::FILE_LIMIT,
        };
        $page = match ($view) {
            WorklistView::Review => $this->worklist->reviewClaims($filter, $limit),
            WorklistView::Posted => $this->worklist->postedChecks($filter, $limit),
            WorklistView::Files => $this->worklist->receivedFiles($filter, $limit),
        };
        // What the view's filter form sends, so a mark posts back to the same list.
        $shown = array_filter([
            'view' => $view->value,
            'payer' => $filter->payerName,
            'check' => $filter->checkNumber,
            'from' => $filter->from?->format('Y-m-d') ?? '',
            'to' => $filter->to?->format('Y-m-d') ?? '',
            'reason' => $filter->reasonValue(),
            'worked' => $filter->showWorked ? '1' : '',
        ], static fn(string $value): bool => $value !== '');

        return new Response(
            $this->twig->render(self::TEMPLATE, [
                'view' => $view->value,
                'shown' => $shown,
                'selfQuery' => http_build_query($shown),
                'rows' => $page->rows,
                'total' => $page->total,
                'limit' => $limit,
                'counts' => $this->worklist->openCounts(),
                'payers' => $view === WorklistView::Files ? [] : $this->worklist->payerNames(),
                'reasons' => WorklistFilter::reviewReasons(),
                // The pay date the posting window uses for a check that carries none.
                'today' => $this->clock->now()->format('Y-m-d'),
                'csrfToken' => CsrfUtils::collectCsrfToken($this->session),
            ]),
            Response::HTTP_OK,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }
}
