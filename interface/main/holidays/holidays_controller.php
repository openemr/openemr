<?php

/**
 * Created by PhpStorm.
 * User: sharonco
 * Date: 6/15/16
 * This class contains the implementation of all the logic included in the holidays calendar story
 */
class Holidays_Controller{

    public function upload_csv($files){
        $target_dir = "uploads/";
        $target_file = $target_dir ."holidays_to_import.csv";
        $upload_ok = 1;
        $file_type = pathinfo($target_file,PATHINFO_EXTENSION);
      
        if($file_type != "csv"){
            return false;
        }
        if (move_uploaded_file($files["form_file"]["tmp_name"], $target_file)) {
            return true;
        }

        return false;

    }
    

}