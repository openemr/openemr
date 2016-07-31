<?php
/**
 * interface/main/holidays/Holidays_Controller.php implementation of holidays logic.
 *
 * This class contains the implementation of all the logic
 * included in the holidays calendar story.
 *
 * Copyright (C) 2016 Sharon Cohen <sharonco@matrix.co.il>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  sharonco <sharonco@matrix.co.il>
 * @link    http://www.open-emr.org
 */

require_once("Holidays_Storage.php");
class Holidays_Controller{

    const UPLOAD_DIR = "documents/holidays_storage";
    const FILE_NAME = "holidays_to_import.csv";

    
    public $storage;
    public $target_file;

    function __construct(){
        $this->set_target_file();
        $this->storage = new Holidays_Storage();
    }

    public function set_target_file(){
            $this->target_file = $GLOBALS['OE_SITE_DIR']."/". self::UPLOAD_DIR."/".self::FILE_NAME;
    }
    public function get_target_file(){
        return $this->target_file;
    }

    /**
     * This function uploads the csv file
     * @param $files
     * @return bool
     */
    public function upload_csv($files){
        if (!file_exists($GLOBALS['OE_SITE_DIR']."/". self::UPLOAD_DIR)) {
            if (!mkdir($GLOBALS['OE_SITE_DIR']."/". self::UPLOAD_DIR."/",0700)) {
                return false;
            }
        }
        $file_type = pathinfo($this->target_file,PATHINFO_EXTENSION);
        if($file_type != "csv"){
            return false;
        }
        if (move_uploaded_file($files["form_file"]["tmp_name"], $this->target_file)) {
            return true;
        }
        return false;
    }

    /**
     * Trys to reach the file (csv) and sends the rows to the storage to import the holidays to the calendar external table
     * @return bool
     */
    public function import_holidays_from_csv(){
        $file=$this->get_file_csv_data();
        if(empty($file)){
            return false;
        }

        $this->storage->import_holidays($this->target_file);
        return true;

    }

    /**
     * Checks if the file exists and returns the last modification date or empty array if the file doesn't exists
     * @return array
     */
    public function get_file_csv_data(){
        $file=array();
        if (file_exists($this->target_file)){
            $file['date']= date ("d/m/Y H:i:s", filemtime($this->target_file));
        }
        return $file;
    }

    /**
     * Gets all the holidays and send the result to create the events for the calendar
     */
    public function create_holiday_event(){
        $holidays = $this->storage->get_holidays();
        $events = $this->storage->create_events($holidays);
        return true;
        
    }

    /**
     * Returns an array of the holiday that are in the calendar_external table
     * @param $start_date
     * @param $end_date
     * @return array
     */
    public function get_holidays_by_date_range($start_date,$end_date){
        $holidays = array();
        $holidays = Holidays_Storage::get_holidays_by_dates($start_date,$end_date);
        return $holidays;
    }

    /**
     * Return true if the date is a holiday/closed
     * @param $date
     */
    public static function is_holiday($date){
        $holidays = array();
        $holidays = Holidays_Storage::get_holidays_by_dates($date,$date);
        if(in_array($date,$holidays)){
            return true;
        }
        return false;
    }
    

}