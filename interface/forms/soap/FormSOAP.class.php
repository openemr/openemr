<?php

/**
 * soap form
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\ORDataObject\ORDataObject;
use OpenEMR\Core\OEGlobalsBag;

define("EVENT_VEHICLE", 1);
define("EVENT_WORK_RELATED", 2);
define("EVENT_SLIP_FALL", 3);
define("EVENT_OTHER", 4);


/**
 * class FormHpTjePrimary
 *
 */
class FormSOAP extends ORDataObject
{
    /**
     *
     * @access public
     */


    /**
     *
     * static
     */
    public $id;
    public $date;
    public $pid;
    public $user;
    public $groupname;
    public $authorized;
    public $activity;
    public $subjective;
    public $objective;
    public $assessment;
    public $plan;

    /**
     * Constructor sets all Form attributes to their default value
     */

    public function __construct($id = "")
    {
        if (is_numeric($id)) {
            $this->id = $id;
        } else {
            $id = "";
            $this->date = date("Y-m-d H:i:s");
        }

        $this->_table = "form_soap";
        $this->activity = 1;
        $this->pid = OEGlobalsBag::getInstance()->get('pid');
        if ($id != "") {
            $this->populate();
            //$this->date = $this->get_date();
        }
    }
    public function populate()
    {
        parent::populate();
        //$this->temp_methods = parent::_load_enum("temp_locations",false);
    }

    public function toString($html = false)
    {
        $string = "\n" . "ID: " . $this->id . "\n";
        return $html ? nl2br($string) : $string;
    }

    public function set_id($id)
    {
        if (!empty($id) && is_numeric($id)) {
            $this->id = $id;
        }
    }
    public function get_id()
    {
        return $this->id;
    }
    public function set_pid($pid)
    {
        if (!empty($pid) && is_numeric($pid)) {
            $this->pid = $pid;
        }
    }
    public function get_pid()
    {
        return $this->pid;
    }
    public function set_activity($tf)
    {
        if (!empty($tf) && is_numeric($tf)) {
            $this->activity = $tf;
        }
    }
    public function get_activity()
    {
        return $this->activity;
    }

    public function get_date()
    {
        return $this->date;
    }
    public function set_date($dt)
    {
        if (!empty($dt)) {
            $this->date = $dt;
        }
    }
    public function get_user()
    {
        return $this->user;
    }
    public function set_user($u)
    {
        if (!empty($u)) {
            $this->user = $u;
        }
    }
    public function get_subjective()
    {
        return $this->subjective;
    }
    public function set_subjective($data)
    {
        if (!empty($data)) {
            $this->subjective = $data;
        }
    }
    public function get_objective()
    {
        return $this->objective;
    }
    public function set_objective($data)
    {
        if (!empty($data)) {
            $this->objective = $data;
        }
    }
    public function get_assessment()
    {
        return $this->assessment;
    }
    public function set_assessment($data)
    {
        if (!empty($data)) {
            $this->assessment = $data;
        }
    }
    public function get_plan()
    {
        return $this->plan;
    }
    public function set_plan($data)
    {
        if (!empty($data)) {
            $this->plan = $data;
        }
    }
}   // end of Form
