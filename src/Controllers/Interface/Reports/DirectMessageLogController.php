<?php

/**
 * Backs the Direct Message Log report, which pages through direct_message_log 100 rows at a time.
 *
 * The markup is templates/reports/direct_message_log/report.html.twig.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2013-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\Reports;

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\DirectMessaging\DirectMessageLogRecord;
use OpenEMR\Common\DirectMessaging\DirectMessageLogRepository;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;

class DirectMessageLogController
{
    public const TEMPLATE = 'reports/direct_message_log/report.html.twig';

    /** Rows per page. The template's Older and Newer buttons post the same step as lognext. */
    public const PAGE_SIZE = 100;

    public function __construct(
        private readonly DirectMessageLogRepository $repository,
        private readonly Environment $twig,
        private readonly SessionInterface $session,
    ) {
    }

    /**
     * @throws SqlQueryException
     */
    public function dispatchAction(Request $request): Response
    {
        if (!AclMain::aclCheckCore('admin', 'super')) {
            AccessDeniedHelper::denyWithTemplate('ACL check failed for admin/super: Direct Message Log', xl('Direct Message Log'));
        }

        if (
            $request->isMethod(Request::METHOD_POST)
            && !CsrfUtils::verifyCsrfToken($request->request->getString('csrf_token_form'), $this->session)
        ) {
            CsrfUtils::csrfNotVerified();
        }

        return $this->render($request);
    }

    /**
     * Render the page the submitted form asks for. Access and the CSRF token are already checked.
     *
     * @throws SqlQueryException
     */
    public function render(Request $request): Response
    {
        $records = $this->findPage(self::postedInt($request->request, 'logstart'), self::postedInt($request->request, 'lognext'));

        $view = [
            'csrfToken' => CsrfUtils::collectCsrfToken($this->session),
            'records' => $records,
            // The form posts the newest id on this page back as logstart, so the next
            // Older or Newer click can step from it.
            'logstart' => $records === [] ? 0 : $records[0]->id,
        ];

        return new Response(
            $this->twig->render(self::TEMPLATE, $view),
            Response::HTTP_OK,
            ['Content-Type' => 'text/html; charset=utf-8'],
        );
    }

    /**
     * The page whose newest id is $logstart + $lognext, or the newest page when that sum is zero.
     *
     * Pages are id ranges rather than offsets, so a page near deleted rows can hold fewer than
     * PAGE_SIZE rows, and stepping past either end shows an empty page.
     *
     * @return list<DirectMessageLogRecord>
     * @throws SqlQueryException
     */
    public function findPage(int $logstart, int $lognext): array
    {
        $topId = $lognext === 0 ? 0 : $logstart + $lognext;
        if ($topId === 0) {
            return $this->repository->findLatest(self::PAGE_SIZE);
        }
        return $this->repository->findByIdRange($topId - self::PAGE_SIZE + 1, $topId);
    }

    /**
     * A posted int, or 0 when the field is absent or empty. The hidden lognext field stays empty
     * until Older or Newer sets it, so Refresh posts it blank; getInt() rejects an empty string.
     *
     * @param InputBag<string> $bag
     */
    private static function postedInt(InputBag $bag, string $field): int
    {
        return $bag->getString($field) === '' ? 0 : $bag->getInt($field);
    }
}
