<?php
/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sun PC Solutions LLC
 * @copyright Copyright (c) 2025 Sun PC Solutions LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Setup script for the openemrAudio2Note module

namespace OpenEMR\Modules\OpenemrAudio2Note;

use OpenEMR\Common\Database\QueryUtils; // For potential DB operations if needed

class Setup
{
    public function install()
    {
        // Register a shutdown function to catch fatal errors during setup.
        register_shutdown_function(function () {
            $last_error = error_get_last();
            if ($last_error && ($last_error['type'] === E_ERROR || $last_error['type'] === E_PARSE || $last_error['type'] === E_CORE_ERROR || $last_error['type'] === E_COMPILE_ERROR)) {
                error_log("OpenemrAudio2Note Setup: FATAL ERROR caught: " . $last_error['message'] . " in " . $last_error['file'] . " on line " . $last_error['line']);
            }
        });

        // error_log("OpenemrAudio2Note Setup: install() method entered.");

        // Form registration and table creation are handled by OpenEMR's core module
        // installer (via ModuleManagerListener calling preinstall, which runs install.sql).

        // Generate and store a unique instance ID for this module installation.
        $config_row_id_to_use = null;
        $existing_instance_id = null;

        // error_log("OpenemrAudio2Note Setup: Checking for existing config row and instance UUID.");

        if (isset($GLOBALS['dbh']) && is_object($GLOBALS['dbh'])) {
            // error_log("OpenemrAudio2Note Setup: Database handle \$GLOBALS['dbh'] is available.");
            try {
                $stmt = $GLOBALS['dbh']->prepare("SELECT id, openemr_internal_random_uuid FROM audio2note_config ORDER BY id ASC LIMIT 1");
                if ($stmt) {
                    $stmt->execute();
                    // Use get_result() for mysqli, or fetchAll(PDO::FETCH_ASSOC) for PDO
                    $result = $stmt->get_result();
                    if ($result && $row = $result->fetch_assoc()) {
                        $config_row_id_to_use = $row['id'];
                        $existing_instance_id = $row['openemr_internal_random_uuid'];
                        // error_log("OpenemrAudio2Note Setup: Found existing config row ID: " . $config_row_id_to_use . " UUID: " . ($existing_instance_id ?: 'NULL'));
                    } else {
                        // error_log("OpenemrAudio2Note Setup: No existing config row found.");
                    }
                    $stmt->close();
                } else {
                    error_log("OpenemrAudio2Note Setup: Failed to prepare SELECT for existing config. DB Error: " . $GLOBALS['dbh']->error);
                }
            } catch (\Throwable $e) {
                error_log("OpenemrAudio2Note Setup: Exception during SELECT for existing config: " . $e->getMessage());
            }
        } else {
            error_log("OpenemrAudio2Note Setup: DB handle \$GLOBALS['dbh'] not available for UUID check.");
        }

        if ((!$config_row_id_to_use || $existing_instance_id === null) && isset($GLOBALS['dbh']) && is_object($GLOBALS['dbh'])) {
            // error_log("OpenemrAudio2Note Setup: Instance UUID needs to be generated/updated.");
            try {
                $data = random_bytes(16);
                // assert(strlen($data) == 16); // Not critical for production
                $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // version 4
                $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variant RFC 4122
                $generated_uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
                // error_log("OpenemrAudio2Note Setup: Generated new UUID: " . $generated_uuid);

                $affectedRows = 0;
                if ($config_row_id_to_use) { // Row exists, UUID was NULL, so UPDATE it
                    // error_log("OpenemrAudio2Note Setup: Existing config row (ID: " . $config_row_id_to_use . ") found with NULL UUID. Updating.");
                    $stmt_update = $GLOBALS['dbh']->prepare("UPDATE audio2note_config SET openemr_internal_random_uuid = ? WHERE id = ?");
                    if ($stmt_update) {
                        $stmt_update->bind_param('si', $generated_uuid, $config_row_id_to_use);
                        $stmt_update->execute();
                        $affectedRows = $stmt_update->affected_rows;
                        $stmt_update->close();
                        // error_log("OpenemrAudio2Note Setup: Attempted to update instance UUID for ID " . $config_row_id_to_use . ". Affected rows: " . $affectedRows);
                    } else {
                        error_log("OpenemrAudio2Note Setup: Failed to prepare UPDATE statement. DB Error: " . $GLOBALS['dbh']->error);
                    }
                } else { // No row exists, so INSERT
                    // error_log("OpenemrAudio2Note Setup: No config row exists. Inserting new row with UUID.");
                    $now_date = date('Y-m-d H:i:s');
                    $stmt_insert = $GLOBALS['dbh']->prepare("INSERT INTO audio2note_config (openemr_internal_random_uuid, created_at, updated_at) VALUES (?, ?, ?)");
                    if ($stmt_insert) {
                        $stmt_insert->bind_param('sss', $generated_uuid, $now_date, $now_date);
                        if ($stmt_insert->execute()) {
                            $affectedRows = $stmt_insert->affected_rows;
                        } else {
                            error_log("OpenemrAudio2Note Setup: INSERT execute failed. DB Error: " . $stmt_insert->error);
                        }
                        $stmt_insert->close();
                        // error_log("OpenemrAudio2Note Setup: Attempted to insert new instance UUID. Affected rows: " . $affectedRows);
                    } else {
                        error_log("OpenemrAudio2Note Setup: Failed to prepare INSERT statement. DB Error: " . $GLOBALS['dbh']->error);
                    }
                }

                if ($affectedRows <= 0) {
                    error_log("OpenemrAudio2Note Setup: Storing/updating instance UUID affected 0 rows or failed. DB Error: " . $GLOBALS['dbh']->error);
                }
            } catch (\Throwable $e) {
                 error_log("OpenemrAudio2Note Setup: Exception during UUID generation/storage: " . $e->getMessage());
            }
        } elseif ($existing_instance_id) {
            // error_log("OpenemrAudio2Note Setup: Instance UUID " . $existing_instance_id . " already exists in config row ID " . $config_row_id_to_use . ".");
        }

        // error_log("OpenemrAudio2Note Setup: install() method finishing.");
        // $finalCheckUuid = self::getStoredInstanceUuid(); // Debugging, can be removed
        // error_log("OpenemrAudio2Note Setup: install() final check - getStoredInstanceUuid() returned: " . ($finalCheckUuid ?: 'NULL'));
        return true;
    }

    public function upgrade($priorVersion)
    {
        // Handle module upgrades.
        return true;
    }

    public function uninstall()
    {
        // error_log("OpenemrAudio2Note Setup: uninstall() method entered.");
        // Unregistering forms is typically handled by OpenEMR core based on module state.

        // Clear sensitive/instance-specific data from audio2note_config table on uninstall.
        // The table itself might be dropped by OpenEMR's module removal process if SQL/uninstall.sql is implemented.
        $config_row_id = 1; // Assuming a single config row.
        if (isset($GLOBALS['dbh']) && is_object($GLOBALS['dbh'])) {
            sqlStatement(
                "UPDATE audio2note_config SET openemr_internal_random_uuid = NULL, " .
                "encrypted_license_key = NULL, encrypted_license_consumer_key = NULL, " . // Corrected: was encrypted_wc_consumer_key
                "encrypted_license_consumer_secret = NULL, encrypted_dlm_activation_token = NULL, " . // Corrected: was encrypted_wc_consumer_secret
                "license_status = NULL, license_expires_at = NULL, last_validation_timestamp = NULL " .
                "WHERE id = ?",
                [$config_row_id]
            );
            // $affectedRows = $GLOBALS['adodb']['db'] ? $GLOBALS['adodb']['db']->Affected_Rows() : -1;
            // error_log("OpenemrAudio2Note Setup: Attempted to clear license data from audio2note_config during uninstall. Affected rows: " . $affectedRows);
        } else {
            error_log("OpenemrAudio2Note Setup: DB handle not available to clear license data during uninstall.");
        }
        return true;
    }

    public function enable()
    {
        // error_log("OpenemrAudio2Note Setup: enable() method entered.");
        // Actions to perform when the module is enabled.
        return true;
    }

    public function disable()
    {
        // error_log("OpenemrAudio2Note Setup: disable() method entered.");
        // Actions to perform when the module is disabled.
        return true;
    }

    public function reset_module()
    {
        // error_log("OpenemrAudio2Note Setup: reset_module() method entered.");
        // Clear sensitive/instance-specific data from audio2note_config table on reset.
        $config_row_id = 1; // Assuming a single config row.
        if (isset($GLOBALS['dbh']) && is_object($GLOBALS['dbh'])) {
            $result = sqlStatement(
                "UPDATE audio2note_config SET encrypted_dlm_activation_token = NULL, " .
                "last_validation_timestamp = NULL " .
                "WHERE id = ?",
                [$config_row_id]
            );
            if ($result === false) {
                error_log("OpenemrAudio2Note Setup: reset_module - sqlStatement failed to clear instance UUID and license data. DB Error: " . ($GLOBALS['dbh']->error ?? 'Unknown DB error'));
            }
            // $affectedRows = $GLOBALS['adodb']['db'] ? $GLOBALS['adodb']['db']->Affected_Rows() : -1;
            // error_log("OpenemrAudio2Note Setup: Attempted to clear license data from audio2note_config during reset_module. Affected rows: " . $affectedRows);
        } else {
            error_log("OpenemrAudio2Note Setup: DB handle not available to clear license data during reset_module.");
        }
        return true;
    }

    /**
     * Retrieves the stored OpenEMR internal instance UUID.
     *
     * @return string|null The UUID if found, otherwise null.
     */
    public static function getStoredInstanceUuid(): ?string
    {
        $uuid = null;
        // error_log("OpenemrAudio2Note Setup::getStoredInstanceUuid: Attempting to retrieve UUID.");
        if (isset($GLOBALS['dbh']) && is_object($GLOBALS['dbh'])) {
            try {
                $stmt = $GLOBALS['dbh']->prepare("SELECT openemr_internal_random_uuid FROM audio2note_config ORDER BY id ASC LIMIT 1");
                if ($stmt) {
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result && $row = $result->fetch_assoc()) {
                        $uuid = $row['openemr_internal_random_uuid'];
                    }
                    $stmt->close();
                } else {
                     error_log("OpenemrAudio2Note Setup::getStoredInstanceUuid: Failed to prepare statement. DB Error: " . $GLOBALS['dbh']->error);
                }
            } catch (\Throwable $e) {
                error_log("OpenemrAudio2Note Setup::getStoredInstanceUuid: Exception during DB query: " . $e->getMessage());
            }

            // if ($uuid) {
            //     error_log("OpenemrAudio2Note Setup::getStoredInstanceUuid: Retrieved UUID: " . $uuid);
            // } else {
            //     error_log("OpenemrAudio2Note Setup::getStoredInstanceUuid: UUID not found in the first config row.");
            // }
        } else {
            error_log("OpenemrAudio2Note Setup::getStoredInstanceUuid: DB handle not available.");
        }
        return $uuid;
    }
}

?>
