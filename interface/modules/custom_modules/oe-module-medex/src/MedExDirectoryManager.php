<?php
/**
 * MedEx Directory Manager
 * 
 * Creates and manages the MedEx directory structure for socket-based calendar replication
 * Handles directory creation, permissions, and cleanup
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018-2026 MedEx
 * @license   Proprietary - All Rights Reserved
 */

namespace OpenEMR\Modules\MedEx;

use OpenEMR\Common\Database\QueryUtils;

class MedExDirectoryManager
{
    private string $medExBaseDir;
    private string $calendarDir;
    private string $socketPath;
    private string $lockPath;
    private string $pidPath;
    
    public function __construct()
    {
        $this->medExBaseDir = $GLOBALS['OE_SITE_DIR'] . '/documents/MedEx/';
        $this->calendarDir = $this->medExBaseDir . 'medex_calendar/';
        $this->socketPath = '/tmp/medex-calendar.sock';
        $this->lockPath = '/tmp/medex-calendar.lock';
        $this->pidPath = '/tmp/medex-daemon.pid';
    }
    
    /**
     * Create all required directories for MedEx calendar system
     * 
     * @return bool Success status
     */
    public function createDirectoryStructure(): bool
    {
        try {
            error_log('[MedEx Directory] Creating directory structure...');
            
            // Create base MedEx directory
            if (!$this->createDirectory($this->medExBaseDir)) {
                throw new \Exception("Failed to create MedEx base directory: {$this->medExBaseDir}");
            }
            
            // Create calendar subdirectory
            if (!$this->createDirectory($this->calendarDir)) {
                throw new \Exception("Failed to create calendar directory: {$this->calendarDir}");
            }
            
            // Set secure permissions
            $this->setSecurePermissions($this->medExBaseDir);
            $this->setSecurePermissions($this->calendarDir);
            
            error_log('[MedEx Directory] Directory structure created successfully');
            error_log('[MedEx Directory] Base dir: ' . $this->medExBaseDir);
            error_log('[MedEx Directory] Calendar dir: ' . $this->calendarDir);
            
            return true;
            
        } catch (\Exception $e) {
            error_log('[MedEx Directory] ERROR: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create a single directory with proper permissions
     * 
     * @param string $directory
     * @return bool Success status
     */
    private function createDirectory(string $directory): bool
    {
        if (file_exists($directory)) {
            return is_dir($directory);
        }
        
        // Create with restrictive permissions first
        if (!mkdir($directory, 0750, true)) {
            error_log("[MedEx Directory] Failed to create directory: $directory");
            return false;
        }
        
        error_log("[MedEx Directory] Created directory: $directory");
        return true;
    }
    
    /**
     * Set secure permissions for directories
     * 
     * @param string $path
     * @return bool Success status
     */
    private function setSecurePermissions(string $path): bool
    {
        try {
            // Set ownership to web server user
            $webServerUser = $this->getWebServerUser();
            
            if ($webServerUser) {
                chown($path, $webServerUser);
                chgrp($path, $webServerUser);
                error_log("[MedEx Directory] Set ownership to $webServerUser for: $path");
            }
            
            // Set appropriate permissions
            if (is_dir($path)) {
                chmod($path, 0755); // rwxr-xr-x
            } else {
                chmod($path, 0644); // rw-r--r--
            }
            
            return true;
            
        } catch (\Exception $e) {
            error_log("[MedEx Directory] Permission setting failed for $path: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get web server user for proper ownership
     * 
     * @return string|null Web server user
     */
    private function getWebServerUser(): ?string
    {
        // Try common web server users
        $possibleUsers = ['www-data', 'apache', 'nginx', 'nobody', 'www'];
        
        foreach ($possibleUsers as $user) {
            if (posix_getpwnam($user)) {
                return $user;
            }
        }
        
        return null;
    }
    
    /**
     * Clean up all MedEx directories and files
     * 
     * @return bool Success status
     */
    public function cleanup(): bool
    {
        try {
            error_log('[MedEx Directory] Starting cleanup...');
            
            // Stop daemon if running
            $this->stopDaemon();
            
            // Remove socket
            if (file_exists($this->socketPath)) {
                unlink($this->socketPath);
                error_log("[MedEx Directory] Removed socket: {$this->socketPath}");
            }
            
            // Remove lock file
            if (file_exists($this->lockPath)) {
                unlink($this->lockPath);
                error_log("[MedEx Directory] Removed lock: {$this->lockPath}");
            }
            
            // Remove PID file
            if (file_exists($this->pidPath)) {
                unlink($this->pidPath);
                error_log("[MedEx Directory] Removed PID: {$this->pidPath}");
            }
            
            // Remove calendar directory
            if (file_exists($this->calendarDir)) {
                $this->recursiveDelete($this->calendarDir);
                error_log("[MedEx Directory] Removed calendar directory: {$this->calendarDir}");
            }
            
            // Remove base MedEx directory (if empty)
            if (file_exists($this->medExBaseDir)) {
                $this->recursiveDelete($this->medExBaseDir);
                error_log("[MedEx Directory] Removed base directory: {$this->medExBaseDir}");
            }
            
            error_log('[MedEx Directory] Cleanup completed');
            return true;
            
        } catch (\Exception $e) {
            error_log('[MedEx Directory] Cleanup failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Recursively delete directory
     * 
     * @param string $dir
     * @return bool Success status
     */
    private function recursiveDelete(string $dir): bool
    {
        if (!file_exists($dir)) {
            return true;
        }
        
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            
            $itemPath = $dir . DIRECTORY_SEPARATOR . $item;
            
            if (!is_dir($itemPath)) {
                unlink($itemPath);
            } else {
                $this->recursiveDelete($itemPath);
            }
        }
        
        return rmdir($dir);
    }
    
    /**
     * Stop the background daemon
     * 
     * @return bool Success status
     */
    private function stopDaemon(): bool
    {
        if (!file_exists($this->pidPath)) {
            return true; // No daemon running
        }
        
        $pid = (int)file_get_contents($this->pidPath);
        
        if ($pid > 0) {
            // Try to stop gracefully
            posix_kill($pid, SIGTERM);
            
            // Wait a bit
            sleep(2);
            
            // Force kill if still running
            if (posix_getpgid($pid) !== false) {
                posix_kill($pid, SIGKILL);
            }
            
            error_log("[MedEx Directory] Stopped daemon with PID: $pid");
        }
        
        return true;
    }
    
    /**
     * Get directory paths for external use
     * 
     * @return array Directory paths
     */
    public function getPaths(): array
    {
        return [
            'base_dir' => $this->medExBaseDir,
            'calendar_dir' => $this->calendarDir,
            'socket_path' => $this->socketPath,
            'lock_path' => $this->lockPath,
            'pid_path' => $this->pidPath,
            'appointments_file' => $this->calendarDir . 'appointments.json',
            'sync_queue_file' => $this->calendarDir . 'sync_queue.json',
            'daemon_log_file' => $this->calendarDir . 'daemon.log'
        ];
    }
    
    /**
     * Verify directory structure exists and is writable
     * 
     * @return bool Verification status
     */
    public function verifyStructure(): bool
    {
        $paths = $this->getPaths();
        
        foreach (['base_dir', 'calendar_dir'] as $dirKey) {
            if (!file_exists($paths[$dirKey]) || !is_dir($paths[$dirKey])) {
                error_log("[MedEx Directory] Missing directory: {$paths[$dirKey]}");
                return false;
            }
            
            if (!is_writable($paths[$dirKey])) {
                error_log("[MedEx Directory] Directory not writable: {$paths[$dirKey]}");
                return false;
            }
        }
        
        error_log('[MedEx Directory] Structure verification passed');
        return true;
    }
}
