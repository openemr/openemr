<?php

/**
 * JSON endpoint that returns a single email_queue row for the report modal.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025-2026 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\Reports;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Reports\Email\EmailQueueService;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class EmailQueueDetailController
{
    /**
     * @param (callable(): void)|null $authorizationGuard
     *        Optional override used by isolated tests so ACL bootstrap is not required.
     */
    public function __construct(
        private readonly EmailQueueService $service,
        private readonly SessionInterface $session,
        private readonly LoggerInterface $logger,
        private readonly mixed $authorizationGuard = null, // callable|null; mixed keeps ctor PHPDoc-friendly for PHPStan
    ) {
        if ($this->authorizationGuard !== null && !is_callable($this->authorizationGuard)) {
            throw new \InvalidArgumentException('authorizationGuard must be callable or null');
        }
    }

    public function dispatchAction(Request $request): Response
    {
        try {
            $this->authorize();
            return $this->fetchEmail($request->query->getInt('id'));
        } catch (RuntimeException $e) {
            $this->logger->error('Email queue detail fetch failed', [
                'exception' => $e,
            ]);

            return new JsonResponse(
                [
                    'success' => false,
                    'error' => 'Unable to load email details.',
                ],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Load one email_queue row after authorization has already succeeded.
     *
     * @throws RuntimeException
     */
    public function fetchEmail(int $emailId): JsonResponse
    {
        if ($emailId < 1) {
            throw new RuntimeException('Email ID is required');
        }

        $email = $this->service->getEmailById($emailId);
        if ($email === null) {
            throw new RuntimeException('Email not found');
        }

        return new JsonResponse([
            'success' => true,
            'email' => $email,
        ]);
    }

    /**
     * @throws RuntimeException
     */
    public function authorize(): void
    {
        if (is_callable($this->authorizationGuard)) {
            ($this->authorizationGuard)();
            return;
        }

        $this->assertAuthorized();
    }

    /**
     * @throws RuntimeException
     */
    public function assertAuthorized(): void
    {
        $authUserId = filter_var($this->session->get('authUserID'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        if (!is_int($authUserId)) {
            throw new RuntimeException('User not authenticated');
        }

        if (!AclMain::aclCheckCore('admin', 'super') && !AclMain::aclCheckCore('acct', 'bill')) {
            throw new RuntimeException('Access denied: insufficient permissions');
        }
    }
}
