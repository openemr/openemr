<?php
/**
 * This file is part of OpenEMR.
 *
 * @package     OpenEMR
 * @subpackage
 * @link        https://www.open-emr.org
 * @author      Robert Down <robertdown@live.com>
 * @copyright   Copyright (c) 2019 Robert Down <robertdown@live.com
 * @license     https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\Service;

use OpenEMR\Admin\Repository\SettingRepository;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\Connector;
use OpenEMR\Common\Logging\EventAuditLogger;

class SettingsService
{

    /**
     * @var SettingRepository
     */
    private $repository;

    private $globals_meta;

    private $user_tabs;

    private $user_globals;

    /** @var CryptoGen */
    private $cryptoGen;

    public function __construct($globals_meta, $user_tabs, $user_globals, $cryptoGen) {
        $this->cryptoGen = $cryptoGen;
        $this->globals_meta = $globals_meta;
        $this->user_tabs = $user_tabs;
        $this->user_globals = $user_globals;
        $db = Connector::Instance();
        $em = $db->entityManager;
        $this->repository = $em->getRepository('\OpenEMR\Admin\Entity\Setting');
    }

    public function getSettingByName($name) {
        return $this->repository->getSettingByName($name);
    }

    /**
     * @todo Document
     */
    public function checkCreateCDB() {
        $sql = "SELECT gl_name, gl_index, gl_value FROM globals WHERE gl_name IN 
                ('couchdb_host','couchdb_user','couchdb_pass','couchdb_port','couchdb_dbase','document_storage_method')";
        $globalsres = sqlStatement($sql);
        $options = array();
        while ($globalsrow = sqlFetchArray($globalsres)) {
            $GLOBALS[$globalsrow['gl_name']] = $globalsrow['gl_value'];
        }

        $directory_created = false;
        if (!empty($GLOBALS['document_storage_method'])) {
            // /documents/temp/ folder is required for CouchDB
            if (!is_dir($GLOBALS['OE_SITE_DIR'] . '/documents/temp/')) {
                $directory_created = mkdir($GLOBALS['OE_SITE_DIR'] . '/documents/temp/', 0777, true);
                if (!$directory_created) {
                    echo xlt("Failed to create temporary folder. CouchDB will not work.");
                }
            }

            $couch = new CouchDB();
            if (!$couch->check_connection()) {
                $alertText = xlj("CouchDB Connection Failed");
                echo "<script>alert(\"{$alertText}.\");</script>";
                return false;
            }

            if ($GLOBALS['couchdb_host'] || $GLOBALS['couchdb_port'] || $GLOBALS['couchdb_dbase']) {
                $couch->createDB($GLOBALS['couchdb_dbase']);
                $couch->createView($GLOBALS['couchdb_dbase']);
            }
        }

        return true;
    }

    /**
     * Update background_services table for a specific service following globals save.
     */
    public function updateBackgroundService($name, $active, $interval) {
        //order important here: next_run change dependent on _old_ value of execute_interval so it comes first
        $sql = "UPDATE background_services SET active=?, next_run = next_run + INTERVAL (? - execute_interval) MINUTE, execute_interval=? WHERE name=?";
        return sqlStatement($sql, [$active, $interval, $interval, $name]);
    }

    /**
     * Make any necessary changes to background_services table when globals are saved.
     *
     * To prevent an unexpected service call during startup or shutdown, follow these rules:
     * 1. Any "startup" operations should occur _before_ the updateBackgroundService() call.
     * 2. Any "shutdown" operations should occur _after_ the updateBackgroundService() call. If these operations
     * would cause errors in a running service call, it would be best to make the shutdown function itself is
     * a background service that is activated here, does nothing if active=1 or running=1 for the
     * parent service.  Then it deactivates itself by setting active=0 when it is done shutting the parent service
     * down. This will prevent non-responsiveness to the user by waiting for a service to finish.
     * 3. If any "previous" values for globals are required for startup/shutdown logic, they need to be
     * copied to a temp variable before the while($globalsrow...) loop.
     */
    public function checkBackgroundServices() {
        //load up any necessary globals
        $sql = "SELECT gl_name, gl_index, gl_value FROM globals WHERE gl_name IN ('phimail_enable','phimail_interval')";
        $bgservices = sqlStatement($sql);
        while ($globalsrow = sqlFetchArray($bgservices)) {
            $GLOBALS[$globalsrow['gl_name']] = $globalsrow['gl_value'];
        }

        //Set up phimail service
        $phimail_active = empty($GLOBALS['phimail_enable']) ? '0' : '1';
        $phimail_interval = max(0, (int) $GLOBALS['phimail_interval']);
        $this->updateBackgroundService('phimail', $phimail_active, $phimail_interval);
    }

    /**
     * Save user-specific settings.
     *
     * @param array $data POST contents
     * @return bool True on success
     */
    public function saveUserSettings(Array $data) {
        if (!CsrfUtils::verifyCsrfToken($data["csrf_token_form"])) {
            CsrfUtils::csrfNotVerified();
        }

        $i = 0;
        foreach ($this->globals_meta as $grpname => $grparr) {
            if (in_array($grpname, $this->user_tabs)) {
                foreach ($grparr as $fldid => $fldarr) {
                    if (in_array($fldid, $this->user_globals)) {
                        list($fldname, $fldtype, $flddef, $flddesc) = $fldarr;
                        $label = "global:".$fldid;
                        if ($fldtype == "encrypted") {
                            if (empty(trim($data["form_$i"]))) {
                                $fldvalue = '';
                            } else {
                                $fldvalue = $this->cryptoGen->encryptStandard(trim($data["form_$i"]));
                            }
                        } else {
                            $fldvalue = trim($data["form_$i"]);
                        }
                        setUserSetting($label, $fldvalue, $_SESSION['authId'], false);
                        if ($data["toggle_$i"] == "YES") {
                            removeUserSetting($label);
                        }

                        ++$i;
                    }
                }
            }
        }
        return true;
    }

    public function saveGlobalSettings(Array $data) {
        //verify csrf
        if (!CsrfUtils::verifyCsrfToken($data["csrf_token_form"])) {
            CsrfUtils::csrfNotVerified();
        }

        // For Auditable events and tamper-resistance (MU2). Check the current status of Audit Logging
        $auditLogStatusFieldOld = $GLOBALS['enable_auditlog'];

        /*
        * Compare form values with old database values.
        * Only save if values differ. Improves speed.
        */

        // Get all the globals from DB
        $old_globals = sqlGetAssoc('SELECT gl_name, gl_index, gl_value FROM `globals` ORDER BY gl_name, gl_index', false, true);

        $i = 0;
        foreach ($this->globals_meta as $grpname => $grparr) {
            foreach ($grparr as $fldid => $fldarr) {
                list($fldname, $fldtype, $flddef, $flddesc) = $fldarr;
                if ($fldtype == 'pwd') {
                    $pass = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = ?", array($fldid));
                    $fldvalueold = $pass['gl_value'];
                }

                /* Multiple choice fields - do not compare , overwrite */
                if (!is_array($fldtype) && substr($fldtype, 0, 2) == 'm_') {
                    if (isset($data["form_$i"])) {
                        $fldindex = 0;

                        sqlStatement("DELETE FROM globals WHERE gl_name = ?", array( $fldid ));

                        foreach ($data["form_$i"] as $fldvalue) {
                            $fldvalue = trim($fldvalue);
                            sqlStatement('INSERT INTO `globals` ( gl_name, gl_index, gl_value ) VALUES ( ?,?,?)', array( $fldid, $fldindex, $fldvalue ));
                            ++$fldindex;
                        }
                    }
                } else {
                    /* check value of single field. Don't update if the database holds the same value */
                    if (isset($data["form_$i"])) {
                        $fldvalue = trim($data["form_$i"]);
                    } else {
                        $fldvalue = "";
                    }

                    if ($fldtype=='pwd') {
                        $fldvalue = $fldvalue ? SHA1($fldvalue) : $fldvalueold; // TODO: salted passwords?
                    }

                    if ($fldtype == 'encrypted') {
                        if (empty(trim($fldvalue))) {
                            $fldvalue = '';
                        } else {
                            $fldvalue = $this->cryptoGen->encryptStandard($fldvalue);
                        }
                    }

                    // We rely on the fact that set of keys in globals.inc === set of keys in `globals`  table!

                    if (!isset($old_globals[$fldid]) || (isset($old_globals[$fldid]) && $old_globals[ $fldid ]['gl_value'] !== $fldvalue )) {
                        // special treatment for some vars
                        switch ($fldid) {
                            case 'first_day_week':
                                // update PostCalendar config as well
                                sqlStatement("UPDATE openemr_module_vars SET pn_value = ? WHERE pn_name = 'pcFirstDayOfWeek'", array($fldvalue));
                                break;
                        }

                        // Replace old values
                        sqlStatement('DELETE FROM `globals` WHERE gl_name = ?', array( $fldid ));
                        sqlStatement('INSERT INTO `globals` ( gl_name, gl_index, gl_value ) VALUES ( ?, ?, ? )', array( $fldid, 0, $fldvalue ));
                    } else {
                        //error_log("No need to update $fldid");
                    }
                }

                ++$i;
            }
        }

        $this->checkCreateCDB();
        $this->checkBackgroundServices();

        // For Auditable events and tamper-resistance (MU2). If Audit Logging status has changed, log it.
        $auditLogStatusNew = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = 'enable_auditlog'");
        $auditLogStatusFieldNew = $auditLogStatusNew['gl_value'];
        if ($auditLogStatusFieldOld != $auditLogStatusFieldNew) {
            EventAuditLogger::instance()->auditSQLAuditTamper($auditLogStatusFieldNew);
        }
    }

    public function renderSettingRow(Array $row) {

    }

    /**
     * Return a section of settings based on given name.
     *
     * @param string $sectionName Name of section to return
     * @return bool|array False if given section doesn't exist, array of section if successful
     */
    public function getSection(string $sectionName): array {
        if (!array_key_exists($sectionName, $this->globals_meta)) {
            return false;
        }

        return $this->globals_meta[$sectionName];
    }

    public function getAllSections(): ?array {
        return $this->globals_meta;
    }

    /**
     * Return section names, limited by user if necessary.
     *
     * @param bool $userOnly Defaults to false, true to limit results to what a user can see
     * @return array Simple array of sections
     */
    public function getSectionNames(bool $userOnly = false): array {
        return ($userOnly) ? array_keys($this->user_tabs) : array_keys($this->globals_meta);
    }

    public function isUserSection(string $sectionName): bool {
        return in_array($sectionName, $this->user_tabs);
    }

    public function isUserField(string $fieldID): bool {
        return in_array($fieldID, $this->user_globals);
    }
}
