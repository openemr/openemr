<?php

class LBF_Validation{

    /*If another library is used the key names can be modified here*/
    const  KEY_REQUIRED="presence";
    /*
 * Function to generate the constraints used in validation.js library
 * Using the data save in layout options validation
 */
    public static function generate_validate_constraints($form_id){

        $fres = sqlStatement("SELECT * FROM layout_options " .
            "WHERE form_id = ? AND uor > 0 AND field_id != '' " .
            "ORDER BY group_name, seq", array($form_id) );
        $constraints=[];
        while ($frow = sqlFetchArray($fres)) {
            if($frow['uor'] == 2 ){
                $id = 'form_'.$frow['field_id'];
                $constraints[$id] = [self::KEY_REQUIRED=>true];
            }
        }
        return json_encode($constraints);

        }
}