<?php

/**
 * ModuleManagerListener for openemrAudio2Note module.
 * Handles actions from OpenEMR's Module Manager.
 */

// Ensure globals.php is loaded if necessary, though AbstractModuleActionListener might handle it.
// require_once dirname(__FILE__, 4) . '/globals.php'; // May not be needed here

use OpenEMR\Core\AbstractModuleActionListener;
use OpenEMR\Modules\OpenemrAudio2Note\Setup; 
// Import the Setup class
// If you create a service class in src/ for your module's core logic, you might use it here.
// use OpenEMR\Modules\OpenemrAudio2Note\AudioNoteService; 

// It's crucial that this class is in the global namespace.

class ModuleManagerListener extends AbstractModuleActionListener
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Required static method for OpenEMR's Module Manager to get the module's primary namespace.
     */
    public static function getModuleNamespace(): string
    {
        return 'OpenEMR\\Modules\\OpenemrAudio2Note\\';
    }

    /**
     * Required static method for OpenEMR's Module Manager to instantiate this listener.
     */
    public static function initListenerSelf(): ModuleManagerListener
    {
        return new self();
    }

    /**
     * Entry point called by OpenEMR's Module Manager.
     */
    public function moduleManagerAction($methodName, $modId, string $currentActionStatus = 'Success'): string
    {
        // error_log("openemrAudio2Note ModuleManagerListener: moduleManagerAction called with method: " . $methodName . " for modId: " . $modId);

        $moduleRegistry = $this->getModuleRegistry($modId, 'mod_directory');
        $moduleDirectoryName = $moduleRegistry['mod_directory'] ?? null;

        if ($moduleDirectoryName !== 'openemrAudio2Note') {
            return $currentActionStatus;
        }

        // error_log("openemrAudio2Note ModuleManagerListener: Action is for our module (" . $moduleDirectoryName . "), attempting to dispatch to method: " . $methodName);

        if (method_exists($this, $methodName)) {
            return $this->$methodName($modId, $currentActionStatus);
        } else {
            // error_log("openemrAudio2Note ModuleManagerListener: Method " . $methodName . " does not exist in this listener.");
            return $currentActionStatus;
        }
    }

    private function enable($modId, $currentActionStatus): string
    {
        // error_log("openemrAudio2Note ModuleManagerListener: private enable() method entered for $modId.");
        try {
            $updateSql = "UPDATE background_services SET execute_interval = 5, active = 1 WHERE name = 'AudioToNote_Polling'";
            sqlStatement($updateSql);
            // error_log("OpenemrAudio2Note Listener: Ensured AudioToNote_Polling execute_interval is 5 and active on enable.");
        } catch (\Throwable $e) {
            error_log("OpenemrAudio2Note Listener: Failed to update AudioToNote_Polling interval/active status on enable: " . $e->getMessage());
        }
        return $currentActionStatus;
    }

    private function disable($modId, $currentActionStatus): string
    {
        // error_log("openemrAudio2Note ModuleManagerListener: private disable() method entered for $modId.");
        return $currentActionStatus;
    }

    private function unregister($modId, $currentActionStatus): string
    {
        // error_log("openemrAudio2Note ModuleManagerListener: private unregister() method entered for $modId.");
        // Logic for removing instance_uuid was moved to reset_module, which is typically called during uninstall.
        return $currentActionStatus;
    }
    
    private function reset_module($modId, $currentActionStatus): string
    {
        // error_log("openemrAudio2Note ModuleManagerListener: private reset_module() method entered for $modId.");
        if (isset($GLOBALS['dbh']) && is_object($GLOBALS['dbh'])) {
            $stmt = $GLOBALS['dbh']->prepare("DELETE FROM audio2note_config WHERE config_name = ?");
            if ($stmt) {
                $stmt->execute(['instance_uuid']);
                // $affectedRows = $GLOBALS['adodb']['db'] ? $GLOBALS['adodb']['db']->Affected_Rows() : -1;
                // error_log("OpenemrAudio2Note ModuleManagerListener: Attempted to remove instance UUID from audio2note_config during reset_module. Affected rows: " . $affectedRows);
            } else {
                error_log("OpenemrAudio2Note ModuleManagerListener: Failed to prepare statement to remove instance UUID during reset_module.");
            }
        } else {
            error_log("OpenemrAudio2Note ModuleManagerListener: DB handle not available to remove instance UUID during reset_module.");
        }
        return $currentActionStatus;
    }

    private function install_sql($modId, $currentActionStatus): string
    {
        // error_log("openemrAudio2Note ModuleManagerListener: private install_sql() method entered for $modId.");
        return $currentActionStatus;
    }

    private function upgrade_sql($modId, $currentActionStatus): string
    {
        // error_log("openemrAudio2Note ModuleManagerListener: private upgrade_sql() method entered for $modId.");
        return $currentActionStatus;
    }
    
    private function help_requested($modId, $currentActionStatus): string
    {
        // error_log("openemrAudio2Note ModuleManagerListener: private help_requested() method entered for $modId.");
        return $currentActionStatus;
    }

    private function preenable($modId, $currentActionStatus): string
    {
        // error_log("openemrAudio2Note ModuleManagerListener: private preenable() method entered for $modId.");
        return $currentActionStatus;
    }

    /**
     * Handles pre-installation tasks for the module.
     * This includes running SQL scripts and calling the module's Setup::install method.
     */
    private function preinstall($modId, $currentActionStatus): string
    {
        // error_log("openemrAudio2Note ModuleManagerListener: private preinstall() method entered for $modId.");

        $modulePath = $GLOBALS['fileroot'] . "/" . $GLOBALS['baseModDir'] . "custom_modules/openemrAudio2Note";
        $sqlFile = $modulePath . '/sql/install.sql';

        if (file_exists($sqlFile)) {
            // error_log("OpenemrAudio2Note Listener: Found install.sql at " . $sqlFile);
            $sqlContent = file_get_contents($sqlFile);

            if ($sqlContent) {
                // Remove comments before executing
                $sqlContent = preg_replace('/#IfNotRow.*?#EndIf/s', '', $sqlContent);
                $sqlContent = preg_replace('/#.*/', '', $sqlContent);
                $sqlContent = preg_replace('/--.*/', '', $sqlContent);

                $sqlStatements = array_filter(array_map('trim', explode(';', $sqlContent)));

                if (!empty($sqlStatements)) {
                    // error_log("OpenemrAudio2Note Listener: BEGIN executing SQL statements from install.sql.");
                    foreach ($sqlStatements as $statement) {
                        if (!empty($statement)) {
                            // error_log("OpenemrAudio2Note Listener: Executing SQL statement: " . $statement);
                            try {
                                sqlStatement($statement);
                                // error_log("OpenemrAudio2Note Listener: SQL statement executed successfully.");
                            } catch (\Throwable $e) {
                                error_log("OpenemrAudio2Note Listener: Failed to execute SQL statement - " . $e->getMessage() . "\nStatement: " . $statement);
                            }
                        }
                    }
                    // error_log("OpenemrAudio2Note Listener: END executing SQL statements from install.sql.");
                } else {
                    // error_log("OpenemrAudio2Note Listener: install.sql is empty or contains only comments after processing.");
                }
            } else {
                 // error_log("OpenemrAudio2Note Listener: install.sql is empty.");
            }
        } else {
             error_log("OpenemrAudio2Note Listener: install.sql not found at " . $sqlFile);
        }

        try {
            // error_log("OpenemrAudio2Note Listener: BEGIN Setup instantiation and Setup::install() call.");
            $setup = new Setup();
            // error_log("OpenemrAudio2Note Listener: Setup class instantiated successfully in preinstall().");
            // error_log("OpenemrAudio2Note Listener: Calling Setup::install() from preinstall().");
            $setup->install();
            // error_log("OpenemrAudio2Note Listener: Setup::install() completed successfully. END Setup::install() call.");
        } catch (\Throwable $e) {
            error_log("OpenemrAudio2Note Listener: FAILED to instantiate Setup class or execute install() method: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
        }

        try {
            $updateSql = "UPDATE background_services SET execute_interval = 5, active = 1 WHERE name = 'AudioToNote_Polling'";
            sqlStatement($updateSql);
            // error_log("OpenemrAudio2Note Listener: Ensured AudioToNote_Polling execute_interval is 5 and active post-preinstall.");
        } catch (\Throwable $e) {
            error_log("OpenemrAudio2Note Listener: Failed to update AudioToNote_Polling interval/active status post-preinstall: " . $e->getMessage());
        }

        // error_log("OpenemrAudio2Note Listener: preinstall() method finishing.");
        return $currentActionStatus;
    }

    private function install($modId, $currentActionStatus): string
    {
        // error_log("openemrAudio2Note ModuleManagerListener: private install() method entered for $modId. This method should no longer contain any logic.");
        // All installation logic should be in preinstall.
        return $currentActionStatus;
    }
}
