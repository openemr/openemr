<?php

class LBF_Validation{

    /*If another library is used the key names can be modified here*/
    const VJS_KEY_REQUIRED = 'presence';
    /*
 * Function to generate the constraints used in validation.js library
 * Using the data save in layout options validation
 */
    public static function generate_validate_constraints($form_id){


        $fres = sqlStatement(
             "SELECT layout_options.*,list_options.notes as validation_json 
              FROM layout_options  
              LEFT JOIN list_options ON layout_options.validation=list_options.option_id AND list_options.list_id = 'LBF_Validations'
              WHERE layout_options.form_id = ? AND layout_options.uor > 0 AND layout_options.field_id != '' 
              ORDER BY layout_options.group_name, layout_options.seq ", array($form_id) );
        $constraints=array();
        $validation_arr=array();
        $required=array();
        while ($frow = sqlFetchArray($fres)) {
            $id = 'form_'.$frow['field_id'];
            $validation_arr=array();
            $required=array();
            //Keep "required" option from the LBF form
            if($frow['uor'] == 2 ){
                $required = array(self::VJS_KEY_REQUIRED=>true);
            }
            if ($frow['validation_json']){
                if(json_decode($frow['validation_json'])) {
                    $validation_arr=json_decode($frow['validation_json'],true);

                }else{
                    trigger_error($frow['validation_json']. " is not a valid json ", E_USER_WARNING);
                }
            }
            if(!empty($required) || !empty($validation_arr)) {
                $constraints[$id] = array_merge($required, $validation_arr);
            }

        }

        return json_encode($constraints);

    }

}