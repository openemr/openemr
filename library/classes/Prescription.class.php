<?php

	require_once (dirname(__FILE__) ."/../sql.inc");
	require_once("ORDataObject.class.php");
	require_once("Patient.class.php");
	require_once("Person.class.php");
	require_once("Provider.class.php");
	require_once("Pharmacy.class.php");
	require_once("NumberToText.class.php");

	define('UNIT_MG',1);
	define('UNIT_MG_1CC',2);
	define('UNIT_MG_2CC',3);
	define('UNIT_MG_3CC',4);
	define('UNIT_MG_4CC',5);
	define('UNIT_MG_5CC',6);
	define('UNIT_GRAMS',7);
	define('UNIT_MCG',8);
	define('INTERVAL_BID',1);
	define('INTERVAL_TID',2);
	define('INTERVAL_QID',3);
	define('INTERVAL_Q_3H',4);
	define('INTERVAL_Q_4H',5);
	define('INTERVAL_Q_5H',6);
	define('INTERVAL_Q_6H',7);
	define('INTERVAL_Q_8H',8);
	define('INTERVAL_QD',9);
	define('SUBSTITUTE_YES',1);
	define('SUBSTITUTE_NO',2);
	
	define('FORM_SUSPENSION',1);
	define('FORM_TABLET',2);
	define('FORM_CAPSULE',3);
	define('FORM_SOLUTION',4);
	define('FORM_TSP',5);
	define('FORM_ML',6);
	define('FORM_UNITS',7);
	define('FORM_INHILATIONS',8);
	define('FORM_GTTS_DROPS',9);

	define("ROUTE_PER_ORIS", 1);
	define("ROUTE_PER_RECTUM", 2);
	define("ROUTE_TO_SKIN", 3);
	define("ROUTE_TO_AFFECTED_AREA", 4);
	define("ROUTE_SUBLINGUAL", 5);
	define("ROUTE_OS", 6);
	define("ROUTE_OD", 7);
	define("ROUTE_OU", 8);
	define("ROUTE_SQ", 9);
	define("ROUTE_IM", 10);
	define("ROUTE_IV", 11);
	define("ROUTE_PER_NOSTRIL", 12);

/**
 * class Prescription
 *
 */
class Prescription extends ORDataObject {

	/**
	 *
	 * @access public
	 */


	/**
	 *
	 * static
	 */
	var $form_array = array(" ",FORM_TABLET => "tablet", FORM_CAPSULE => "capsule", FORM_TSP => "tsp", FORM_ML => "ml", FORM_UNITS => "units", 
							FORM_INHILATIONS => "inhilations", FORM_GTTS_DROPS => "gtts(drops)");
	var $unit_array = array(" ","mg","mg/1cc","","","","mg/5cc","mcg");
	var $route_array = array(" ","Per Oris","Per Rectum","To Skin","To Affected Area","Sublingual", "OS", "OD", "OU", "SQ", "IM", "IV", "Per Nostril");
	var $interval_array = array(" ","b.i.d.","t.i.d.","q.i.d.","q.3h","q.4h","q.5h","q.6h","q.8h","q.d.");
	var $substitute_array = array("","substitution allowed","substitution not allowed");
	var $refills_array;

	/**
	 *
	 * @access private
	 */

	var $id;
	var $patient;
	var $pharmacist;
	var $date_added;
	var $date_modified;
	var $pharmacy;
	var $start_date;
	var $filled_date;
	var $provider;
	var $note;
	var $drug;
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


	/**
	 * Constructor sets all Prescription attributes to their default value
	 */

	function Prescription($id= "", $_prefix = "")	{
		if (is_numeric($id)) {
			$this->id = $id;
		}
		else {
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
		$this->provider = new Provider($_SESSION['authId']);
		$this->patient = new Patient();
		$this->start_date = date("Y-m-d");
		$this->date_added = date("Y-m-d");
		$this->date_modified = date("Y-m-d");
		$this->per_refill = 0;
		$this->note = "";
		for($i=0;$i<21;$i++) {
			$this->refills_array[$i] = sprintf("%02d",$i);
		}
		if ($id != "") {
			$this->populate();
		}
	}

	function persist() {

		$this->date_modified = date("Y-m-d");
		if ($this->id == "") {
			$this->date_added = date("Y-m-d");
		}
		if (parent::persist()) {
		}

	}

	function populate() {
		parent::populate();
	}

	function toString($html = false) {
		$string .= "\n"
			."ID: " . $this->id . "\n"
			."Patient:" . $this->patient . "\n"
			."Patient ID:" . $this->patient->id . "\n"
			."Pharmacist: " . $this->pharmacist . "\n"
			."Pharmacist ID: " . $this->pharmacist->id . "\n"
			."Date Added: " . $this->date_added. "\n"
			."Date Modified: " . $this->date_modified. "\n"
			."Pharmacy: " . $this->pharmacy. "\n"
			."Pharmacy ID:" . $this->pharmacy->id . "\n"
			."Start Date: " . $this->start_date. "\n"
			."Filled Date: " . $this->filled_date. "\n"
			."Provider: " . $this->provider. "\n"
			."Provider ID: " . $this->provider->id. "\n"
			."Note: " . $this->note. "\n"
			."Drug: " . $this->drug. "\n"
			."Form: " . $this->form_array[$this->form]. "\n"
			."Dosage: " . $this->dosage. "\n"
			."Qty: " . $this->quantity. "\n"
			."Size: " . $this->size. "\n"
			."Unit: " . $this->unit_array[$this->unit] . "\n"
			."Route: " . $this->route_array[$this->route] . "\n"
			."Interval: " .$this->interval_array[$this->interval]. "\n"
			."Substitute: " . $this->substitute_array[$this->substitute]. "\n"
			."Refills: " . $this->refills. "\n"
			."Per Refill: " . $this->per_refill;

		if ($html) {
			return nl2br($string);
		}
		else {
			return $string;
		}
	}

	function get_unit_display() {
		return $this->unit_array[$this->unit];
	}
	function get_unit() {
		return $this->unit;
	}
	function set_unit($unit) {
		if (is_numeric($unit)) {
			$this->unit = $unit;
		}
	}
	function set_id($id) {
		if (!empty($id) && is_numeric($id)) {
			$this->id = $id;
		}
	}
	function get_id() {
		return $this->id;
	}
	function get_dosage_display() {
		if (empty($this->form) && empty($this->interval)) {
		  return ($this->dosage);
		}
		else {
		  return ($this->dosage . " in " . $this->form_array[$this->form] . " " . $this->interval_array[$this->interval]);
		}
	}
	function set_dosage($dosage) {
			$this->dosage = $dosage;
	}
	function get_dosage() {
		return $this->dosage;
	}
	function set_form($form) {
		if (is_numeric($form)) {
			$this->form = $form;
		}
	}
	function get_form() {
		return $this->form;
	}

	function set_refills($refills) {
		if (is_numeric($refills)) {
			$this->refills = $refills;
		}
	}
	function get_refills() {
		return $this->refills;
	}

	function set_size($size) {
		if (is_numeric($size)) {
			$this->size = $size;
		}
	}
	function get_size() {
		return $this->size;
	}
	function set_quantity($qty) {
		if (is_numeric($qty)) {
			$this->quantity = $qty;
		}
	}
	function get_quantity() {
		return $this->quantity;
	}
	
	function set_route($route) {
		if (is_numeric($route)) {
			$this->route = $route;
		}
	}
	function get_route() {
		return $this->route;
	}
	
	function set_interval($interval) {
		if (is_numeric($interval)) {
			$this->interval = $interval;
		}
	}
	function get_interval() {
		return $this->interval;
	}
	function set_substitute($sub) {
		if (is_numeric($sub)) {
			$this->substitute = $sub;
		}
	}
	function get_substitute() {
		return $this->substitute;
	}
	function set_per_refill($pr) {
		if (is_numeric($pr)) {
			$this->per_refill = $pr;
		}
	}
	function get_per_refill() {
		return $this->per_refill;
	}
	function set_patient_id($id) {
		if (is_numeric($id)) {
			$this->patient = new Patient($id);
		}
	}
	function get_patient_id() {
		return $this->patient->id;
	}
	function set_provider_id($id) {
		if (is_numeric($id)) {
			$this->provider = new Provider($id);
		}
	}
	function get_provider_id() {
		return $this->provider->id;
	}
	function set_provider($pobj) {
		if (get_class($pobj) == "provider") {
			$this->provider = $pobj;
		}
	}

	function set_pharmacy_id($id) {
		if (is_numeric($id)) {
			$this->pharmacy = new Pharmacy($id);
		}
	}
	function get_pharmacy_id() {
		return $this->pharmacy->id;
	}
	function set_pharmacist_id($id) {
		if (is_numeric($id)) {
			$this->pharmacist = new Person($id);
		}
	}
	function get_pharmacist() {
		return $this->pharmacist->id;
	}
	function get_start_date_y() {
		$ymd = split("-",$this->start_date);
		return $ymd[0];
	}
	function set_start_date_y($year) {
		if (is_numeric($year)) {
			$ymd = split("-",$this->start_date);
			$ymd[0] = $year;
			$this->start_date = $ymd[0] ."-" . $ymd[1] ."-" . $ymd[2];
		}
	}
	function get_start_date_m() {
		$ymd = split("-",$this->start_date);
		return $ymd[1];
	}
	function set_start_date_m($month) {
		if (is_numeric($month)) {
			$ymd = split("-",$this->start_date);
			$ymd[1] = $month;
			$this->start_date = $ymd[0] ."-" . $ymd[1] ."-" . $ymd[2];
		}
	}
	function get_start_date_d() {
		$ymd = split("-",$this->start_date);
		return $ymd[2];
	}
	function set_start_date_d($day) {
		if (is_numeric($day)) {
			$ymd = split("-",$this->start_date);
			$ymd[2] = $day;
			$this->start_date = $ymd[0] ."-" . $ymd[1] ."-" . $ymd[2];
		}
	}
	function get_start_date() {
		return $this->start_date;
	}
	function set_start_date($date) {
		return $this->start_date = $date;
	}
	function get_date_added() {
		return $this->date_added;
	}
	function set_date_added($date) {
		return $this->date_added = $date;
	}
	function get_date_modified() {
		return $this->date_modified;
	}
	function set_date_modified($date) {
		return $this->date_modified = $date;
	}
	function get_filled_date() {
		return $this->filled_date;
	}
	function set_filled_date($date) {
		return $this->filled_date = $date;
	}
	function set_note($note) {
		$this->note = $note;
	}
	function get_note() {
		return $this->note;
	}
	function set_drug($drug) {
		$this->drug = $drug;
	}
	function get_drug() {
		return $this->drug;
	}
	function get_filled_by_id() {
		return $this->pharmacist->id;
	}
	function set_filled_by_id($id) {
		if (is_numeric($id)) {
			return $this->pharmacist->id = $id;
		}
	}

	function get_prescription_display() {
		
		$pconfig = $GLOBALS['oer_config']['prescriptions'];
		
		switch ($pconfig['format']) {
			case "FL":
				return $this->get_prescription_florida_display();
				break;
			default:
				break;
		}
		$sql = "SELECT * FROM users JOIN facility AS f ON f.name = users.facility where users.id ='" . mysql_real_escape_string($this->provider->id) . "'";
		$db = get_db();
		$results = $db->Execute($sql);
		if (!$results->EOF) {

			$string =	 $results->fields['name'] . "\n"
			. $results->fields['street'] . "\n"
			. $results->fields['city'] . ", " . $results->fields['state'] . " " . $results->fields['postal_code'] . "\n"
			. $results->fields['phone'] . "\n\n";
		}
		
		$string .= ""
			."Prescription For:" . "\t" .$this->patient->get_name_display() . "\n"
			."Start Date: " . "\t\t" . $this->start_date. "\n"
			."Provider: " . "\t\t" . $this->provider->get_name_display(). "\n"
			."Provider FDID: " . "\t\t" . $this->provider->federal_drug_id. "\n"
			."Drug: " . "\t\t\t" . $this->drug. "\n"
			."Dosage: " . "\t\t" . $this->dosage . " in ". $this->form_array[$this->form]. " form " . $this->interval_array[$this->interval]. "\n"
			."Qty: " . "\t\t\t" . $this->quantity. "\n"
			."Medication Unit: " . "\t" . $this->size  . " ". $this->unit_array[$this->unit] . "\n"
			."Substitute: " . "\t\t" . $this->substitute_array[$this->substitute]. "\n";
			if ($this->refills > 0) {
				$string .= "Refills: " . "\t\t" . $this->refills. ", of quantity: " . $this->per_refill ."\n";
			}
			$string .= "\n"."Notes: \n" . $this->note . "\n";
			return $string;
	}
	
	function get_prescription_florida_display() {
		
		$db = get_db();
		$ntt = new NumberToText($this->quantity);
		$ntt2 = new NumberToText($this->per_refill);
		$ntt3 = new NumberToText($this->refills);
		
		$string = "";
		
		$gnd = $this->provider->get_name_display();
		
		while(strlen($gnd)<31) {
				$gnd .= " ";
		}
		
		$string .= $gnd . $this->provider->federal_drug_id . "\n"; 
		
		$sql = "SELECT * FROM users JOIN facility AS f ON f.name = users.facility where users.id ='" . mysql_real_escape_string($this->provider->id) . "'";
		$results = $db->Execute($sql);
		
		if (!$results->EOF) {
			$rfn = $results->fields['name'];
			
			while(strlen($rfn)<31) {
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
		$string .= strtoupper($this->drug) . " " . $this->size  . " ". $this->unit_array[$this->unit] . "\n";
		if (strlen($this->note) > 0) {
			$string .= "Notes: \n" . $this->note . "\n";	
		}
		if (!empty($this->dosage)) {
		  $string .= $this->dosage;
		  if (!empty($this->form)){ 
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
		$string .= "Refills: " . $this->refills . " (" . trim(strtoupper($ntt3->convert())) ."), Per Refill Disp: " . $this->per_refill . " (" . trim(strtoupper($ntt2->convert())) . ")" ."\n";
		$string .= $this->substitute_array[$this->substitute]. "\n";
		$string .= "\n";
		
		return $string;
	}

	function prescriptions_factory($patient_id,$order_by = "active DESC, date_modified DESC, date_added DESC") {
		$prescriptions = array();
		$p = new Prescription();
		$sql = "SELECT id FROM  " . $p->_table . " WHERE patient_id = " .mysql_real_escape_string($patient_id) . " and active = 1 ORDER BY " . mysql_real_escape_string($order_by);
		$results = sqlQ($sql);
		//echo "sql: $sql";
		while ($row = mysql_fetch_array($results) ) {
			$prescriptions[] = new Prescription($row['id']);
		}
		return $prescriptions;
	}

}	// end of Prescription

//class test
/*
$rx = new Prescription(1);
echo "<br /><br />the object is now: " . $rx->toString(true) . "<br />";
$rx->form = FORM_TABLET;
echo "<br /><br />the object is now: " . $rx->toString(true) . "<br />";
$rx->persist();
echo "<br /><br />the object is now: " . $rx->toString(true) . "<br />";

$rx2 = new Prescription(1);
echo "<br /><br />the object is now: " . $rx->toString(true) . "<br />";
*/

?>
