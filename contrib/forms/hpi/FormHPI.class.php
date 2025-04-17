<?php

/* FormHPI class
 *
 * @package OpenEMR
 * @author Aron Racho <aron@mi-squared.com>
 * @author Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (C) 2009 Aron Racho <aron@mi-squared.com>
 * @copyright Copyright (c) 2017 Stephen Waite <stephen.waite@cmsvt.com>
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\ORDataObject\ORDataObject;

define("EVENT_VEHICLE", 1);
define("EVENT_WORK_RELATED", 2);
define("EVENT_SLIP_FALL", 3);
define("EVENT_OTHER", 4);


/**
 * class FormHpTjePrimary
 *
 */

class FormHPI extends ORDataObject
{
    var $id;
    var $date;
    var $pid;
    var $user;
    var $groupname;
    var $activity;
    var $complaint;
    var $location;
    var $quality;
    var $severity;
    var $duration;
    var $timing;
    var $context;
    var $factors;
    var $signs;

    /**
     * Constructor sets all Form attributes to their default value
     */

    function __construct($id = "", $_prefix = "")
    {
        parent::__construct();

        if (is_numeric($id)) {
            $this->id = $id;
        } else {
            $id = "";
            $this->date = date("Y-m-d H:i:s");
        }

        $this->_table = "form_hpi";
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

    function __toString()
    {
        return "ID: " . $this->id . "\n";
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

    function get_complaint()
    {
        return $this->complaint;
    }

    function set_complaint($data)
    {
        if (!empty($data)) {
            $this->complaint = $data;
        }
    }

    function get_location()
    {
        return $this->location;
    }

    function set_location($data)
    {
        if (!empty($data)) {
            $this->location = $data;
        }
    }

    function get_quality()
    {
        return $this->quality;
    }

    function set_quality($data)
    {
        if (!empty($data)) {
            $this->quality = $data;
        }
    }

    function get_severity()
    {
        return $this->severity;
    }

    function set_severity($data)
    {
        if (!empty($data)) {
            $this->severity = $data;
        }
    }

    function get_duration()
    {
        return $this->duration;
    }

    function set_duration($data)
    {
        if (!empty($data)) {
            $this->duration = $data;
        }
    }

    function get_timing()
    {
        return $this->timing;
    }

    function set_timing($data)
    {
        if (!empty($data)) {
            $this->timing = $data;
        }
    }

    function get_context()
    {
        return $this->context;
    }

    function set_context($data)
    {
        if (!empty($data)) {
            $this->context = $data;
        }
    }

    function get_factors()
    {
        return $this->factors;
    }

    function set_factors($data)
    {
        if (!empty($data)) {
            $this->factors = $data;
        }
    }

    function get_signs()
    {
        return $this->signs;
    }

    function set_signs($data)
    {
        if (!empty($data)) {
            $this->signs = $data;
        }
    }

    function persist()
    {
        parent::persist();
    }
}   // end of Form
