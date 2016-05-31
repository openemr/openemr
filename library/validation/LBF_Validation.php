<?php

class LBF_Validation{

    /*If another library is used the key names can be modified here*/
    const VJS_KEY_REQUIRED = "presence";
    const VJS_TYPE_NUMERICALITY =  "numericality";
    const VJS_NUM_ONLYINT = "onlyInteger";
    const VJS_NUM_MAX = "lessThanOrEqualTo";
    const VJS_NUM_MIN = "greaterThanOrEqualTo";
    const VJS_FORMAT = "format";
    const VJS_PATTERN = "pattern";
    const VJS_TYPE_EMAIL = "email";
    const VJS_TYPE_URL = "url";

    const KEY_TYPE = "type";
    const TYPE_INT = "int";
    const TYPE_FLOAT = "float";
    const TYPE_EMAIL = "email";
    const TYPE_URL = "url";
    const TYPE_NAME = "name";

    const NUM_MIN='min';
    const NUM_MAX='max';
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
            $id = 'form_'.$frow['field_id'];
            //Keep "required" option from the LBF form
            if($frow['uor'] == 2 ){
                $constraints[$id] = [self::VJS_KEY_REQUIRED=>true];
            }
            if ($frow['validation']){
                $constraints[$id]=self::generate_rules($frow['validation']);
            }
        }
        return json_encode($constraints);

    }
    /*
    * Take the json string save  in LBF form translate each field into a validate.js acceptable rule
    *
    */
    private static function generate_rules($validation_str){
        $validations = json_decode($validation_str,true);
        foreach($validations as $key=>$validation){
            switch($key){
                case self::KEY_TYPE:
                    $rules=self::generate_by_type($key,$validations);
                    break;
            }
        }
        return $rules;
    }

    private static function generate_by_type($key,$validations){
        switch($validations[$key]){
            case self::TYPE_FLOAT:
            case self::TYPE_INT:
               $rules=self::numericality($validations);
            break;
            /*Name is a predefined regular expression that accespt letters spaces ' and -*/
            case self::TYPE_NAME:
                $rules[self::VJS_FORMAT][self::VJS_PATTERN] = "[a-zA-z]+([ '-\\s][a-zA-Z]+)*" ;
            break;
            case self::TYPE_EMAIL:
                $rules[self::VJS_TYPE_EMAIL] = true;
            break;
            case self::TYPE_URL:
                $rules[self::VJS_TYPE_URL] = true;
            break;

        }
        return $rules;
    }
    /**
    * Create the numerical rules
    * Examples:
    * var constraints = {
       duration: {
         numericality: {
         onlyInteger: true,
         greaterThan: 0,
         lessThanOrEqualTo: 30,
         even: true,
         notEven: "must be evenly divisible by two"
       }
    }
    * float numbers:Using regular expressions
       "form_drivers_license":{
          "numericality":{
            "format":{
              "pattern":"[-+]?(\\d*[.])?\\d+"
            }
          }
       }
     *@see https://validatejs.org/#validators-numericality
     */
     private static function numericality ($validations){
         $rules = [];
         $rules[self::VJS_TYPE_NUMERICALITY] = [];
         //If it integer then only int is true
         if ($validations[self::KEY_TYPE] == self::TYPE_INT) {
             $rules[self::VJS_TYPE_NUMERICALITY][self::VJS_NUM_ONLYINT] = true;
         }
         if ($validations[self::KEY_TYPE] == self::TYPE_FLOAT) {
             $rules[self::VJS_TYPE_NUMERICALITY][self::VJS_FORMAT][self::VJS_PATTERN] = "[-+]?(\\d*[.])?\\d+" ;
         }
         if (isset($validations[self::NUM_MIN])) {
             $rules[self::VJS_TYPE_NUMERICALITY][self::VJS_NUM_MIN] = $validations[self::NUM_MIN];
         }
         if (isset($validations[self::NUM_MAX])) {
             $rules[self::VJS_TYPE_NUMERICALITY][self::VJS_NUM_MAX] = $validations[self::NUM_MAX];
         }

         return $rules;
     }
}