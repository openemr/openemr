<?php

/**
 * soap form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\ORDataObject\ORDataObject;

define("EVENT_VEHICLE", 1);
define("EVENT_WORK_RELATED", 2);
define("EVENT_SLIP_FALL", 3);
define("EVENT_OTHER", 4);


/**
 * class FormSOAP
 */
class FormSOAP extends ORDataObject
{
    protected string $_table = 'form_soap';

    /**
     * @access public
     */

    /**
     * static
     */
    var $date;
    var $pid;
    var $user;
    var $groupname;
    var $authorized;
    var $activity;
    var $subjective;
    var $objective;
    var $assessment;
    var $plan;

    /**
     * Set Form attributes to their default value
     *
     * @return void
     */
    protected function init(): void
    {
        $this->activity = 1;
        $this->pid = $GLOBALS['pid'];
    }

    function toString($html = false)
    {
        $string = "\n" . "ID: " . $this->id . "\n";
        return $html ? nl2br($string) : $string;
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
    function get_subjective()
    {
        return $this->subjective;
    }
    function set_subjective($data)
    {
        if (!empty($data)) {
            $this->subjective = $data;
        }
    }
    function get_objective()
    {
        return $this->objective;
    }
    function set_objective($data)
    {
        if (!empty($data)) {
            $this->objective = $data;
        }
    }
    function get_assessment()
    {
        return $this->assessment;
    }
    function set_assessment($data)
    {
        if (!empty($data)) {
            $this->assessment = $data;
        }
    }
    function get_plan()
    {
        return $this->plan;
    }
    function set_plan($data)
    {
        if (!empty($data)) {
            $this->plan = $data;
        }
    }

    function persist()
    {
        parent::persist();
    }
}   // end of Form
