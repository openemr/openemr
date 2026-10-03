<?php

/**
 * GCIP Module Manager Listener
 * 
 * <!-- AI-Generated Content Start -->
 * This class handles module lifecycle events such as installation,
 * uninstallation, enabling, and disabling of the GCIP authentication
 * module, ensuring proper database setup and cleanup.
 * <!-- AI-Generated Content End -->
 *
 * @package   OpenEMR\Modules\GcipAuth
 * @link      http://www.open-emr.org
 * @author    OpenEMR Development Team
 * @copyright Copyright (c) 2024 OpenEMR Development Team
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\GcipAuth;

use OpenEMR\Events\ModuleManagerEvent;
use OpenEMR\Modules\GcipAuth\Services\GcipAuditService;

/**
 * Module Manager Listener for GCIP Authentication
 */
class ModuleManagerListener
{
    /**
     * Handle module installation - AI-Generated
     */
    public function onModuleInstall(ModuleManagerEvent $event): void
    {
        if ($event->getModuleName() !== 'oe-module-gcip-auth') {
            return;
        }

        try {
            // Run database migrations - AI-Generated
            $this->runDatabaseMigrations();
            
            // Set default configuration - AI-Generated
            $this->setDefaultConfiguration();
            
            // Log installation - AI-Generated
            $auditService = new GcipAuditService();
            $auditService->logConfigurationChange(
                $_SESSION['authUser'] ?? 'system',
                'Module Installation',
                'installed'
            );
            
            $event->setSuccess(true);
            $event->setMessage('GCIP Authentication module installed successfully');
            
        } catch (\Exception $e) {
            error_log('GCIP module installation failed: ' . $e->getMessage());
            $event->setSuccess(false);
            $event->setMessage('Installation failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle module uninstallation - AI-Generated
     */
    public function onModuleUninstall(ModuleManagerEvent $event): void
    {
        if ($event->getModuleName() !== 'oe-module-gcip-auth') {
            return;
        }

        try {
            // Log uninstallation before cleanup - AI-Generated
            $auditService = new GcipAuditService();
            $auditService->logConfigurationChange(
                $_SESSION['authUser'] ?? 'system',
                'Module Uninstallation',
                'uninstalled'
            );
            
            // Run cleanup script - AI-Generated
            $this->runCleanupScript();
            
            $event->setSuccess(true);
            $event->setMessage('GCIP Authentication module uninstalled successfully');
            
        } catch (\Exception $e) {
            error_log('GCIP module uninstallation failed: ' . $e->getMessage());
            $event->setSuccess(false);
            $event->setMessage('Uninstallation failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle module enable - AI-Generated
     */
    public function onModuleEnable(ModuleManagerEvent $event): void
    {
        if ($event->getModuleName() !== 'oe-module-gcip-auth') {
            return;
        }

        try {
            // Verify configuration before enabling - AI-Generated
            $configService = new Services\GcipConfigService();
            $validation = $configService->validateConfiguration();
            
            if (!$validation['valid']) {
                $event->setSuccess(false);
                $event->setMessage('Cannot enable module: Configuration validation failed. Please configure the module first.');
                return;
            }
            
            // Enable the module - AI-Generated
            $configService->setConfigValue('gcip_enabled', true);
            
            // Log enable event - AI-Generated
            $auditService = new GcipAuditService();
            $auditService->logConfigurationChange(
                $_SESSION['authUser'] ?? 'system',
                'Module Enable',
                'enabled'
            );
            
            $event->setSuccess(true);
            $event->setMessage('GCIP Authentication module enabled successfully');
            
        } catch (\Exception $e) {
            error_log('GCIP module enable failed: ' . $e->getMessage());
            $event->setSuccess(false);
            $event->setMessage('Enable failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle module disable - AI-Generated
     */
    public function onModuleDisable(ModuleManagerEvent $event): void
    {
        if ($event->getModuleName() !== 'oe-module-gcip-auth') {
            return;
        }

        try {
            // Disable the module - AI-Generated
            $configService = new Services\GcipConfigService();
            $configService->setConfigValue('gcip_enabled', false);
            
            // Log disable event - AI-Generated
            $auditService = new GcipAuditService();
            $auditService->logConfigurationChange(
                $_SESSION['authUser'] ?? 'system',
                'Module Disable',
                'disabled'
            );
            
            $event->setSuccess(true);
            $event->setMessage('GCIP Authentication module disabled successfully');
            
        } catch (\Exception $e) {
            error_log('GCIP module disable failed: ' . $e->getMessage());
            $event->setSuccess(false);
            $event->setMessage('Disable failed: ' . $e->getMessage());
        }
    }

    /**
     * Run database migrations - AI-Generated
     */
    private function runDatabaseMigrations(): void
    {
        $sqlFile = __DIR__ . '/sql/table.sql';
        
        if (!file_exists($sqlFile)) {
            throw new \Exception('Database migration file not found');
        }
        
        $sql = file_get_contents($sqlFile);
        if ($sql === false) {
            throw new \Exception('Failed to read database migration file');
        }
        
        // Split SQL statements and execute them - AI-Generated
        $statements = explode(';', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement) || str_starts_with($statement, '--')) {
                continue;
            }
            
            $result = sqlStatementNoLog($statement);
            if ($result === false) {
                throw new \Exception('Database migration failed: ' . sqlErrorMessage());
            }
        }
    }

    /**
     * Set default configuration values - AI-Generated
     */
    private function setDefaultConfiguration(): void
    {
        $configService = new Services\GcipConfigService();
        
        // Set default values if not already set - AI-Generated
        $defaults = [
            'gcip_enabled' => false,
            'gcip_audit_logging' => true,
            'gcip_auto_user_creation' => false,
            'gcip_default_role' => 'Clinician'
        ];
        
        foreach ($defaults as $key => $value) {
            // Only set if not already configured - AI-Generated
            $current = $configService->getConfigValue($key);
            if ($current === null) {
                $configService->setConfigValue($key, $value);
            }
        }
    }

    /**
     * Run cleanup script for uninstallation - AI-Generated
     */
    private function runCleanupScript(): void
    {
        $cleanupFile = __DIR__ . '/sql/cleanup.sql';
        
        if (!file_exists($cleanupFile)) {
            throw new \Exception('Cleanup script not found');
        }
        
        $sql = file_get_contents($cleanupFile);
        if ($sql === false) {
            throw new \Exception('Failed to read cleanup script');
        }
        
        // Split SQL statements and execute them - AI-Generated
        $statements = explode(';', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement) || str_starts_with($statement, '--')) {
                continue;
            }
            
            try {
                $result = sqlStatementNoLog($statement);
                // Continue even if some cleanup statements fail
            } catch (\Exception $e) {
                error_log('GCIP cleanup statement failed: ' . $e->getMessage());
            }
        }
    }
}