<?php
/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\Service;

use CouchDB;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

class Globals
{

    private $request;

    private $globals;

    private $fs;

    public function __construct($globals, Filesystem $fs)
    {
        $this->globals = $globals;
        $this->fs = $fs;
    }

    public function saveUserSettings(Request $request, $globalsMeta, $userSpecificTabs, $userSpecificGlobals)
    {
        $i = 0;
        foreach ($globalsMeta as $name => $arr) {
            if (!in_array($name, $userSpecificTabs)) {
                continue;
            }

            foreach ($arr as $fieldId => $fieldArr) {
                if (!in_array($fieldId, $userSpecificGlobals)) {
                    continue;
                }

                list($fieldName, $fieldType, $fieldDef, $fieldDesc) = $fieldArr;

                $label = "global:{$fieldId}";
                $fieldValue = $request->request->get("form_{$i}");
                setUserSetting($label, $fieldValue, $_SESSION['authId'], false);

                if ($request->request->get("toggle_{$i}") === "YES") {
                    removeUserSetting($label);
                }

                ++$i;
            }
        }
    }

    public function saveGlobalSettings(Request $r, $globalsMeta)
    {
        $force_off_enable_auditlog_encryption = true;
        // Need to force enable_auditlog_encryption off if the php mycrypt module
        // is not installed.
        if (extension_loaded('mcrypt')) {
            $force_off_enable_auditlog_encryption = false;
        }

        // Aug 22, 2014: Ensoftek: For Auditable events and tamper-resistance (MU2)
        // Check the current status of Audit Logging
        $auditLogStatusFieldOld = $GLOBALS['enable_auditlog'];

        /*
         * Compare form values with old database values.
         * Only save if values differ. Improves speed.
         */

        // Get all the globals from DB
        $sql = "SELECT gl_name, gl_index, gl_value FROM globals ORDER BY gl_name, gl_index";
        $oldGlobals = sqlGetAssoc($sql, false, true);

        $i = 0;

        foreach ($globalsMeta as $name => $array) {
            foreach ($array as $fieldId => $fieldArray) {
                list($fieldName, $fieldType, $fieldDef, $fieldDesc) = $fieldArray;

                if ($fieldType == "pwd") {
                    $pass = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = ?", [$fieldId]);
                    $fieldValueOld = $pass['gl_value'];
                }

                /* Multiple choice fields - do not compare , overwrite */
                if (!is_array($fieldType) && substr($fieldType, 0, 2) == 'm_') {
                    if ($r->request->get("form_{$i}")) {
                        $fieldIndex = 0;
                        sqlStatement("DELETE FROM globals WHERE gl_name = ?", [$fieldId]);

                        $rawField = $r->request->get("form_{$i}");
                        $field = (is_array($rawField)) ? $rawField : array($rawField);
                        foreach ($field as $fieldValue) {
                            $fieldValue = trim($fieldValue);
                            $sql = "INSERT INTO globals (gl_name, gl_index, gl_value) VALUES (?, ?, ?)";
                            $binders = [$fieldId, $fieldIndex, $fieldValue];
                            sqlStatement($sql, $binders);
                            ++$fieldIndex;
                        }
                    }
                } else {
                    $fieldValue = $r->request->get("form_{$i}", "");

                    if ($fieldType == "pwd") {
                        $fieldValue = ($fieldValue) ? SHA1($fieldValue) : $fieldValueOld;
                    }

                    if (!isset($oldGlobals[$fieldId])
                        || (isset($oldGlobals[$fieldId]) && $oldGlobals[$fieldId]['gl_value'] !== $fieldValue)
                    ) {
                        // Must be able to support mcrypt for auditlog
                        if ($force_off_enable_auditlog_encryption && ($fieldId == 'enable_auditlog_encryption')) {
                            error_log("OpenEMR Error: Unable to support auditlog encryption since the php
                             mcrypt module is not installed", 0);
                            $fieldValue = 0;
                        }

                        switch ($fieldId) {
                            case 'first_day_week':
                                $sql = "UPDATE openemr_module_vars SET pn_value = ? 
                                        WHERE pn_name = 'pcFirstDayOfWeek'";
                                sqlStatement($sql, [$fieldValue]);
                                break;
                        }

                        sqlStatement("DELETE FROM globals WHERE gl_name = ?", [$fieldId]);
                        sqlStatement(
                            "INSERT INTO globals (gl_name, gl_index, gl_value) VALUES (?, ?, ?)",
                            [$fieldId, 0, $fieldValue]
                        );
                    }
                    ++$i;
                }
            }
        }

        $this->checkCreateCouchDB();
        $this->checkBackgroundServices();
        $this->handleAltServices('ccdaservice', 'ccda_alt_service_enable', 1);

        // July 1, 2014: Ensoftek: For Auditable events and tamper-resistance (MU2)
        // If Audit Logging status has changed, log it.
        $auditLogStatusNew = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = 'enable_auditlog'");
        $auditLogStatusFieldNew = $auditLogStatusNew['gl_value'];
        if ($auditLogStatusFieldOld != $auditLogStatusFieldNew) {
            auditSQLAuditTamper($auditLogStatusFieldNew);
        }
    }
    
    public function handleAltServices($serviceID, $name = '', $sinterval = 1)
    {
        $sql = "SELECT gl_name, gl_index, gl_value FROM globals WHERE gl_name = ?";
        $result = sqlStatement($sql, [$name]);
        while ($row = sqlFetchArray($result)) {
            $GLOBALS[$row['gl_name']] = $row['gl_value'];
        }

        $isActive = empty($GLOBALS[$name]) ? '0' : '1';
        $interval = max(0, (int) $sinterval);
        $this->updateBackgroundService($serviceID, $isActive, $interval);
        if (!$isActive && $serviceID == 'ccdaservice') {
            require_once($this->globals['fileroot'] . "/ccdaservice/ssmanager.php");
            service_shutdown(0);
        }
    }

    /**
     * Make any necessary changes to background_services table when globals are saved.
     *
     * To prevent an unexpected service call during startup or shutdown, follow these rules:
     * 1. Any "startup" operations should occur _before_ the updateBackgroundService() call.
     * 2. Any "shutdown" operations should occur _after_ the updateBackgroundService() call. If these operations
     * would cause errors in a running service call, it would be best to make the shutdown function itself
     * a background service that is activated here, does nothing if active=1 or running=1 for the
     * parent service, then deactivates itself by setting active=0 when it is done shutting the parent service
     * down. This will prevent nonresponsiveness to the user by waiting for a service to finish.
     * 3. If any "previous" values for globals are required for startup/shutdown logic, they need to be
     * copied to a temp variable before the while($globalsrow...) loop.
     */
    public function checkBackgroundServices()
    {
        //load up any necessary globals
        $sql = "SELECT gl_name, gl_index, gl_value FROM globals WHERE gl_name IN ('phimail_enable','phimail_interval')";
        $result = sqlStatement($sql);
        while ($globalsrow = sqlFetchArray($result)) {
            $GLOBALS[$globalsrow['gl_name']] = $globalsrow['gl_value'];
        }

        //Set up phimail service
        $phimail_active = empty($GLOBALS['phimail_enable']) ? '0' : '1';
        $phimail_interval = max(0, (int) $GLOBALS['phimail_interval']);
        $this->updateBackgroundService('phimail', $phimail_active, $phimail_interval);
    }

    public function checkCreateCouchDB()
    {
        if(empty($GLOBALS['document_storage_method'])) {
            return false;
        }

        $sql = "SELECT gl_name, gl_index, gl_value FROM globals WHERE gl_name IN ('couchdb_host', 'couchdb_user', 
                'couchdb_pass', 'couchdb_port', 'couchdb_dbase', 'document_storage_method')";
        $result = sqlStatement($sql);

        while ($row = sqlFetchArray($result)) {
            $GLOBALS[$row['gl_name']] = $row['gl_value'];
        }

        $temp_dir = $GLOBALS['OE_SITE_DIR'] . '/documents/temp';
        if (!$this->fs->exists($temp_dir)) {
            try {
                $this->fs->mkdir($temp_dir);
            } catch (IOExceptionInterface $e) {
                echo xlt("Failed to create temporary folder. CouchDB will not work.");
            }
        }

        $couch = new CouchDB();
        if (!$couch->check_connection()) {
            echo "<script type=\"text/javascript\">alert('" . xls("CouchDB Connection Failed.") . "');</script>";
            return false;
        }

        if ($GLOBALS['couchdb_host'] || $GLOBALS['couchdb_port'] || $GLOBALS['couchdb_dbase']) {
            $couch->createDB($GLOBALS['couchdb_dbase']);
            $couch->createView($GLOBALS['couchdb_dbase']);
        }

        return true;
    }

    /**
     * Update background_services table for specific service following globals save.
     *
     * @param string $name
     * @param $active
     * @param $interval
     * @return \recordset
     */
    public function updateBackgroundService($name, $active, $interval)
    {
        $sql = "UPDATE background_services SET 
          active=?, 
          next_run=next_run + INTERVAL(? - execute_interval) MINUTE, 
          execute_interval=?
          WHERE name=?";
        return sqlStatement($sql, [$active, $interval, $interval, $name]);
    }

}
