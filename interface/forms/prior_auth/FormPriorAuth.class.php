<?php

/**
 * prior auth form
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\ORDataObject\ORDataObject;
use OpenEMR\Core\OEGlobalsBag;

/**
 * class PriorAuth
 *
 */
class FormPriorAuth extends ORDataObject implements \Stringable
{
    /**
     *
     * @access public
     */



    /**
     *
     * @access private
     */

    public $id;
    public $pid;
    public $activity;
    public $date;
    public $prior_auth_number;
    public $comments;
    public $date_from;
    public $date_to;

    /**
     * Constructor sets all Form attributes to their default value
     */

    public function __construct($id = "")
    {
        parent::__construct();

        $this->_table = "form_prior_auth";

        if (is_numeric($id)) {
            $this->id = $id;
        } else {
            $id = "";
        }

        $this->pid = OEGlobalsBag::getInstance()->get('pid');
        $this->activity = 1;
        $this->date = date("Y-m-d H:i:s");
        $this->prior_auth_number = "";
        $this->date_from = date("Y-m-d");
        $this->date_to = null;

        if ($id != "") {
            $this->populate();
        }
    }

    public function __toString(): string
    {
        return "ID: " . $this->id . "\n";
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


    public function set_comments($string)
    {
        $this->comments = $string;
    }

    public function get_comments()
    {
        return $this->comments;
    }

    public function set_prior_auth_number($string)
    {
        $this->prior_auth_number = $string;
    }

    public function get_prior_auth_number()
    {
        return $this->prior_auth_number;
    }


    public function get_date()
    {
        return $this->date;
    }

    public function get_date_from()
    {
        return $this->date_from;
    }

    public function set_date_from($dt)
    {
        $this->date_from = $dt;
    }

    public function get_date_to()
    {
        return $this->date_to;
    }

    public function set_date_to($dt)
    {
        $this->date_to = $dt;
    }
}
// end of Form
