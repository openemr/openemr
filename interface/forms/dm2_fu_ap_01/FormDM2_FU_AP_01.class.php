<?php

/**
 * dm2_fu_ap_01 form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ralf Lukner <lukner@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Ralf Lukner <lukner@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\ORDataObject\ORDataObject;

define("EVENT_VEHICLE", 1);
define("EVENT_WORK_RELATED", 2);
define("EVENT_SLIP_FALL", 3);
define("EVENT_OTHER", 4);


/**
 * class FormDM2_FU_AP_01
 *
 */
class FormDM2_FU_AP_01 extends ORDataObject
{

    /**
     *
     * @access public
     */


    /**
     *
     * static
     */
    var $id;
    var $date;
    var $pid;
    var $user;
    var $groupname;
    var $authorized;
    var $activity;
    var $dm2_mgmt_status;
    var $dm2_medications;
    var $dm2_referrals;
    var $dm2_goals;
    var $dm2_labs_procedures_ordered;
    var $pt_diet_exercise;
    var $dm_complications;
    var $preventatives;

    /**
     * Constructor sets all Form attributes to their default value
     */

    function __construct($id = "", $_prefix = "")
    {
        if (is_numeric($id)) {
            $this->id = $id;
        } else {
            $id = "";
            $this->date = date("Y-m-d H:i:s");
        }

        $this->_table = "form_dm2_fu_ap_01";
        $this->activity = 1;
        $this->pid = $GLOBALS['pid'];
        if ($id != "") {
            $this->populate();
            //$this->date = $this->get_date();
        }
    }
    function populate()
    {
        parent::populate();
        //$this->temp_methods = parent::_load_enum("temp_locations",false);
    }

    function toString($html = false)
    {
        $string .= "\n"
            . "ID: " . $this->id . "\n";

        if ($html) {
            return nl2br($string);
        } else {
            return $string;
        }
    }
    function set_id($id)
    {
        if (!empty($id) && is_numeric($id)) {
            $this->id = $id;
        }
    }
    function get_id()
    {
        return $this->id;
    }
    function set_pid($pid)
    {
        if (!empty($pid) && is_numeric($pid)) {
            $this->pid = $pid;
        }
    }
    function get_pid()
    {
        return $this->pid;
    }
    function set_activity($tf)
    {
        if (!empty($tf) && is_numeric($tf)) {
            $this->activity = $tf;
        }
    }
    function get_activity()
    {
        return $this->activity;
    }

    function get_date()
    {
        return $this->date;
    }
    function set_date($dt)
    {
        if (!empty($dt)) {
            $this->date = $dt;
        }
    }
    function get_user()
    {
        return $this->user;
    }
    function set_user($u)
    {
        if (!empty($u)) {
            $this->user = $u;
        }
    }
    function get_dm2_mgmt_status()
    {
        return $this->dm2_mgmt_status;
    }
    function set_dm2_mgmt_status($data)
    {
        $this->dm2_mgmt_status = $data;
    }
    function get_dm2_medications()
    {
        return $this->dm2_medications;
    }
    function set_dm2_medications($data)
    {
        if (!empty($data)) {
            $this->dm2_medications = $data;
        }
    }
    function get_dm2_referrals()
    {
        return $this->dm2_referrals;
    }
    function set_dm2_referrals($data)
    {
        if (!empty($data)) {
            $this->dm2_referrals = $data;
        }
    }
    function get_dm2_goals()
    {
        return $this->dm2_goals;
    }
    function set_dm2_goals($data)
    {
        if (!empty($data)) {
            $this->dm2_goals = $data;
        }
    }
    function get_dm2_labs_procedures_ordered()
    {
        return $this->dm2_labs_procedures_ordered;
    }
    function set_dm2_labs_procedures_ordered($data)
    {
        if (!empty($data)) {
            $this->dm2_labs_procedures_ordered = $data;
        }
    }
    function get_pt_diet_exercise()
    {
        return $this->pt_diet_exercise;
    }
    function set_pt_diet_exercise($data)
    {
        if (!empty($data)) {
            $this->pt_diet_exercise = $data;
        }
    }
    function get_dm_complications()
    {
        return $this->dm_complications;
    }
    function set_dm_complications($data)
    {
        if (!empty($data)) {
            $this->dm_complications = $data;
        }
    }
    function get_preventatives()
    {
        return $this->preventatives;
    }
    function set_preventatives($data)
    {
        if (!empty($data)) {
            $this->preventatives = $data;
        }
    }

    function persist()
    {
        parent::persist();
    }
}   // end of Form
