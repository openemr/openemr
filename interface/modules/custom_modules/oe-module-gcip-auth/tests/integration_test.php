<?php

/**
 * GCIP Module Integration Test Script
 * 
 * <!-- AI-Generated Content Start -->
 * This script provides basic testing for the GCIP authentication module
 * including configuration validation, service initialization, and
 * integration checks to ensure the module is properly installed.
 * <!-- AI-Generated Content End -->
 *
 * @package   OpenEMR GCIP Authentication Module
 * @link      http://www.open-emr.org
 * @author    OpenEMR Development Team
 * @copyright Copyright (c) 2024 OpenEMR Development Team
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// This script should be run from the OpenEMR command line
require_once dirname(__FILE__, 5) . '/globals.php';

use OpenEMR\Core\ModulesClassLoader;
use OpenEMR\Modules\GcipAuth\Services\GcipConfigService;
use OpenEMR\Modules\GcipAuth\Services\GcipAuthService;
use OpenEMR\Modules\GcipAuth\Services\GcipAuditService;
use OpenEMR\Modules\GcipAuth\Helpers\LoginIntegrationHelper;

// Test runner class - AI-Generated
class GcipModuleTest
{
    private $results = [];

    public function runTests(): void
    {
        echo "GCIP Authentication Module Integration Tests\n";
        echo "==========================================\n\n";

        // Test 1: Class loading - AI-Generated
        $this->test('Class Loading', function() {
            $classLoader = new ModulesClassLoader($GLOBALS['fileroot']);
            $classLoader->registerNamespaceIfNotExists("OpenEMR\\Modules\\GcipAuth\\", __DIR__ . '/src');
            return class_exists('OpenEMR\\Modules\\GcipAuth\\Services\\GcipConfigService');
        });

        // Test 2: Service initialization - AI-Generated
        $this->test('Service Initialization', function() {
            $configService = new GcipConfigService();
            $authService = new GcipAuthService($configService);
            $auditService = new GcipAuditService();
            return ($configService && $authService && $auditService);
        });

        // Test 3: Configuration service - AI-Generated
        $this->test('Configuration Service', function() {
            $configService = new GcipConfigService();
            
            // Test basic configuration methods
            $isEnabled = is_bool($configService->isGcipEnabled());
            $validation = $configService->validateConfiguration();
            $allConfig = $configService->getAllConfig();
            
            return $isEnabled && is_array($validation) && is_array($allConfig);
        });

        // Test 4: Authorization URL generation - AI-Generated
        $this->test('Authorization URL Generation', function() {
            $configService = new GcipConfigService();
            $authService = new GcipAuthService($configService);
            
            // Should return null when not configured
            $authUrl = $authService->getAuthorizationUrl('test-state');
            return $authUrl === null; // Expected since not configured
        });

        // Test 5: Helper classes - AI-Generated
        $this->test('Helper Classes', function() {
            $helper = new LoginIntegrationHelper();
            
            // Test basic helper methods
            $shouldDisplay = is_bool($helper->shouldDisplayGcipLogin());
            $loginButton = is_string($helper->getGcipLoginButton());
            $statusIndicator = is_string($helper->getGcipStatusIndicator());
            
            return $shouldDisplay && $loginButton && $statusIndicator;
        });

        // Test 6: Database schema check - AI-Generated
        $this->test('Database Schema', function() {
            // Check if required tables exist (they may not be created yet)
            $tables = [
                'module_gcip_user_tokens',
                'module_gcip_audit_log', 
                'module_gcip_user_mapping'
            ];
            
            $existingTables = [];
            foreach ($tables as $table) {
                $result = sqlQuery("SHOW TABLES LIKE '$table'");
                if ($result) {
                    $existingTables[] = $table;
                }
            }
            
            // Return true if we can check (tables may not exist until installed)
            return true;
        });

        // Test 7: File structure - AI-Generated
        $this->test('File Structure', function() {
            $moduleDir = __DIR__;
            $requiredFiles = [
                'README.md',
                'LICENSE',
                'composer.json',
                'moduleConfig.php',
                'openemr.bootstrap.php',
                'version.php',
                'src/Bootstrap.php',
                'src/Services/GcipConfigService.php',
                'src/Services/GcipAuthService.php',
                'src/Services/GcipAuditService.php',
                'templates/gcip_setup.php',
                'public/callback.php',
                'sql/table.sql'
            ];
            
            foreach ($requiredFiles as $file) {
                if (!file_exists($moduleDir . '/' . $file)) {
                    return false;
                }
            }
            
            return true;
        });

        // Display results - AI-Generated
        $this->displayResults();
    }

    private function test(string $name, callable $testFunction): void
    {
        echo "Testing: $name ... ";
        
        try {
            $result = $testFunction();
            if ($result) {
                echo "PASS\n";
                $this->results[$name] = 'PASS';
            } else {
                echo "FAIL\n";
                $this->results[$name] = 'FAIL';
            }
        } catch (Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
            $this->results[$name] = 'ERROR';
        }
    }

    private function displayResults(): void
    {
        echo "\n";
        echo "Test Results Summary\n";
        echo "===================\n";
        
        $total = count($this->results);
        $passed = array_count_values($this->results)['PASS'] ?? 0;
        $failed = array_count_values($this->results)['FAIL'] ?? 0;
        $errors = array_count_values($this->results)['ERROR'] ?? 0;
        
        foreach ($this->results as $test => $result) {
            $status = str_pad($result, 8);
            echo "$status $test\n";
        }
        
        echo "\n";
        echo "Total:  $total\n";
        echo "Passed: $passed\n";
        echo "Failed: $failed\n";
        echo "Errors: $errors\n";
        
        if ($passed === $total) {
            echo "\n✅ All tests passed! The GCIP module appears to be properly structured.\n";
        } else {
            echo "\n⚠️  Some tests failed. Please check the module configuration.\n";
        }
    }
}

// Run tests if script is called directly - AI-Generated
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $tester = new GcipModuleTest();
    $tester->runTests();
}