<?php
/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Patient;

/**
 * Entry point for a patient.
 *
 * If you use the Service Container to `get()` this object, `setGlobals()` is
 * automatically called, otherwise you must call it to inject the `$GLOBALS`
 * variable into the object.
 *
 * @package Patient
 * @subpackage Patient
 * @author Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2017 Robert Down <robertdown@live.com>
 */
class Patient
{

    /**
     * @var array $globals The $GLOBALS array
     */
    private $globals = [];

    /**
     * @var int $pid PID of the current patient
     */
    protected $pid;

    /**
     * @var null|bool $portalAuthorized Status of the onsite portal
     */
    protected $portalAuthorized = null;

    /**
     * Patient constructor.
     *
     * @param integer $pid PID of the current patient
     */
    public function __construct($pid)
    {
        $this->pid = $pid;
    }

    /**
     * Inject the $GLOBALS variable
     *
     * @param array $globals
     */
    public function setGlobals($globals)
    {
        $this->globals = $globals;
    }

    /**
     * Has the patient authorized portal usage.
     *
     * @return bool
     */
    public function isPortalAuthorized()
    {
        if (null !== $this->portalAuthorized) {
            return $this->portalAuthorized;
        }

        $g = $this->globals;
        $one = $g['portal_onsite_enable'];
        $one_addr = $g['portal_onsite_address'];
        $two = $g['portal_onsite_two_enable'];
        $two_addr = $g['portal_onsite_two_address'];
        if (($one && $one_addr) || ($two && $two_addr)) {
            $sql = "SELECT allow_patient_portal AS allowed FROM patient_data WHERE pid = ?";
            $query = sqlStatement($sql, [$this->pid]);
            $row = sqlFetchArray($query);

            if ($row['allowed'] == 'YES') {
                $this->portalAuthorized = true;
                return true;
            } else {
                $this->portalAuthorized = false;
                return false;
            }
        }

        $this->portalAuthorized = false;
        return false;
    }

    /**
     * Has the patient created a portal account.
     *
     * @return bool
     */
    public function hasPortalCredentials()
    {
        if (!$this->isPortalAuthorized()) {
            return false;
        }

        $sql = "SELECT pid FROM patient_access_onsite WHERE pid = ?";
        $query = sqlQuery($sql, [$this->pid]);
        if (empty($query)) {
            return false;
        } else {
            return true;
        }
    }
}
