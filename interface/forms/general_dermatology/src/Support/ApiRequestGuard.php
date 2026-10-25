<?php

declare(strict_types=1);

namespace GeneralDermatology\Support;

use OpenEMR\Common\Csrf\CsrfUtils;

/**
 * Shared auth/CSRF checks for dermatology JSON/POST endpoints.
 */
final class ApiRequestGuard
{
    /**
     * Verify session exists and CSRF token is valid for API-style requests.
     *
     * Token may arrive as:
     * - JSON body key csrf_token_form
     * - POST field csrf_token_form
     * - header X-CSRF-Token
     *
     * @param array<string, mixed>|null $jsonBody
     */
    public static function assertCsrf(FormContext $context, ?array $jsonBody = null): void
    {
        $session = $context->session();
        $token = null;

        if (is_array($jsonBody) && isset($jsonBody['csrf_token_form'])) {
            $token = $jsonBody['csrf_token_form'];
        } elseif (isset($_POST['csrf_token_form'])) {
            $token = $_POST['csrf_token_form'];
        } elseif (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        if (!is_string($token) || !CsrfUtils::verifyCsrfToken($token, $session)) {
            CsrfUtils::csrfNotVerified(toScreen: false);
        }
    }

    public static function requireAuthenticatedSession(FormContext $context): void
    {
        $authUser = $context->authUser();
        if ($authUser === '') {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Authentication required']);
            exit;
        }
    }
}
