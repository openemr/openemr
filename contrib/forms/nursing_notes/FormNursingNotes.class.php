<?php

// Copyright (C) 2009 Aron Racho <aron@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2

use OpenEMR\Common\ORDataObject\ORDataObject;

define("EVENT_VEHICLE", 1);
define("EVENT_WORK_RELATED", 2);
define("EVENT_SLIP_FALL", 3);
define("EVENT_OTHER", 4);


/**
 * class FormHpTjePrimary
 *
 */
class FormNursingNotes extends ORDataObject
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
    var $activity;
    var $assessment;
    var $procedures;
    var $discharge;

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

        $this->_table = "form_nursing_notes";
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

    function get_procedures()
    {
        return $this->procedures;
    }

    function set_procedures($data)
    {
        if (!empty($data)) {
            $this->procedures = $data;
        }
    }

    function get_discharge()
    {
        return $this->discharge;
    }

    function set_discharge($data)
    {
        if (!empty($data)) {
            $this->discharge = $data;
        }
    }

    function persist()
    {
        parent::persist();
    }
}   // end of Form
