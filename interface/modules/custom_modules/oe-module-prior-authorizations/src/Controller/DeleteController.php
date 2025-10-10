<?php

/*
 *  package OpenEMR
 *  link    https://www.open-emr.org
 *  author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  Copyright (c) 2022-2024.
 *  All Rights Reserved
 */

namespace Juggernaut\OpenEMR\Modules\PriorAuthModule\Controller;

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Kernel;

class DeleteController
{
    private readonly TwigContainer $twig;
    private const MODULE_TABLE = 'module_prior_authorizations';

    public function __construct(?TwigContainer $twigContainer = null)
    {
        // Set up Twig with module template path
        $modulePath = dirname(__DIR__, 2) . '/templates';
        $kernel = new Kernel();
        $this->twig = $twigContainer ?? new TwigContainer($modulePath, $kernel);
    }

    /**
     * Main delete action
     */
    public function deleteAction(): string
    {
        $result = $this->processDelete();
        // Prepare template data
        $templateData = [
            'success' => $result['success'],
            'error' => !$result['success'],
            'error_message' => $result['error_message'] ?? '',
            'header_assets' => ['common']
        ];

        // Render template
        $twig = $this->twig->getTwig();
        return $twig->render('delete_confirmation.html.twig', $templateData);
    }

    /**
     * Process the deletion with all security checks
     */
    private function processDelete(): array
    {
        try {
            // Check ACL permissions
            if (!AclMain::aclCheckCore('admin', 'practice')) {
                error_log("Prior Auth Delete: Unauthorized access attempt from user");
                return [
                    'success' => false,
                    'error_message' => xlt('Unauthorized: You do not have permission to delete records.')
                ];
            }

            // Verify CSRF token
            $csrfToken = $_GET['csrf_token_form'] ?? '';
            if (!CsrfUtils::verifyCsrfToken($csrfToken)) {
                error_log("Prior Auth Delete: CSRF token verification failed");
                return [
                    'success' => false,
                    'error_message' => xlt('Security validation failed. Please try again.')
                ];
            }

            // Validate ID parameter
            $recordId = $_GET['id'] ?? '';
            if (empty($recordId) || !is_numeric($recordId)) {
                error_log("Prior Auth Delete: Invalid or missing record ID: " . $recordId);
                return [
                    'success' => false,
                    'error_message' => xlt('Invalid record ID provided.')
                ];
            }

            // Check if record exists before attempting deletion
            $existingRecord = sqlQuery(
                "SELECT id FROM " . self::MODULE_TABLE . " WHERE id = ?",
                [(int)$recordId]
            );

            if (!$existingRecord) {
                error_log("Prior Auth Delete: Record not found with ID: " . $recordId);
                return [
                    'success' => false,
                    'error_message' => xlt('Record not found or already deleted.')
                ];
            }

            // Perform the deletion
            $result = sqlQuery(
                "DELETE FROM " . self::MODULE_TABLE . " WHERE id = ?",
                [(int)$recordId]
            );

            if ($result === false) {
                error_log("Prior Auth Delete: Database deletion failed for ID: " . $recordId);
                return [
                    'success' => false,
                    'error_message' => xlt('Database error occurred while deleting the record.')
                ];
            }

            // Log successful deletion for audit purposes
            error_log("Prior Auth Delete: Successfully deleted record ID: " . $recordId . " by user");

            return [
                'success' => true
            ];
        } catch (\Exception $e) {
            error_log("Prior Auth Delete: Exception occurred - " . $e->getMessage());
            return [
                'success' => false,
                'error_message' => xlt('An unexpected error occurred. Please try again.')
            ];
        }
    }

    /**
     * Validate that the record belongs to the current user's accessible patients (if needed)
     * This could be extended for additional security checks
     */
    private function validateRecordAccess(int $recordId): bool
    {
        // For now, we rely on ACL permissions
        // This could be extended to check patient access rights if needed
        return true;
    }
}
