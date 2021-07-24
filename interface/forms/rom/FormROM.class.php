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
class FormROM extends ORDataObject
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

        $this->_table = "form_rom";
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

    function persist()
    {
        parent::persist();
    }

    // fields



    // ----- Flexion -----

    var $r1_1_active;
    var $r1_1_passive;
    function get_r1_1_active()
    {
        return $this->r1_1_active;
    }
    function set_r1_1_active($data)
    {
        if (!empty($data)) {
            $this->r1_1_active = $data;
        }
    }
    function get_r1_1_passive()
    {
        return $this->r1_1_passive;
    }
    function set_r1_1_passive($data)
    {
        if (!empty($data)) {
            $this->r1_1_passive = $data;
        }
    }

    // ----- Extension -----

    var $r1_2_active;
    var $r1_2_passive;
    function get_r1_2_active()
    {
        return $this->r1_2_active;
    }
    function set_r1_2_active($data)
    {
        if (!empty($data)) {
            $this->r1_2_active = $data;
        }
    }
    function get_r1_2_passive()
    {
        return $this->r1_2_passive;
    }
    function set_r1_2_passive($data)
    {
        if (!empty($data)) {
            $this->r1_2_passive = $data;
        }
    }

    // ----- Right Lateral Bending -----

    var $r1_3_active;
    var $r1_3_passive;
    function get_r1_3_active()
    {
        return $this->r1_3_active;
    }
    function set_r1_3_active($data)
    {
        if (!empty($data)) {
            $this->r1_3_active = $data;
        }
    }
    function get_r1_3_passive()
    {
        return $this->r1_3_passive;
    }
    function set_r1_3_passive($data)
    {
        if (!empty($data)) {
            $this->r1_3_passive = $data;
        }
    }

    // ----- Left Lateral Bending -----

    var $r1_4_active;
    var $r1_4_passive;
    function get_r1_4_active()
    {
        return $this->r1_4_active;
    }
    function set_r1_4_active($data)
    {
        if (!empty($data)) {
            $this->r1_4_active = $data;
        }
    }
    function get_r1_4_passive()
    {
        return $this->r1_4_passive;
    }
    function set_r1_4_passive($data)
    {
        if (!empty($data)) {
            $this->r1_4_passive = $data;
        }
    }

    // ----- Right Rotation -----

    var $r1_5_active;
    var $r1_5_passive;
    function get_r1_5_active()
    {
        return $this->r1_5_active;
    }
    function set_r1_5_active($data)
    {
        if (!empty($data)) {
            $this->r1_5_active = $data;
        }
    }
    function get_r1_5_passive()
    {
        return $this->r1_5_passive;
    }
    function set_r1_5_passive($data)
    {
        if (!empty($data)) {
            $this->r1_5_passive = $data;
        }
    }

    // ----- Left Rotation -----

    var $r1_6_active;
    var $r1_6_passive;
    function get_r1_6_active()
    {
        return $this->r1_6_active;
    }
    function set_r1_6_active($data)
    {
        if (!empty($data)) {
            $this->r1_6_active = $data;
        }
    }
    function get_r1_6_passive()
    {
        return $this->r1_6_passive;
    }
    function set_r1_6_passive($data)
    {
        if (!empty($data)) {
            $this->r1_6_passive = $data;
        }
    }

    // ----- Flexion -----

    var $r1_7_active;
    var $r1_7_passive;
    function get_r1_7_active()
    {
        return $this->r1_7_active;
    }
    function set_r1_7_active($data)
    {
        if (!empty($data)) {
            $this->r1_7_active = $data;
        }
    }
    function get_r1_7_passive()
    {
        return $this->r1_7_passive;
    }
    function set_r1_7_passive($data)
    {
        if (!empty($data)) {
            $this->r1_7_passive = $data;
        }
    }

    // ----- Extension -----

    var $r1_8_active;
    var $r1_8_passive;
    function get_r1_8_active()
    {
        return $this->r1_8_active;
    }
    function set_r1_8_active($data)
    {
        if (!empty($data)) {
            $this->r1_8_active = $data;
        }
    }
    function get_r1_8_passive()
    {
        return $this->r1_8_passive;
    }
    function set_r1_8_passive($data)
    {
        if (!empty($data)) {
            $this->r1_8_passive = $data;
        }
    }

    // ----- Right Lateral Bending -----

    var $r1_9_active;
    var $r1_9_passive;
    function get_r1_9_active()
    {
        return $this->r1_9_active;
    }
    function set_r1_9_active($data)
    {
        if (!empty($data)) {
            $this->r1_9_active = $data;
        }
    }
    function get_r1_9_passive()
    {
        return $this->r1_9_passive;
    }
    function set_r1_9_passive($data)
    {
        if (!empty($data)) {
            $this->r1_9_passive = $data;
        }
    }

    // ----- Left Lateral Bending -----

    var $r1_10_active;
    var $r1_10_passive;
    function get_r1_10_active()
    {
        return $this->r1_10_active;
    }
    function set_r1_10_active($data)
    {
        if (!empty($data)) {
            $this->r1_10_active = $data;
        }
    }
    function get_r1_10_passive()
    {
        return $this->r1_10_passive;
    }
    function set_r1_10_passive($data)
    {
        if (!empty($data)) {
            $this->r1_10_passive = $data;
        }
    }

    // ----- Right Lateral Rotation -----

    var $r1_11_active;
    var $r1_11_passive;
    function get_r1_11_active()
    {
        return $this->r1_11_active;
    }
    function set_r1_11_active($data)
    {
        if (!empty($data)) {
            $this->r1_11_active = $data;
        }
    }
    function get_r1_11_passive()
    {
        return $this->r1_11_passive;
    }
    function set_r1_11_passive($data)
    {
        if (!empty($data)) {
            $this->r1_11_passive = $data;
        }
    }

    // ----- Right Lateral Rotation -----

    var $r1_12_active;
    var $r1_12_passive;
    function get_r1_12_active()
    {
        return $this->r1_12_active;
    }
    function set_r1_12_active($data)
    {
        if (!empty($data)) {
            $this->r1_12_active = $data;
        }
    }
    function get_r1_12_passive()
    {
        return $this->r1_12_passive;
    }
    function set_r1_12_passive($data)
    {
        if (!empty($data)) {
            $this->r1_12_passive = $data;
        }
    }



    var $r2_1_rt_active;
    var $r2_1_rt_passive;
    var $r2_1_lf_active;
    var $r2_1_lf_passive;
    function get_r2_1_rt_active()
    {
        return $this->r2_1_rt_active;
    }
    function set_r2_1_rt_active($data)
    {
        if (!empty($data)) {
            $this->r2_1_rt_active = $data;
        }
    }
    function get_r2_1_rt_passive()
    {
        return $this->r2_1_rt_passive;
    }
    function set_r2_1_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r2_1_rt_passive = $data;
        }
    }
    function get_r2_1_lf_active()
    {
        return $this->r2_1_lf_active;
    }
    function set_r2_1_lf_active($data)
    {
        if (!empty($data)) {
            $this->r2_1_lf_active = $data;
        }
    }
    function get_r2_1_lf_passive()
    {
        return $this->r2_1_lf_passive;
    }
    function set_r2_1_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r2_1_lf_passive = $data;
        }
    }

    var $r2_2_rt_active;
    var $r2_2_rt_passive;
    var $r2_2_lf_active;
    var $r2_2_lf_passive;
    function get_r2_2_rt_active()
    {
        return $this->r2_2_rt_active;
    }
    function set_r2_2_rt_active($data)
    {
        if (!empty($data)) {
            $this->r2_2_rt_active = $data;
        }
    }
    function get_r2_2_rt_passive()
    {
        return $this->r2_2_rt_passive;
    }
    function set_r2_2_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r2_2_rt_passive = $data;
        }
    }
    function get_r2_2_lf_active()
    {
        return $this->r2_2_lf_active;
    }
    function set_r2_2_lf_active($data)
    {
        if (!empty($data)) {
            $this->r2_2_lf_active = $data;
        }
    }
    function get_r2_2_lf_passive()
    {
        return $this->r2_2_lf_passive;
    }
    function set_r2_2_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r2_2_lf_passive = $data;
        }
    }

    var $r2_3_rt_active;
    var $r2_3_rt_passive;
    var $r2_3_lf_active;
    var $r2_3_lf_passive;
    function get_r2_3_rt_active()
    {
        return $this->r2_3_rt_active;
    }
    function set_r2_3_rt_active($data)
    {
        if (!empty($data)) {
            $this->r2_3_rt_active = $data;
        }
    }
    function get_r2_3_rt_passive()
    {
        return $this->r2_3_rt_passive;
    }
    function set_r2_3_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r2_3_rt_passive = $data;
        }
    }
    function get_r2_3_lf_active()
    {
        return $this->r2_3_lf_active;
    }
    function set_r2_3_lf_active($data)
    {
        if (!empty($data)) {
            $this->r2_3_lf_active = $data;
        }
    }
    function get_r2_3_lf_passive()
    {
        return $this->r2_3_lf_passive;
    }
    function set_r2_3_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r2_3_lf_passive = $data;
        }
    }

    var $r2_4_rt_active;
    var $r2_4_rt_passive;
    var $r2_4_lf_active;
    var $r2_4_lf_passive;
    function get_r2_4_rt_active()
    {
        return $this->r2_4_rt_active;
    }
    function set_r2_4_rt_active($data)
    {
        if (!empty($data)) {
            $this->r2_4_rt_active = $data;
        }
    }
    function get_r2_4_rt_passive()
    {
        return $this->r2_4_rt_passive;
    }
    function set_r2_4_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r2_4_rt_passive = $data;
        }
    }
    function get_r2_4_lf_active()
    {
        return $this->r2_4_lf_active;
    }
    function set_r2_4_lf_active($data)
    {
        if (!empty($data)) {
            $this->r2_4_lf_active = $data;
        }
    }
    function get_r2_4_lf_passive()
    {
        return $this->r2_4_lf_passive;
    }
    function set_r2_4_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r2_4_lf_passive = $data;
        }
    }

    var $r2_5_rt_active;
    var $r2_5_rt_passive;
    var $r2_5_lf_active;
    var $r2_5_lf_passive;
    function get_r2_5_rt_active()
    {
        return $this->r2_5_rt_active;
    }
    function set_r2_5_rt_active($data)
    {
        if (!empty($data)) {
            $this->r2_5_rt_active = $data;
        }
    }
    function get_r2_5_rt_passive()
    {
        return $this->r2_5_rt_passive;
    }
    function set_r2_5_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r2_5_rt_passive = $data;
        }
    }
    function get_r2_5_lf_active()
    {
        return $this->r2_5_lf_active;
    }
    function set_r2_5_lf_active($data)
    {
        if (!empty($data)) {
            $this->r2_5_lf_active = $data;
        }
    }
    function get_r2_5_lf_passive()
    {
        return $this->r2_5_lf_passive;
    }
    function set_r2_5_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r2_5_lf_passive = $data;
        }
    }

    var $r2_6_rt_active;
    var $r2_6_rt_passive;
    var $r2_6_lf_active;
    var $r2_6_lf_passive;
    function get_r2_6_rt_active()
    {
        return $this->r2_6_rt_active;
    }
    function set_r2_6_rt_active($data)
    {
        if (!empty($data)) {
            $this->r2_6_rt_active = $data;
        }
    }
    function get_r2_6_rt_passive()
    {
        return $this->r2_6_rt_passive;
    }
    function set_r2_6_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r2_6_rt_passive = $data;
        }
    }
    function get_r2_6_lf_active()
    {
        return $this->r2_6_lf_active;
    }
    function set_r2_6_lf_active($data)
    {
        if (!empty($data)) {
            $this->r2_6_lf_active = $data;
        }
    }
    function get_r2_6_lf_passive()
    {
        return $this->r2_6_lf_passive;
    }
    function set_r2_6_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r2_6_lf_passive = $data;
        }
    }



    var $r3_1_rt_active;
    var $r3_1_rt_passive;
    var $r3_1_lf_active;
    var $r3_1_lf_passive;
    function get_r3_1_rt_active()
    {
        return $this->r3_1_rt_active;
    }
    function set_r3_1_rt_active($data)
    {
        if (!empty($data)) {
            $this->r3_1_rt_active = $data;
        }
    }
    function get_r3_1_rt_passive()
    {
        return $this->r3_1_rt_passive;
    }
    function set_r3_1_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r3_1_rt_passive = $data;
        }
    }
    function get_r3_1_lf_active()
    {
        return $this->r3_1_lf_active;
    }
    function set_r3_1_lf_active($data)
    {
        if (!empty($data)) {
            $this->r3_1_lf_active = $data;
        }
    }
    function get_r3_1_lf_passive()
    {
        return $this->r3_1_lf_passive;
    }
    function set_r3_1_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r3_1_lf_passive = $data;
        }
    }

    var $r3_2_rt_active;
    var $r3_2_rt_passive;
    var $r3_2_lf_active;
    var $r3_2_lf_passive;
    function get_r3_2_rt_active()
    {
        return $this->r3_2_rt_active;
    }
    function set_r3_2_rt_active($data)
    {
        if (!empty($data)) {
            $this->r3_2_rt_active = $data;
        }
    }
    function get_r3_2_rt_passive()
    {
        return $this->r3_2_rt_passive;
    }
    function set_r3_2_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r3_2_rt_passive = $data;
        }
    }
    function get_r3_2_lf_active()
    {
        return $this->r3_2_lf_active;
    }
    function set_r3_2_lf_active($data)
    {
        if (!empty($data)) {
            $this->r3_2_lf_active = $data;
        }
    }
    function get_r3_2_lf_passive()
    {
        return $this->r3_2_lf_passive;
    }
    function set_r3_2_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r3_2_lf_passive = $data;
        }
    }

    var $r3_3_rt_active;
    var $r3_3_rt_passive;
    var $r3_3_lf_active;
    var $r3_3_lf_passive;
    function get_r3_3_rt_active()
    {
        return $this->r3_3_rt_active;
    }
    function set_r3_3_rt_active($data)
    {
        if (!empty($data)) {
            $this->r3_3_rt_active = $data;
        }
    }
    function get_r3_3_rt_passive()
    {
        return $this->r3_3_rt_passive;
    }
    function set_r3_3_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r3_3_rt_passive = $data;
        }
    }
    function get_r3_3_lf_active()
    {
        return $this->r3_3_lf_active;
    }
    function set_r3_3_lf_active($data)
    {
        if (!empty($data)) {
            $this->r3_3_lf_active = $data;
        }
    }
    function get_r3_3_lf_passive()
    {
        return $this->r3_3_lf_passive;
    }
    function set_r3_3_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r3_3_lf_passive = $data;
        }
    }

    var $r3_4_rt_active;
    var $r3_4_rt_passive;
    var $r3_4_lf_active;
    var $r3_4_lf_passive;
    function get_r3_4_rt_active()
    {
        return $this->r3_4_rt_active;
    }
    function set_r3_4_rt_active($data)
    {
        if (!empty($data)) {
            $this->r3_4_rt_active = $data;
        }
    }
    function get_r3_4_rt_passive()
    {
        return $this->r3_4_rt_passive;
    }
    function set_r3_4_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r3_4_rt_passive = $data;
        }
    }
    function get_r3_4_lf_active()
    {
        return $this->r3_4_lf_active;
    }
    function set_r3_4_lf_active($data)
    {
        if (!empty($data)) {
            $this->r3_4_lf_active = $data;
        }
    }
    function get_r3_4_lf_passive()
    {
        return $this->r3_4_lf_passive;
    }
    function set_r3_4_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r3_4_lf_passive = $data;
        }
    }

    var $r3_5_rt_active;
    var $r3_5_rt_passive;
    var $r3_5_lf_active;
    var $r3_5_lf_passive;
    function get_r3_5_rt_active()
    {
        return $this->r3_5_rt_active;
    }
    function set_r3_5_rt_active($data)
    {
        if (!empty($data)) {
            $this->r3_5_rt_active = $data;
        }
    }
    function get_r3_5_rt_passive()
    {
        return $this->r3_5_rt_passive;
    }
    function set_r3_5_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r3_5_rt_passive = $data;
        }
    }
    function get_r3_5_lf_active()
    {
        return $this->r3_5_lf_active;
    }
    function set_r3_5_lf_active($data)
    {
        if (!empty($data)) {
            $this->r3_5_lf_active = $data;
        }
    }
    function get_r3_5_lf_passive()
    {
        return $this->r3_5_lf_passive;
    }
    function set_r3_5_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r3_5_lf_passive = $data;
        }
    }

    var $r3_6_rt_active;
    var $r3_6_rt_passive;
    var $r3_6_lf_active;
    var $r3_6_lf_passive;
    function get_r3_6_rt_active()
    {
        return $this->r3_6_rt_active;
    }
    function set_r3_6_rt_active($data)
    {
        if (!empty($data)) {
            $this->r3_6_rt_active = $data;
        }
    }
    function get_r3_6_rt_passive()
    {
        return $this->r3_6_rt_passive;
    }
    function set_r3_6_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r3_6_rt_passive = $data;
        }
    }
    function get_r3_6_lf_active()
    {
        return $this->r3_6_lf_active;
    }
    function set_r3_6_lf_active($data)
    {
        if (!empty($data)) {
            $this->r3_6_lf_active = $data;
        }
    }
    function get_r3_6_lf_passive()
    {
        return $this->r3_6_lf_passive;
    }
    function set_r3_6_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r3_6_lf_passive = $data;
        }
    }

    var $r3_7_rt_active;
    var $r3_7_rt_passive;
    var $r3_7_lf_active;
    var $r3_7_lf_passive;
    function get_r3_7_rt_active()
    {
        return $this->r3_7_rt_active;
    }
    function set_r3_7_rt_active($data)
    {
        if (!empty($data)) {
            $this->r3_7_rt_active = $data;
        }
    }
    function get_r3_7_rt_passive()
    {
        return $this->r3_7_rt_passive;
    }
    function set_r3_7_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r3_7_rt_passive = $data;
        }
    }
    function get_r3_7_lf_active()
    {
        return $this->r3_7_lf_active;
    }
    function set_r3_7_lf_active($data)
    {
        if (!empty($data)) {
            $this->r3_7_lf_active = $data;
        }
    }
    function get_r3_7_lf_passive()
    {
        return $this->r3_7_lf_passive;
    }
    function set_r3_7_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r3_7_lf_passive = $data;
        }
    }

    var $r3_8_rt_active;
    var $r3_8_rt_passive;
    var $r3_8_lf_active;
    var $r3_8_lf_passive;
    function get_r3_8_rt_active()
    {
        return $this->r3_8_rt_active;
    }
    function set_r3_8_rt_active($data)
    {
        if (!empty($data)) {
            $this->r3_8_rt_active = $data;
        }
    }
    function get_r3_8_rt_passive()
    {
        return $this->r3_8_rt_passive;
    }
    function set_r3_8_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r3_8_rt_passive = $data;
        }
    }
    function get_r3_8_lf_active()
    {
        return $this->r3_8_lf_active;
    }
    function set_r3_8_lf_active($data)
    {
        if (!empty($data)) {
            $this->r3_8_lf_active = $data;
        }
    }
    function get_r3_8_lf_passive()
    {
        return $this->r3_8_lf_passive;
    }
    function set_r3_8_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r3_8_lf_passive = $data;
        }
    }

    var $r3_9_rt_active;
    var $r3_9_rt_passive;
    var $r3_9_lf_active;
    var $r3_9_lf_passive;
    function get_r3_9_rt_active()
    {
        return $this->r3_9_rt_active;
    }
    function set_r3_9_rt_active($data)
    {
        if (!empty($data)) {
            $this->r3_9_rt_active = $data;
        }
    }
    function get_r3_9_rt_passive()
    {
        return $this->r3_9_rt_passive;
    }
    function set_r3_9_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r3_9_rt_passive = $data;
        }
    }
    function get_r3_9_lf_active()
    {
        return $this->r3_9_lf_active;
    }
    function set_r3_9_lf_active($data)
    {
        if (!empty($data)) {
            $this->r3_9_lf_active = $data;
        }
    }
    function get_r3_9_lf_passive()
    {
        return $this->r3_9_lf_passive;
    }
    function set_r3_9_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r3_9_lf_passive = $data;
        }
    }

    var $r3_10_rt_active;
    var $r3_10_rt_passive;
    var $r3_10_lf_active;
    var $r3_10_lf_passive;
    function get_r3_10_rt_active()
    {
        return $this->r3_10_rt_active;
    }
    function set_r3_10_rt_active($data)
    {
        if (!empty($data)) {
            $this->r3_10_rt_active = $data;
        }
    }
    function get_r3_10_rt_passive()
    {
        return $this->r3_10_rt_passive;
    }
    function set_r3_10_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r3_10_rt_passive = $data;
        }
    }
    function get_r3_10_lf_active()
    {
        return $this->r3_10_lf_active;
    }
    function set_r3_10_lf_active($data)
    {
        if (!empty($data)) {
            $this->r3_10_lf_active = $data;
        }
    }
    function get_r3_10_lf_passive()
    {
        return $this->r3_10_lf_passive;
    }
    function set_r3_10_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r3_10_lf_passive = $data;
        }
    }

    var $r3_11_rt_active;
    var $r3_11_rt_passive;
    var $r3_11_lf_active;
    var $r3_11_lf_passive;
    function get_r3_11_rt_active()
    {
        return $this->r3_11_rt_active;
    }
    function set_r3_11_rt_active($data)
    {
        if (!empty($data)) {
            $this->r3_11_rt_active = $data;
        }
    }
    function get_r3_11_rt_passive()
    {
        return $this->r3_11_rt_passive;
    }
    function set_r3_11_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r3_11_rt_passive = $data;
        }
    }
    function get_r3_11_lf_active()
    {
        return $this->r3_11_lf_active;
    }
    function set_r3_11_lf_active($data)
    {
        if (!empty($data)) {
            $this->r3_11_lf_active = $data;
        }
    }
    function get_r3_11_lf_passive()
    {
        return $this->r3_11_lf_passive;
    }
    function set_r3_11_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r3_11_lf_passive = $data;
        }
    }

    var $r3_12_rt_active;
    var $r3_12_rt_passive;
    var $r3_12_lf_active;
    var $r3_12_lf_passive;
    function get_r3_12_rt_active()
    {
        return $this->r3_12_rt_active;
    }
    function set_r3_12_rt_active($data)
    {
        if (!empty($data)) {
            $this->r3_12_rt_active = $data;
        }
    }
    function get_r3_12_rt_passive()
    {
        return $this->r3_12_rt_passive;
    }
    function set_r3_12_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r3_12_rt_passive = $data;
        }
    }
    function get_r3_12_lf_active()
    {
        return $this->r3_12_lf_active;
    }
    function set_r3_12_lf_active($data)
    {
        if (!empty($data)) {
            $this->r3_12_lf_active = $data;
        }
    }
    function get_r3_12_lf_passive()
    {
        return $this->r3_12_lf_passive;
    }
    function set_r3_12_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r3_12_lf_passive = $data;
        }
    }

    var $r3_13_rt_active;
    var $r3_13_rt_passive;
    var $r3_13_lf_active;
    var $r3_13_lf_passive;
    function get_r3_13_rt_active()
    {
        return $this->r3_13_rt_active;
    }
    function set_r3_13_rt_active($data)
    {
        if (!empty($data)) {
            $this->r3_13_rt_active = $data;
        }
    }
    function get_r3_13_rt_passive()
    {
        return $this->r3_13_rt_passive;
    }
    function set_r3_13_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r3_13_rt_passive = $data;
        }
    }
    function get_r3_13_lf_active()
    {
        return $this->r3_13_lf_active;
    }
    function set_r3_13_lf_active($data)
    {
        if (!empty($data)) {
            $this->r3_13_lf_active = $data;
        }
    }
    function get_r3_13_lf_passive()
    {
        return $this->r3_13_lf_passive;
    }
    function set_r3_13_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r3_13_lf_passive = $data;
        }
    }

    var $r3_14_rt_active;
    var $r3_14_rt_passive;
    var $r3_14_lf_active;
    var $r3_14_lf_passive;
    function get_r3_14_rt_active()
    {
        return $this->r3_14_rt_active;
    }
    function set_r3_14_rt_active($data)
    {
        if (!empty($data)) {
            $this->r3_14_rt_active = $data;
        }
    }
    function get_r3_14_rt_passive()
    {
        return $this->r3_14_rt_passive;
    }
    function set_r3_14_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r3_14_rt_passive = $data;
        }
    }
    function get_r3_14_lf_active()
    {
        return $this->r3_14_lf_active;
    }
    function set_r3_14_lf_active($data)
    {
        if (!empty($data)) {
            $this->r3_14_lf_active = $data;
        }
    }
    function get_r3_14_lf_passive()
    {
        return $this->r3_14_lf_passive;
    }
    function set_r3_14_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r3_14_lf_passive = $data;
        }
    }

    var $r3_15_rt_active;
    var $r3_15_rt_passive;
    var $r3_15_lf_active;
    var $r3_15_lf_passive;
    function get_r3_15_rt_active()
    {
        return $this->r3_15_rt_active;
    }
    function set_r3_15_rt_active($data)
    {
        if (!empty($data)) {
            $this->r3_15_rt_active = $data;
        }
    }
    function get_r3_15_rt_passive()
    {
        return $this->r3_15_rt_passive;
    }
    function set_r3_15_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r3_15_rt_passive = $data;
        }
    }
    function get_r3_15_lf_active()
    {
        return $this->r3_15_lf_active;
    }
    function set_r3_15_lf_active($data)
    {
        if (!empty($data)) {
            $this->r3_15_lf_active = $data;
        }
    }
    function get_r3_15_lf_passive()
    {
        return $this->r3_15_lf_passive;
    }
    function set_r3_15_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r3_15_lf_passive = $data;
        }
    }

    var $r3_16_rt_active;
    var $r3_16_rt_passive;
    var $r3_16_lf_active;
    var $r3_16_lf_passive;
    function get_r3_16_rt_active()
    {
        return $this->r3_16_rt_active;
    }
    function set_r3_16_rt_active($data)
    {
        if (!empty($data)) {
            $this->r3_16_rt_active = $data;
        }
    }
    function get_r3_16_rt_passive()
    {
        return $this->r3_16_rt_passive;
    }
    function set_r3_16_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r3_16_rt_passive = $data;
        }
    }
    function get_r3_16_lf_active()
    {
        return $this->r3_16_lf_active;
    }
    function set_r3_16_lf_active($data)
    {
        if (!empty($data)) {
            $this->r3_16_lf_active = $data;
        }
    }
    function get_r3_16_lf_passive()
    {
        return $this->r3_16_lf_passive;
    }
    function set_r3_16_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r3_16_lf_passive = $data;
        }
    }

    var $r3_17_rt_active;
    var $r3_17_rt_passive;
    var $r3_17_lf_active;
    var $r3_17_lf_passive;
    function get_r3_17_rt_active()
    {
        return $this->r3_17_rt_active;
    }
    function set_r3_17_rt_active($data)
    {
        if (!empty($data)) {
            $this->r3_17_rt_active = $data;
        }
    }
    function get_r3_17_rt_passive()
    {
        return $this->r3_17_rt_passive;
    }
    function set_r3_17_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r3_17_rt_passive = $data;
        }
    }
    function get_r3_17_lf_active()
    {
        return $this->r3_17_lf_active;
    }
    function set_r3_17_lf_active($data)
    {
        if (!empty($data)) {
            $this->r3_17_lf_active = $data;
        }
    }
    function get_r3_17_lf_passive()
    {
        return $this->r3_17_lf_passive;
    }
    function set_r3_17_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r3_17_lf_passive = $data;
        }
    }

    var $r3_18_rt_active;
    var $r3_18_rt_passive;
    var $r3_18_lf_active;
    var $r3_18_lf_passive;
    function get_r3_18_rt_active()
    {
        return $this->r3_18_rt_active;
    }
    function set_r3_18_rt_active($data)
    {
        if (!empty($data)) {
            $this->r3_18_rt_active = $data;
        }
    }
    function get_r3_18_rt_passive()
    {
        return $this->r3_18_rt_passive;
    }
    function set_r3_18_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r3_18_rt_passive = $data;
        }
    }
    function get_r3_18_lf_active()
    {
        return $this->r3_18_lf_active;
    }
    function set_r3_18_lf_active($data)
    {
        if (!empty($data)) {
            $this->r3_18_lf_active = $data;
        }
    }
    function get_r3_18_lf_passive()
    {
        return $this->r3_18_lf_passive;
    }
    function set_r3_18_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r3_18_lf_passive = $data;
        }
    }

    var $r3_19_rt_active;
    var $r3_19_rt_passive;
    var $r3_19_lf_active;
    var $r3_19_lf_passive;
    function get_r3_19_rt_active()
    {
        return $this->r3_19_rt_active;
    }
    function set_r3_19_rt_active($data)
    {
        if (!empty($data)) {
            $this->r3_19_rt_active = $data;
        }
    }
    function get_r3_19_rt_passive()
    {
        return $this->r3_19_rt_passive;
    }
    function set_r3_19_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r3_19_rt_passive = $data;
        }
    }
    function get_r3_19_lf_active()
    {
        return $this->r3_19_lf_active;
    }
    function set_r3_19_lf_active($data)
    {
        if (!empty($data)) {
            $this->r3_19_lf_active = $data;
        }
    }
    function get_r3_19_lf_passive()
    {
        return $this->r3_19_lf_passive;
    }
    function set_r3_19_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r3_19_lf_passive = $data;
        }
    }

    var $r3_20_rt_active;
    var $r3_20_rt_passive;
    var $r3_20_lf_active;
    var $r3_20_lf_passive;
    function get_r3_20_rt_active()
    {
        return $this->r3_20_rt_active;
    }
    function set_r3_20_rt_active($data)
    {
        if (!empty($data)) {
            $this->r3_20_rt_active = $data;
        }
    }
    function get_r3_20_rt_passive()
    {
        return $this->r3_20_rt_passive;
    }
    function set_r3_20_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r3_20_rt_passive = $data;
        }
    }
    function get_r3_20_lf_active()
    {
        return $this->r3_20_lf_active;
    }
    function set_r3_20_lf_active($data)
    {
        if (!empty($data)) {
            $this->r3_20_lf_active = $data;
        }
    }
    function get_r3_20_lf_passive()
    {
        return $this->r3_20_lf_passive;
    }
    function set_r3_20_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r3_20_lf_passive = $data;
        }
    }



    var $r4_1_rt_active;
    var $r4_1_rt_passive;
    var $r4_1_lf_active;
    var $r4_1_lf_passive;
    function get_r4_1_rt_active()
    {
        return $this->r4_1_rt_active;
    }
    function set_r4_1_rt_active($data)
    {
        if (!empty($data)) {
            $this->r4_1_rt_active = $data;
        }
    }
    function get_r4_1_rt_passive()
    {
        return $this->r4_1_rt_passive;
    }
    function set_r4_1_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r4_1_rt_passive = $data;
        }
    }
    function get_r4_1_lf_active()
    {
        return $this->r4_1_lf_active;
    }
    function set_r4_1_lf_active($data)
    {
        if (!empty($data)) {
            $this->r4_1_lf_active = $data;
        }
    }
    function get_r4_1_lf_passive()
    {
        return $this->r4_1_lf_passive;
    }
    function set_r4_1_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r4_1_lf_passive = $data;
        }
    }

    var $r4_2_rt_active;
    var $r4_2_rt_passive;
    var $r4_2_lf_active;
    var $r4_2_lf_passive;
    function get_r4_2_rt_active()
    {
        return $this->r4_2_rt_active;
    }
    function set_r4_2_rt_active($data)
    {
        if (!empty($data)) {
            $this->r4_2_rt_active = $data;
        }
    }
    function get_r4_2_rt_passive()
    {
        return $this->r4_2_rt_passive;
    }
    function set_r4_2_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r4_2_rt_passive = $data;
        }
    }
    function get_r4_2_lf_active()
    {
        return $this->r4_2_lf_active;
    }
    function set_r4_2_lf_active($data)
    {
        if (!empty($data)) {
            $this->r4_2_lf_active = $data;
        }
    }
    function get_r4_2_lf_passive()
    {
        return $this->r4_2_lf_passive;
    }
    function set_r4_2_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r4_2_lf_passive = $data;
        }
    }

    var $r4_3_rt_active;
    var $r4_3_rt_passive;
    var $r4_3_lf_active;
    var $r4_3_lf_passive;
    function get_r4_3_rt_active()
    {
        return $this->r4_3_rt_active;
    }
    function set_r4_3_rt_active($data)
    {
        if (!empty($data)) {
            $this->r4_3_rt_active = $data;
        }
    }
    function get_r4_3_rt_passive()
    {
        return $this->r4_3_rt_passive;
    }
    function set_r4_3_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r4_3_rt_passive = $data;
        }
    }
    function get_r4_3_lf_active()
    {
        return $this->r4_3_lf_active;
    }
    function set_r4_3_lf_active($data)
    {
        if (!empty($data)) {
            $this->r4_3_lf_active = $data;
        }
    }
    function get_r4_3_lf_passive()
    {
        return $this->r4_3_lf_passive;
    }
    function set_r4_3_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r4_3_lf_passive = $data;
        }
    }

    var $r4_4_rt_active;
    var $r4_4_rt_passive;
    var $r4_4_lf_active;
    var $r4_4_lf_passive;
    function get_r4_4_rt_active()
    {
        return $this->r4_4_rt_active;
    }
    function set_r4_4_rt_active($data)
    {
        if (!empty($data)) {
            $this->r4_4_rt_active = $data;
        }
    }
    function get_r4_4_rt_passive()
    {
        return $this->r4_4_rt_passive;
    }
    function set_r4_4_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r4_4_rt_passive = $data;
        }
    }
    function get_r4_4_lf_active()
    {
        return $this->r4_4_lf_active;
    }
    function set_r4_4_lf_active($data)
    {
        if (!empty($data)) {
            $this->r4_4_lf_active = $data;
        }
    }
    function get_r4_4_lf_passive()
    {
        return $this->r4_4_lf_passive;
    }
    function set_r4_4_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r4_4_lf_passive = $data;
        }
    }

    var $r4_5_rt_active;
    var $r4_5_rt_passive;
    var $r4_5_lf_active;
    var $r4_5_lf_passive;
    function get_r4_5_rt_active()
    {
        return $this->r4_5_rt_active;
    }
    function set_r4_5_rt_active($data)
    {
        if (!empty($data)) {
            $this->r4_5_rt_active = $data;
        }
    }
    function get_r4_5_rt_passive()
    {
        return $this->r4_5_rt_passive;
    }
    function set_r4_5_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r4_5_rt_passive = $data;
        }
    }
    function get_r4_5_lf_active()
    {
        return $this->r4_5_lf_active;
    }
    function set_r4_5_lf_active($data)
    {
        if (!empty($data)) {
            $this->r4_5_lf_active = $data;
        }
    }
    function get_r4_5_lf_passive()
    {
        return $this->r4_5_lf_passive;
    }
    function set_r4_5_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r4_5_lf_passive = $data;
        }
    }

    var $r4_6_rt_active;
    var $r4_6_rt_passive;
    var $r4_6_lf_active;
    var $r4_6_lf_passive;
    function get_r4_6_rt_active()
    {
        return $this->r4_6_rt_active;
    }
    function set_r4_6_rt_active($data)
    {
        if (!empty($data)) {
            $this->r4_6_rt_active = $data;
        }
    }
    function get_r4_6_rt_passive()
    {
        return $this->r4_6_rt_passive;
    }
    function set_r4_6_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r4_6_rt_passive = $data;
        }
    }
    function get_r4_6_lf_active()
    {
        return $this->r4_6_lf_active;
    }
    function set_r4_6_lf_active($data)
    {
        if (!empty($data)) {
            $this->r4_6_lf_active = $data;
        }
    }
    function get_r4_6_lf_passive()
    {
        return $this->r4_6_lf_passive;
    }
    function set_r4_6_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r4_6_lf_passive = $data;
        }
    }

    var $r4_7_rt_active;
    var $r4_7_rt_passive;
    var $r4_7_lf_active;
    var $r4_7_lf_passive;
    function get_r4_7_rt_active()
    {
        return $this->r4_7_rt_active;
    }
    function set_r4_7_rt_active($data)
    {
        if (!empty($data)) {
            $this->r4_7_rt_active = $data;
        }
    }
    function get_r4_7_rt_passive()
    {
        return $this->r4_7_rt_passive;
    }
    function set_r4_7_rt_passive($data)
    {
        if (!empty($data)) {
            $this->r4_7_rt_passive = $data;
        }
    }
    function get_r4_7_lf_active()
    {
        return $this->r4_7_lf_active;
    }
    function set_r4_7_lf_active($data)
    {
        if (!empty($data)) {
            $this->r4_7_lf_active = $data;
        }
    }
    function get_r4_7_lf_passive()
    {
        return $this->r4_7_lf_passive;
    }
    function set_r4_7_lf_passive($data)
    {
        if (!empty($data)) {
            $this->r4_7_lf_passive = $data;
        }
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
