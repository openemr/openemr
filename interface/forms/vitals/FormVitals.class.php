<?php

define("EVENT_VEHICLE", 1);
define("EVENT_WORK_RELATED", 2);
define("EVENT_SLIP_FALL", 3);
define("EVENT_OTHER", 4);


/**
 * class FormHpTjePrimary
 *
 */

use OpenEMR\Common\ORDataObject\ORDataObject;

class FormVitals extends ORDataObject
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
    public $bps;
    public $bpd;
    public $weight;
    public $height;
    public $temperature;
    public $temp_method;
    public $pulse;
    public $respiration;
    public $note;
    public $BMI;
    public $BMI_status;
    public $waist_circ;
    public $head_circ;
    public $oxygen_saturation;
    public $oxygen_flow_rate;

    // public $temp_methods;
    /**
     * Constructor sets all Form attributes to their default value
     */

    public function __construct($id = "", $_prefix = "")
    {
        parent::__construct();
        if ($id > 0) {
            $this->id = $id;
        } else {
            $id = "";
            $this->date = $this->get_date();
            $this->user = $_SESSION['authUser'];
            $this->groupname = $_SESSION['authProvider'];
        }

        $this->_table = "form_vitals";
        $this->activity = 1;
        $this->pid = $GLOBALS['pid'];
        if (!empty($id)) {
            $this->populate();
        }
    }
    public function populate()
    {
        parent::populate();
        //$this->temp_methods = parent::_load_enum("temp_locations",false);
    }

    public function toString($html = false)
    {
        $string .= "\n"
            . "ID: " . $this->id . "\n";

        if ($html) {
            return nl2br($string);
        }

        return $string;
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
        if (!$this->date) {
            $this->date = date('YmdHis', time());
        }

        return $this->date;
    }

    public function set_date($dt)
    {
        if (!empty($dt)) {
            $dt = str_replace(array('-', ':', ' '), '', $dt);
            while (strlen($dt) < 14) {
                $dt .= '0';
            }

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

    public function get_groupname()
    {
        return $this->groupname;
    }
    public function set_groupname($g)
    {
        if (!empty($g)) {
            $this->groupname = $g;
        }
    }

    public function get_bps()
    {
        return $this->bps;
    }
    public function set_bps($bps)
    {
        if (!empty($bps)) {
            $this->bps = $bps;
        }
    }
    public function get_bpd()
    {
        return $this->bpd;
    }
    public function set_bpd($bpd)
    {
        if (!empty($bpd)) {
            $this->bpd = $bpd;
        }
    }
    public function get_weight()
    {
        return $this->weight;
    }
    public function set_weight($w)
    {
        if (!empty($w) && is_numeric($w)) {
            $this->weight = $w;
        }
    }
    public function display_weight($pounds)
    {
        if ($pounds != 0) {
            if ($GLOBALS['us_weight_format'] == 2) {
                $pounds_int = floor($pounds);
                return $pounds_int . " " . xl('lb') . " " . round(($pounds - $pounds_int) * 16) . " " . xl('oz');
            } else {
                return $pounds;
            }
        }
    }
    public function get_height()
    {
        return $this->height;
    }
    public function set_height($h)
    {
        if (!empty($h) && is_numeric($h)) {
            $this->height = $h;
        }
    }
    public function get_temperature()
    {
        return $this->temperature;
    }
    public function set_temperature($t)
    {
        if (!empty($t) && is_numeric($t)) {
            $this->temperature = $t;
        }
    }
    public function get_temp_method()
    {
        return $this->temp_method;
    }
    public function set_temp_method($tm)
    {
        $this->temp_method = $tm;
    }
    // public function get_temp_methods() {
    //  return $this->temp_methods;
    // }
    public function get_pulse()
    {
        return $this->pulse;
    }
    public function set_pulse($p)
    {
        if (!empty($p) && is_numeric($p)) {
            $this->pulse = $p;
        }
    }
    public function get_respiration()
    {
        return $this->respiration;
    }
    public function set_respiration($r)
    {
        if (!empty($r) && is_numeric($r)) {
            $this->respiration = $r;
        }
    }
    public function get_note()
    {
        return $this->note;
    }
    public function set_note($n)
    {
        if (!empty($n)) {
            $this->note = $n;
        }
    }
    public function get_BMI()
    {
        return $this->BMI;
    }
    public function set_BMI($bmi)
    {
        if (!empty($bmi) && is_numeric($bmi)) {
            $this->BMI = $bmi;
        }
    }
    public function get_BMI_status()
    {
        return $this->BMI_status;
    }
    public function set_BMI_status($status)
    {
        $this->BMI_status = $status;
    }
    public function get_waist_circ()
    {
        return $this->waist_circ;
    }
    public function set_waist_circ($w)
    {
        if (!empty($w) && is_numeric($w)) {
            $this->waist_circ = $w;
        }
    }
    public function get_head_circ()
    {
        return $this->head_circ;
    }
    public function set_head_circ($h)
    {
        if (!empty($h) && is_numeric($h)) {
            $this->head_circ = $h;
        }
    }
    public function get_oxygen_saturation()
    {
        return $this->oxygen_saturation;
    }
    public function set_oxygen_saturation($o)
    {
        if (!empty($o) && is_numeric($o)) {
            $this->oxygen_saturation = $o;
        }
    }
    public function get_oxygen_flow_rate()
    {
        return $this->oxygen_flow_rate;
    }
    public function set_oxygen_flow_rate($o)
    {
        if (!empty($o) && is_numeric($o)) {
            $this->oxygen_flow_rate = $o;
        } else {
            $this->oxygen_flow_rate = 0.00;
        }
    }
    public function persist()
    {
        parent::persist();
    }
}   // end of Form
