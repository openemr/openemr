<?php

/**
 * dm2_fu_hpi_01 form
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
 * class FormDM2_FU_HPI_01
 *
 */
class FormDM2_FU_HPI_01 extends ORDataObject
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
    var $date_of_original_dm2_diagnosis;
    var $date_last_dm2_visit;
    var $dm2_mgmt_last_visit;
    var $intv_hx_chngs_bs_er_hosp;
    var $severity_significance;

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

        $this->_table = "form_dm2_fu_hpi_01";
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
    function get_date_of_original_dm2_diagnosis()
    {
        return $this->date_of_original_dm2_diagnosis;
    }
    function set_date_of_original_dm2_diagnosis($data)
    {
        $this->date_of_original_dm2_diagnosis = $data;
    }
    function get_date_last_dm2_visit()
    {
        return $this->date_last_dm2_visit;
    }
    function set_date_last_dm2_visit($data)
    {
        if (!empty($data)) {
            $this->date_last_dm2_visit = $data;
        }
    }
    function get_dm2_mgmt_last_visit()
    {
        return $this->dm2_mgmt_last_visit;
    }
    function set_dm2_mgmt_last_visit($data)
    {
        if (!empty($data)) {
            $this->dm2_mgmt_last_visit = $data;
        }
    }
    function get_intv_hx_chngs_bs_er_hosp()
    {
        return $this->intv_hx_chngs_bs_er_hosp;
    }
    function set_intv_hx_chngs_bs_er_hosp($data)
    {
        if (!empty($data)) {
            $this->intv_hx_chngs_bs_er_hosp = $data;
        }
    }
    function get_severity_significance()
    {
        return $this->severity_significance;
    }
    function set_severity_significance($data)
    {
        if (!empty($data)) {
            $this->severity_significance = $data;
        }
    }

    function persist()
    {
        parent::persist();
    }
}   // end of Form
