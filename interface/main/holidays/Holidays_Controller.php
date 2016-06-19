<?php
require_once("Holidays_Storage.php");
/**
 * Created by PhpStorm.
 * User: sharonco
 * Date: 6/15/16
 * This class contains the implementation of all the logic included in the holidays calendar story
 */
class Holidays_Controller{

    const UPLOAD_DIR = "uploads";
    const FILE_NAME = "holidays_to_import.csv";
    const TARGET_FILE = self::UPLOAD_DIR."/".self::FILE_NAME;
    
    public $storage;


    public function upload_csv($files){

        $file_type = pathinfo(self::TARGET_FILE,PATHINFO_EXTENSION);
      
        if($file_type != "csv"){
            return false;
        }
        if (move_uploaded_file($files["form_file"]["tmp_name"], self::TARGET_FILE)) {
            return true;
        }

        return false;

    }

    public function get_file_csv_data(){
        $file=array();
        if (file_exists(self::TARGET_FILE)){
            $file['date']= date ("d/m/Y H:i:s", filemtime(self::TARGET_FILE));
        }
        return $file;
    }

    /**
     *
     */
    public function create_holiday_event(){
        $this->storage = new Holidays_Storage();
        $holidays = $this->storage->get_holidays();
        $events = $this->storage->create_events($holidays);
        
        
    }

    public function get_holidays_by_date_range($start_date,$end_date){
        $holidaye = array();
        $this->storage = new Holidays_Storage();
        $holidays = $this->storage->get_holidays_by_dates($start_date,$end_date);
        return $holidays;
    }
    

}