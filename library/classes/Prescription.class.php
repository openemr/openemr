<?php

/**
 * Prescription.class.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Below list of terms are deprecated, but we keep this list
//   to keep track of the official openemr drugs terms and
//   corresponding ID's for reference. Official is referring
//   to the default settings after installing OpenEMR.
//
// define('UNIT_BLANK',0);
// define('UNIT_MG',1);
// define('UNIT_MG_1CC',2);
// define('UNIT_MG_2CC',3);
// define('UNIT_MG_3CC',4);
// define('UNIT_MG_4CC',5);
// define('UNIT_MG_5CC',6);
// define('UNIT_MCG',7);
// define('UNIT_GRAMS',8);
//
// define('INTERVAL_BLANK',0);
// define('INTERVAL_BID',1);
// define('INTERVAL_TID',2);
// define('INTERVAL_QID',3);
// define('INTERVAL_Q_3H',4);
// define('INTERVAL_Q_4H',5);
// define('INTERVAL_Q_5H',6);
// define('INTERVAL_Q_6H',7);
// define('INTERVAL_Q_8H',8);
// define('INTERVAL_QD',9);
// define('INTERVAL_AC',10); // added May 2008
// define('INTERVAL_PC',11); // added May 2008
// define('INTERVAL_AM',12); // added May 2008
// define('INTERVAL_PM',13); // added May 2008
// define('INTERVAL_ANTE',14); // added May 2008
// define('INTERVAL_H',15); // added May 2008
// define('INTERVAL_HS',16); // added May 2008
// define('INTERVAL_PRN',17); // added May 2008
// define('INTERVAL_STAT',18); // added May 2008
//
// define('FORM_BLANK',0);
// define('FORM_SUSPENSION',1);
// define('FORM_TABLET',2);
// define('FORM_CAPSULE',3);
// define('FORM_SOLUTION',4);
// define('FORM_TSP',5);
// define('FORM_ML',6);
// define('FORM_UNITS',7);
// define('FORM_INHILATIONS',8);
// define('FORM_GTTS_DROPS',9);
// define('FORM_CR',10);
// define('FORM_OINT',11);
//
// define('ROUTE_BLANK',0);
// define("ROUTE_PER_ORIS", 1);
// define("ROUTE_PER_RECTUM", 2);
// define("ROUTE_TO_SKIN", 3);
// define("ROUTE_TO_AFFECTED_AREA", 4);
// define("ROUTE_SUBLINGUAL", 5);
// define("ROUTE_OS", 6);
// define("ROUTE_OD", 7);
// define("ROUTE_OU", 8);
// define("ROUTE_SQ", 9);
// define("ROUTE_IM", 10);
// define("ROUTE_IV", 11);
// define("ROUTE_PER_NOSTRIL", 12);
// define("ROUTE_B_EAR", 13);
// define("ROUTE_L_EAR", 14);
// define("ROUTE_R_EAR", 15);
//
// define('SUBSTITUTE_YES',1);
// define('SUBSTITUTE_NO',2);
//


require_once(dirname(__FILE__) . "/../lists.inc");


/**
 * class Prescription
 *
 */

use OpenEMR\Common\ORDataObject\ORDataObject;

class Prescription extends ORDataObject
{

    /**
     *
     * @access public
     */

    /**
     *
     * static
     */
    var $form_array;
    var $unit_array;
    var $route_array;
    var $interval_array;
    var $substitute_array;
    var $medication_array;
    var $refills_array;

    /**
     *
     * @access private
     */

    var $id;
    var $patient;
    var $pharmacist;
    var $date_added;
    var $txDate;
    var $date_modified;
    var $pharmacy;
    var $start_date;
    var $filled_date;
    var $provider;
    var $note;
    var $drug;
    var $rxnorm_drugcode;
    var $form;
    var $dosage;
    var $quantity;
    var $size;
    var $unit;
    var $route;
    var $interval;
    var $substitute;
    var $refills;
    var $per_refill;
    var $medication;

    var $drug_id;
    var $active;
    var $ntx;

    /**
    * Constructor sets all Prescription attributes to their default value
    */

    function __construct($id = "", $_prefix = "")
    {
        $this->route_array = $this->load_drug_attributes('drug_route');
        $this->form_array = $this->load_drug_attributes('drug_form');
        $this->interval_array = $this->load_drug_attributes('drug_interval');
        $this->unit_array = $this->load_drug_attributes('drug_units');

        $this->substitute_array = array("",xl("substitution allowed"),
            xl("do not substitute"));

        $this->medication_array = array(0 => xl('No'), 1 => xl('Yes'));

        if (is_numeric($id)) {
            $this->id = $id;
        } else {
            $id = "";
        }

        //$this->unit = UNIT_MG;
        //$this->route = ROUTE_PER_ORIS;
        //$this->quantity = 1;
        //$this->size = 1;
            $this->refills = 0;
        //$this->form = FORM_TABLET;
            $this->substitute = false;
            $this->_prefix = $_prefix;
            $this->_table = "prescriptions";
            $this->pharmacy = new Pharmacy();
            $this->pharmacist = new Person();
            $this->provider = new Provider($_SESSION['authUserID']);
            $this->patient = new Patient();
            $this->start_date = date("Y-m-d");
            $this->date_added = date("Y-m-d");
            $this->date_modified = date("Y-m-d");
            $this->per_refill = 0;
            $this->note = "";

            $this->drug_id = 0;
            $this->active = 1;

        $this->ntx = 0;

        for ($i = 0; $i < 21; $i++) {
            $this->refills_array[$i] = sprintf("%02d", $i);
        }

        if ($id != "") {
            $this->populate();
        }
    }

    function persist()
    {
        $this->date_modified = date("Y-m-d");
        if ($this->id == "") {
            $this->date_added = date("Y-m-d");
        }

        if (parent::persist()) {
        }
    }

    function populate()
    {
        parent::populate();
    }

    function toString($html = false)
    {
        $string .= "\n"
            . "ID: " . $this->id . "\n"
            . "Patient:" . $this->patient . "\n"
            . "Patient ID:" . $this->patient->id . "\n"
            . "Pharmacist: " . $this->pharmacist . "\n"
            . "Pharmacist ID: " . $this->pharmacist->id . "\n"
            . "Date Added: " . $this->date_added . "\n"
            . "Date Modified: " . $this->date_modified . "\n"
            . "Pharmacy: " . $this->pharmacy . "\n"
            . "Pharmacy ID:" . $this->pharmacy->id . "\n"
            . "Start Date: " . $this->start_date . "\n"
            . "Filled Date: " . $this->filled_date . "\n"
            . "Provider: " . $this->provider . "\n"
            . "Provider ID: " . $this->provider->id . "\n"
            . "Note: " . $this->note . "\n"
            . "Drug: " . $this->drug . "\n"
          . "Code: " . $this->rxnorm_drugcode . "\n"
            . "Form: " . $this->form_array[$this->form] . "\n"
            . "Dosage: " . $this->dosage . "\n"
            . "Qty: " . $this->quantity . "\n"
            . "Size: " . $this->size . "\n"
            . "Unit: " . $this->unit_array[$this->unit] . "\n"
            . "Route: " . $this->route_array[$this->route] . "\n"
            . "Interval: " . $this->interval_array[$this->interval] . "\n"
            . "Substitute: " . $this->substitute_array[$this->substitute] . "\n"
            . "Refills: " . $this->refills . "\n"
            . "Per Refill: " . $this->per_refill . "\n"
            . "Drug ID: " . $this->drug_id . "\n"
            . "Active: " . $this->active . "\n"
            . "Transmitted: " . $this->ntx;

        if ($html) {
            return nl2br($string);
        } else {
            return $string;
        }
    }

    private function load_drug_attributes($id)
    {
        $res = sqlStatement("SELECT * FROM list_options WHERE list_id = ? AND activity = 1 ORDER BY seq", array($id));
        while ($row = sqlFetchArray($res)) {
            if ($row['title'] == '') {
                $arr[$row['option_id']] = ' ';
            } else {
                $arr[$row['option_id']] = xl_list_label($row['title']);
            }
        }

        return $arr;
    }

    function get_encounter()
    {
        return $_SESSION['encounter'];
    }

    function get_unit_display($display_form = "")
    {
        return( $this->unit_array[$this->unit] );
    }

    function get_unit()
    {
        return $this->unit;
    }
    function set_unit($unit)
    {
        if (is_numeric($unit)) {
            $this->unit = $unit;
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

    function get_dosage_display($display_form = "")
    {
        if (empty($this->form) && empty($this->interval)) {
            return( $this->dosage );
        } else {
            return ($this->dosage . " " . xl('in') . " " . $this->form_array[$this->form] . " " . $this->interval_array[$this->interval]);
        }
    }

    function set_dosage($dosage)
    {
        $this->dosage = $dosage;
    }
    function get_dosage()
    {
        return $this->dosage;
    }

    function set_form($form)
    {
        if (is_numeric($form)) {
            $this->form = $form;
        }
    }
    function get_form()
    {
        return $this->form;
    }

    function set_refills($refills)
    {
        if (is_numeric($refills)) {
            $this->refills = $refills;
        }
    }
    function get_refills()
    {
        return $this->refills;
    }

    function set_size($size)
    {
        $this->size = preg_replace("/[^0-9\/\.\-]/", "", $size);
    }
    function get_size()
    {
        return $this->size;
    }

    function set_quantity($qty)
    {
        $this->quantity = $qty;
    }
    function get_quantity()
    {
        return $this->quantity;
    }

    function set_route($route)
    {
        if (is_numeric($route)) {
            $this->route = $route;
        }
    }
    function get_route()
    {
        return $this->route;
    }

    function set_interval($interval)
    {
        if (is_numeric($interval)) {
            $this->interval = $interval;
        }
    }
    function get_interval()
    {
        return $this->interval;
    }

    function set_substitute($sub)
    {
        if (is_numeric($sub)) {
            $this->substitute = $sub;
        }
    }
    function get_substitute()
    {
        return $this->substitute;
    }
    function set_erx_source($erx_source)
    {
        $this->erx_source = $erx_source;
    }
    function set_medication($med)
    {
        global $ISSUE_TYPES;

        $this->medication = $med;

        // Avoid making a mess if we are not using the "medication" issue type.
        if (isset($ISSUE_TYPES) && !$ISSUE_TYPES['medication']) {
            return;
        }

        //below statements are bypassing the persist() function and being used directly in database statements, hence need to use the functions in library/formdata.inc.php
        // they have already been run through populate() hence stripped of escapes, so now need to be escaped for database (add_escape_custom() function).

        //check if this drug is on the medication list
        $dataRow = sqlQuery("select id from lists where type = 'medication' and activity = 1 and (enddate is null or cast(now() as date) < enddate) and upper(trim(title)) = upper(trim('" . add_escape_custom($this->drug) . "')) and pid = '" . add_escape_custom($this->patient->id) . "' limit 1");

        if ($med && !isset($dataRow['id'])) {
            $dataRow = sqlQuery("select id from lists where type = 'medication' and activity = 0 and (enddate is null or cast(now() as date) < enddate) and upper(trim(title)) = upper(trim('" . add_escape_custom($this->drug) . "')) and pid = '" . add_escape_custom($this->patient->id) . "' limit 1");

            if (!isset($dataRow['id'])) {
                //add the record to the medication list
                sqlStatement("insert into lists(date,begdate,type,activity,pid,user,groupname,title) values (now(),cast(now() as date),'medication',1,'" . add_escape_custom($this->patient->id) . "','" . add_escape_custom($_SESSION['authUser']) . "','" . add_escape_custom($_SESSION['authProvider']) . "','" . add_escape_custom($this->drug) . "')");
            } else {
                $dataRow = sqlQuery('update lists set activity = 1'
                            . " ,user = '" . add_escape_custom($_SESSION['authUser'])
                            . "', groupname = '" . add_escape_custom($_SESSION['authProvider']) . "' where id = '" . add_escape_custom($dataRow['id']) . "'");
            }
        } elseif (!$med && isset($dataRow['id'])) {
            //remove the drug from the medication list if it exists
            $dataRow = sqlQuery('update lists set activity = 0'
                            . " ,user = '" . add_escape_custom($_SESSION['authUser'])
                            . "', groupname = '" . add_escape_custom($_SESSION['authProvider']) . "' where id = '" . add_escape_custom($dataRow['id']) . "'");
        }
    }

    function get_medication()
    {
        return $this->medication;
    }

    function set_per_refill($pr)
    {
        if (is_numeric($pr)) {
            $this->per_refill = $pr;
        }
    }
    function get_per_refill()
    {
        return $this->per_refill;
    }

    function set_patient_id($id)
    {
        if (is_numeric($id)) {
            $this->patient = new Patient($id);
        }
    }
    function get_patient_id()
    {
        return $this->patient->id;
    }

    function set_provider_id($id)
    {
        if (is_numeric($id)) {
            $this->provider = new Provider($id);
        }
    }
    function get_provider_id()
    {
        return $this->provider->id;
    }

    function set_provider($pobj)
    {
        if (get_class($pobj) == "provider") {
            $this->provider = $pobj;
        }
    }

    function set_pharmacy_id($id)
    {
        if (is_numeric($id)) {
            $this->pharmacy = new Pharmacy($id);
        }
    }
    function get_pharmacy_id()
    {
        return $this->pharmacy->id;
    }

    function set_pharmacist_id($id)
    {
        if (is_numeric($id)) {
            $this->pharmacist = new Person($id);
        }
    }
    function get_pharmacist()
    {
        return $this->pharmacist->id;
    }

    function get_start_date_y()
    {
        $ymd = explode("-", $this->start_date);
        return $ymd[0];
    }
    function set_start_date_y($year)
    {
        if (is_numeric($year)) {
            $ymd = explode("-", $this->start_date);
            $ymd[0] = $year;
            $this->start_date = $ymd[0] . "-" . $ymd[1] . "-" . $ymd[2];
        }
    }
    function get_start_date_m()
    {
        $ymd = explode("-", $this->start_date);
        return $ymd[1];
    }
    function set_start_date_m($month)
    {
        if (is_numeric($month)) {
            $ymd = explode("-", $this->start_date);
            $ymd[1] = $month;
            $this->start_date = $ymd[0] . "-" . $ymd[1] . "-" . $ymd[2];
        }
    }
    function get_start_date_d()
    {
        $ymd = explode("-", $this->start_date);
        return $ymd[2];
    }
    function set_start_date_d($day)
    {
        if (is_numeric($day)) {
            $ymd = explode("-", $this->start_date);
            $ymd[2] = $day;
            $this->start_date = $ymd[0] . "-" . $ymd[1] . "-" . $ymd[2];
        }
    }
    function get_start_date()
    {
        return $this->start_date;
    }
    function set_start_date($date)
    {
        return $this->start_date = $date;
    }

    // TajEmo work by CB 2012/05/30 01:56:32 PM added encounter for auto ticking of checkboxes
    function set_encounter($enc)
    {
        return $this->encounter = $enc;
    }

    function get_date_added()
    {
        return $this->date_added;
    }
    function set_date_added($date)
    {
        return $this->date_added = $date;
    }
    function set_txDate($txdate)
    {
        return $this->txDate = $txdate;
    }
    function get_txDate()
    {
        return $this->txDate;
    }

    function get_date_modified()
    {
        return $this->date_modified;
    }
    function set_date_modified($date)
    {
        return $this->date_modified = $date;
    }

    function get_filled_date()
    {
        return $this->filled_date;
    }
    function set_filled_date($date)
    {
        return $this->filled_date = $date;
    }

    function set_note($note)
    {
        $this->note = $note;
    }
    function get_note()
    {
        return $this->note;
    }

    function set_drug($drug)
    {
        $this->drug = $drug;
    }
    function get_drug()
    {
        return $this->drug;
    }
    function set_ntx($ntx)
    {
        $this->ntx = $ntx;
    }
    function get_ntx()
    {
        return $this->ntx;
    }

    function set_rxnorm_drugcode($rxnorm_drugcode)
    {
        $this->rxnorm_drugcode = $rxnorm_drugcode;
    }
    function get_rxnorm_drugcode()
    {
        return $this->rxnorm_drugcode;
    }

    function get_filled_by_id()
    {
        return $this->pharmacist->id;
    }
    function set_filled_by_id($id)
    {
        if (is_numeric($id)) {
            return $this->pharmacist->id = $id;
        }
    }

    function set_drug_id($drug_id)
    {
        $this->drug_id = $drug_id;
    }
    function get_drug_id()
    {
        return $this->drug_id;
    }

    function set_active($active)
    {
        $this->active = $active;
    }
    function get_active()
    {
        return $this->active;
    }
    function get_prescription_display()
    {
        $pconfig = $GLOBALS['oer_config']['prescriptions'];

        switch ($pconfig['format']) {
            case "FL":
                return $this->get_prescription_florida_display();
                break;
            default:
                break;
        }

        $sql = "SELECT * FROM users JOIN facility AS f ON f.name = users.facility where users.id ='" . add_escape_custom($this->provider->id) . "'";
        $db = get_db();
        $results = $db->Execute($sql);
        if (!$results->EOF) {
            $string = $results->fields['name'] . "\n"
                    . $results->fields['street'] . "\n"
                    . $results->fields['city'] . ", " . $results->fields['state'] . " " . $results->fields['postal_code'] . "\n"
                    . $results->fields['phone'] . "\n\n";
        }

        $string .= ""
                . "Prescription For:" . "\t" . $this->patient->get_name_display() . "\n"
                . "DOB:" . "\t" . $this->patient->get_dob() . "\n"
                . "Start Date: " . "\t\t" . $this->start_date . "\n"
                . "Provider: " . "\t\t" . $this->provider->get_name_display() . "\n"
                . "Provider DEA No.: " . "\t\t" . $this->provider->federal_drug_id . "\n"
                . "Drug: " . "\t\t\t" . $this->drug . "\n"
                . "Dosage: " . "\t\t" . $this->dosage . " in " . $this->form_array[$this->form] . " form " . $this->interval_array[$this->interval] . "\n"
                . "Qty: " . "\t\t\t" . $this->quantity . "\n"
                . "Medication Unit: " . "\t" . $this->size  . " " . $this->unit_array[$this->unit] . "\n"
                . "Substitute: " . "\t\t" . $this->substitute_array[$this->substitute] . "\n";
        if ($this->refills > 0) {
            $string .= "Refills: " . "\t\t" . $this->refills . ", of quantity: " . $this->per_refill . "\n";
        }

        $string .= "\n" . "Notes: \n" . $this->note . "\n";
        return $string;
    }

    function get_prescription_florida_display()
    {

        $db = get_db();
        $ntt = new NumberToText($this->quantity);
        $ntt2 = new NumberToText($this->per_refill);
        $ntt3 = new NumberToText($this->refills);

        $string = "";

        $gnd = $this->provider->get_name_display();

        while (strlen($gnd) < 31) {
            $gnd .= " ";
        }

        $string .= $gnd . $this->provider->federal_drug_id . "\n";

        $sql = "SELECT * FROM users JOIN facility AS f ON f.name = users.facility where users.id ='" . add_escape_custom($this->provider->id) . "'";
        $results = $db->Execute($sql);

        if (!$results->EOF) {
            $rfn = $results->fields['name'];

            while (strlen($rfn) < 31) {
                $rfn .= " ";
            }

            $string .= $rfn . $this->provider->get_provider_number_default() . "\n"
                    . $results->fields['street'] . "\n"
                    . $results->fields['city'] . ", " . $results->fields['state'] . " " . $results->fields['postal_code'] . "\n"
                    . $results->fields['phone'] . "\n";
        }

        $string .= "\n";
        $string .= strtoupper($this->patient->lname) . ", " . ucfirst($this->patient->fname) . " " . $this->patient->mname . "\n";
        $string .= "DOB " .  $this->patient->date_of_birth . "\n";
        $string .= "\n";
        $string .= date("F j, Y", strtotime($this->start_date)) . "\n";
        $string .= "\n";
        $string .= strtoupper($this->drug) . " " . $this->size  . " " . $this->unit_array[$this->unit] . "\n";
        if (strlen($this->note) > 0) {
            $string .= "Notes: \n" . $this->note . "\n";
        }

        if (!empty($this->dosage)) {
            $string .= $this->dosage;
            if (!empty($this->form)) {
                $string .= " " . $this->form_array[$this->form];
            }

            if (!empty($this->interval)) {
                $string .= " " . $this->interval_array[$this->interval];
            }

            if (!empty($this->route)) {
                $string .= " " . $this->route_array[$this->route] . "\n";
            }
        }

        if (!empty($this->quantity)) {
            $string .= "Disp: " . $this->quantity . " (" . trim(strtoupper($ntt->convert())) . ")" . "\n";
        }

        $string .= "\n";
        $string .= "Refills: " . $this->refills . " (" . trim(strtoupper($ntt3->convert())) . "), Per Refill Disp: " . $this->per_refill . " (" . trim(strtoupper($ntt2->convert())) . ")" . "\n";
        $string .= $this->substitute_array[$this->substitute] . "\n";
        $string .= "\n";

        return $string;
    }

    static function prescriptions_factory(
        $patient_id,
        $order_by = "active DESC, date_modified DESC, date_added DESC"
    ) {

        $prescriptions = array();
        $p = new Prescription();
        $sql = "SELECT id FROM " . escape_table_name($p->_table) . " WHERE patient_id = ? " .
                "ORDER BY " . add_escape_custom($order_by);
        $results = sqlQ($sql, array($patient_id));
        while ($row = sqlFetchArray($results)) {
            $prescriptions[] = new Prescription($row['id']);
        }

        return $prescriptions;
    }

    function get_dispensation_count()
    {
        if (empty($this->id)) {
            return 0;
        }

        $refills_row = sqlQuery("SELECT count(*) AS count FROM drug_sales " .
                    "WHERE prescription_id = ? AND quantity > 0", [$this->id]);
        return $refills_row['count'];
    }
}// end of Prescription
