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
class FormHand extends ORDataObject
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
    var $left_1;
    var $left_2;
    var $left_3;
    var $right_1;
    var $right_2;
    var $right_3;

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

        $this->_table = "form_hand";
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
        $string = "\n" . "ID: " . $this->id . "\n";

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


    function persist()
    {
        parent::persist();
    }

    //

    function set_left_1($tf)
    {
        $this->left_1 = $tf;
    }
    function get_left_1()
    {
        return $this->left_1;
    }

    function set_left_2($tf)
    {
        $this->left_2 = $tf;
    }
    function get_left_2()
    {
        return $this->left_2;
    }

    function set_left_3($tf)
    {
        $this->left_3 = $tf;
    }
    function get_left_3()
    {
        return $this->left_3;
    }

    function set_right_1($tf)
    {
        $this->right_1 = $tf;
    }
    function get_right_1()
    {
        return $this->right_1;
    }

    function set_right_2($tf)
    {
        $this->right_2 = $tf;
    }
    function get_right_2()
    {
        return $this->right_2;
    }

    function set_right_3($tf)
    {
            $this->right_3 = $tf;
    }
    function get_right_3()
    {
        return $this->right_3;
    }


    var $handedness;
    function get_handedness()
    {
        return $this->handedness;
    }
    function set_handedness($data)
    {
        if (!empty($data)) {
            $this->handedness = $data;
        }
    }
    function get_handedness_l()
    {
        return $this->handedness == "Left" ? "CHECKED" : "";
    }
    function get_handedness_r()
    {
        return $this->handedness == "Right" ? "CHECKED" : "";
    }
    function get_handedness_b()
    {
        return $this->handedness == "Both" ? "CHECKED" : "";
    }

    // ----- notes -----

    var $notes;
    function get_notes()
    {
        return $this->notes;
    }
    function set_notes($data)
    {
        if (!empty($data)) {
            $this->notes = $data;
        }
    }
}   // end of Form
