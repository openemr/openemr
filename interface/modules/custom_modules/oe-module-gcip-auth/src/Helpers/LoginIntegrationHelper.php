<?php

/**
 * GCIP Login Integration Helper
 * 
 * <!-- AI-Generated Content Start -->
 * This helper class provides methods to integrate GCIP authentication
 * into OpenEMR's existing login forms and authentication workflows,
 * adding the "Sign in with Google" functionality seamlessly.
 * <!-- AI-Generated Content End -->
 *
 * @package   OpenEMR\Modules\GcipAuth\Helpers
 * @link      http://www.open-emr.org
 * @author    OpenEMR Development Team
 * @copyright Copyright (c) 2024 OpenEMR Development Team
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\GcipAuth\Helpers;

use OpenEMR\Modules\GcipAuth\Services\GcipConfigService;

/**
 * Helper class for integrating GCIP authentication into login forms
 */
class LoginIntegrationHelper
{
    /**
     * @var GcipConfigService
     */
    private $configService;

    /**
     * LoginIntegrationHelper constructor - AI-Generated
     */
    public function __construct()
    {
        $this->configService = new GcipConfigService();
    }

    /**
     * Check if GCIP should be displayed on login forms - AI-Generated
     */
    public function shouldDisplayGcipLogin(): bool
    {
        return $this->configService->isGcipEnabled() && 
               $this->configService->validateConfiguration()['valid'];
    }

    /**
     * Generate GCIP login button HTML - AI-Generated
     */
    public function getGcipLoginButton(): string
    {
        if (!$this->shouldDisplayGcipLogin()) {
            return '';
        }

        $modulePath = $GLOBALS['webroot'] . '/interface/modules/custom_modules/oe-module-gcip-auth';
        
        return '
        <!-- AI-Generated GCIP Login Button -->
        <div class="gcip-auth-container">
            <div class="gcip-separator">
                <span>' . xlt('Or sign in with') . '</span>
            </div>
            <button type="button" class="btn gcip-login-button" id="gcip-login-btn">
                <svg class="gcip-icon" viewBox="0 0 24 24" width="18" height="18">
                    <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                ' . xlt('Sign in with Google') . '
            </button>
        </div>
        <script>
        // AI-Generated JavaScript for GCIP button functionality
        document.addEventListener("DOMContentLoaded", function() {
            const gcipBtn = document.getElementById("gcip-login-btn");
            if (gcipBtn) {
                gcipBtn.addEventListener("click", function(e) {
                    e.preventDefault();
                    window.location.href = "' . $modulePath . '/public/auth/login.php";
                });
            }
        });
        </script>
        ';
    }

    /**
     * Add GCIP authentication styles to login page - AI-Generated
     */
    public function getGcipLoginStyles(): string
    {
        if (!$this->shouldDisplayGcipLogin()) {
            return '';
        }

        return '
        <!-- AI-Generated GCIP Authentication Styles -->
        <style>
        .gcip-auth-container {
            margin: 15px 0;
            text-align: center;
        }
        .gcip-separator {
            position: relative;
            margin: 20px 0;
            text-align: center;
            color: #6c757d;
            font-size: 0.875rem;
        }
        .gcip-separator::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background-color: #dee2e6;
            z-index: 1;
        }
        .gcip-separator span {
            background-color: #fff;
            padding: 0 15px;
            position: relative;
            z-index: 2;
        }
        .gcip-login-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            background-color: #4285f4;
            color: white !important;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            min-width: 200px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .gcip-login-button:hover {
            background-color: #3367d6;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            color: white !important;
            text-decoration: none;
        }
        .gcip-icon {
            width: 18px;
            height: 18px;
            margin-right: 8px;
            flex-shrink: 0;
            fill: currentColor;
        }
        </style>
        ';
    }

    /**
     * Inject GCIP authentication into existing login form - AI-Generated
     */
    public function injectIntoLoginForm(string $formHtml): string
    {
        if (!$this->shouldDisplayGcipLogin()) {
            return $formHtml;
        }

        // Find the submit button and inject GCIP button after it - AI-Generated
        $gcipButton = $this->getGcipLoginButton();
        $gcipStyles = $this->getGcipLoginStyles();
        
        // Look for common login form patterns - AI-Generated
        $patterns = [
            '/(<input[^>]*type=["\']submit["\'][^>]*>)/',
            '/(<button[^>]*type=["\']submit["\'][^>]*>.*?<\/button>)/',
            '/(<input[^>]*value=["\'][^"\']*[Ll]ogin[^"\']*["\'][^>]*>)/'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $formHtml)) {
                $formHtml = preg_replace($pattern, '$1' . $gcipButton, $formHtml, 1);
                break;
            }
        }
        
        // Add styles to the form - AI-Generated
        $formHtml = $gcipStyles . $formHtml;
        
        return $formHtml;
    }

    /**
     * Generate GCIP authentication status indicator - AI-Generated
     */
    public function getGcipStatusIndicator(): string
    {
        // Check if current user is GCIP authenticated - AI-Generated
        if (!isset($_SESSION['gcip_authenticated']) || !$_SESSION['gcip_authenticated']) {
            return '';
        }

        $gcipEmail = $_SESSION['gcip_email'] ?? '';
        $gcipName = $_SESSION['gcip_name'] ?? '';
        
        return '
        <!-- AI-Generated GCIP Status Indicator -->
        <span class="gcip-status-indicator" title="' . xla('Authenticated via Google Cloud Identity Platform') . '">
            <i class="fa fa-google"></i>
            GCIP
        </span>
        <style>
        .gcip-status-indicator {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            background-color: #4285f4;
            color: white;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            margin-left: 8px;
        }
        .gcip-status-indicator i {
            margin-right: 4px;
            font-size: 10px;
        }
        </style>
        ';
    }

    /**
     * Handle GCIP callback error display - AI-Generated
     */
    public function handleCallbackError(): string
    {
        $error = $_GET['gcip_error'] ?? null;
        
        if (!$error) {
            return '';
        }

        // Clean up URL parameters - AI-Generated
        if (isset($_GET['gcip_error'])) {
            $cleanUrl = strtok($_SERVER['REQUEST_URI'], '?');
            $params = $_GET;
            unset($params['gcip_error']);
            
            if (!empty($params)) {
                $cleanUrl .= '?' . http_build_query($params);
            }
            
            // Use JavaScript to clean URL without page refresh - AI-Generated
            echo '<script>
                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, document.title, "' . $cleanUrl . '");
                }
            </script>';
        }

        return '
        <!-- AI-Generated GCIP Error Alert -->
        <div class="alert alert-danger gcip-error-alert" style="margin-bottom: 20px;">
            <strong>' . xlt('Authentication Error') . ':</strong> ' . xlt(htmlspecialchars($error)) . '
            <button type="button" class="close" onclick="this.parentElement.remove()" style="float: right; background: none; border: none; font-size: 20px; cursor: pointer;">
                <span>&times;</span>
            </button>
        </div>
        ';
    }

    /**
     * Get module path for assets - AI-Generated
     */
    public function getModulePath(): string
    {
        return $GLOBALS['webroot'] . '/interface/modules/custom_modules/oe-module-gcip-auth';
    }
}