<?php

/**
 * Backs the Email Queue report list and filters.
 *
 * Markup lives in templates/reports/email/queue.html.twig.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025-2026 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\Reports;

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Reports\Email\EmailQueueService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class EmailQueueReportController
{
    public const TEMPLATE = 'reports/email/queue.html.twig';

    public const PAGE_SIZE = 50;

    public function __construct(
        private readonly EmailQueueService $service,
        private readonly Environment $twig,
        private readonly string $webroot,
    ) {
    }

    public function dispatchAction(Request $request): Response
    {
        if (!AclMain::aclCheckCore('admin', 'super') && !AclMain::aclCheckCore('acct', 'bill')) {
            AccessDeniedHelper::denyWithTemplate(
                'ACL check failed for admin/super or acct/bill: Email Queue Report',
                xl('Email Queue Report')
            );
        }

        return $this->render($request);
    }

    /**
     * Build the report page. Access is already checked by dispatchAction().
     */
    public function render(Request $request): Response
    {
        $filters = $this->filtersFromRequest($request);
        $currentPage = $this->pageFromRequest($request);
        $offset = ($currentPage - 1) * self::PAGE_SIZE;

        $totalCount = $this->service->getEmailQueueCount($filters);
        $totalPages = max(1, (int) ceil($totalCount / self::PAGE_SIZE));
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
            $offset = ($currentPage - 1) * self::PAGE_SIZE;
        }

        $view = [
            'statistics' => $this->service->getStatistics(),
            'templateNames' => $this->service->getTemplateNames(),
            'emails' => $this->service->getEmailQueue($filters, self::PAGE_SIZE, $offset),
            'filters' => $filters,
            'totalCount' => $totalCount,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'queryString' => $this->buildQueryString($filters),
            'webroot' => $this->webroot,
        ];

        return new Response(
            $this->twig->render(self::TEMPLATE, $view),
            Response::HTTP_OK,
            ['Content-Type' => 'text/html; charset=utf-8'],
        );
    }

    /**
     * @return array{search: string, status: string, template_name: string, date_from: string, date_to: string}
     */
    public function filtersFromRequest(Request $request): array
    {
        return [
            'search' => $request->query->getString('search'),
            'status' => $request->query->getString('status'),
            'template_name' => $request->query->getString('template_name'),
            'date_from' => $request->query->getString('date_from'),
            'date_to' => $request->query->getString('date_to'),
        ];
    }

    public function pageFromRequest(Request $request): int
    {
        $page = $request->query->getInt('page', 1);
        return $page >= 1 ? $page : 1;
    }

    /**
     * @param array<string, string> $filters
     */
    public function buildQueryString(array $filters): string
    {
        $queryParams = [];
        foreach ($filters as $key => $value) {
            if ($value !== '') {
                $queryParams[$key] = $value;
            }
        }

        return http_build_query($queryParams);
    }
}
