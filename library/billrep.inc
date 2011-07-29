<?php
    require_once("{$GLOBALS['srcdir']}/sql.inc");
    function GenerateTheQueryPart()
     {
        global $query_part,$billstring,$auth;
        //Search Criteria section.
        $billstring='';
        $auth='';
        $query_part='';
        if(isset($_REQUEST['final_this_page_criteria']))
         {
            foreach($_REQUEST['final_this_page_criteria'] as $criteria_key => $criteria_value)
             {
              $criteria_value=PrepareSearchItem(stripslashes($criteria_value));
              $SplitArray=array();
              //---------------------------------------------------------
              if(strpos($criteria_value,"billing.billed = '1'")!== false)
               {
                $billstring .= ' AND '.$criteria_value;
               }
              elseif(strpos($criteria_value,"billing.billed = '0'")!== false)
               {
                //3 is an error condition
                $billstring .= ' AND '."(billing.billed = '0' or (billing.billed = '1' and billing.bill_process = '3'))";
               }
              elseif(strpos($criteria_value,"billing.billed = '7'")!== false)
               {
                $billstring .= ' AND '."billing.bill_process = '7'";
               }
              //---------------------------------------------------------
              elseif(strpos($criteria_value,"billing.id = 'null'")!== false)
               {
                $billstring .= ' AND '."billing.id is null";
               }
              //---------------------------------------------------------
              elseif(strpos($criteria_value,"billing.id = 'not null'")!== false)
               {
                $billstring .= ' AND '."billing.id is not null";
               }
              //---------------------------------------------------------
              elseif(strpos($criteria_value,"patient_data.fname")!== false)
               {
                $SplitArray=split(' like ',$criteria_value);
                $query_part .= " AND ($criteria_value or patient_data.lname like ".$SplitArray[1].")";
               }
              //---------------------------------------------------------
              elseif(strpos($criteria_value,"billing.authorized")!== false)
               {
                $auth = ' AND '.$criteria_value;
               }
              //---------------------------------------------------------
              elseif(strpos($criteria_value,"form_encounter.pid")!== false)
               {//comes like '781,780'
                $SplitArray=split(" = '",$criteria_value);//comes like 781,780'
                $SplitArray[1]=substr($SplitArray[1], 0, -1);//comes like 781,780
                $query_part .= ' AND form_encounter.pid in ('.$SplitArray[1].')';
               }
              //---------------------------------------------------------
              elseif(strpos($criteria_value,"form_encounter.encounter")!== false)
               {//comes like '781,780'
                $SplitArray=split(" = '",$criteria_value);//comes like 781,780'
                $SplitArray[1]=substr($SplitArray[1], 0, -1);//comes like 781,780
                $query_part .= ' AND form_encounter.encounter in ('.$SplitArray[1].')';
               }
              //---------------------------------------------------------
              elseif(strpos($criteria_value,"insurance_data.provider = '1'")!== false)
               {
                $query_part .= ' AND '."insurance_data.provider > '0' and insurance_data.date <= form_encounter.date";
               }
              elseif(strpos($criteria_value,"insurance_data.provider = '0'")!== false)
               {
                $query_part .= ' AND '."(insurance_data.provider = '0' or insurance_data.date > form_encounter.date)";
               }
              //---------------------------------------------------------
              else
               {
                $query_part .= ' AND '.$criteria_value;
               }
              }
         }
     }
    //date must be in nice format (e.g. 2002-07-11)
    function getBillsBetween( $code_type,
        $cols = "id,date,pid,code_type,code,user,authorized,x12_partner_id")
    {
        GenerateTheQueryPart();
        global $query_part,$billstring,$auth;
        // Selecting by the date in the billing table is wrong, because that is
        // just the data entry date; instead we want to go by the encounter date
        // which is the date in the form_encounter table.
        //
        $sql = "SELECT distinct form_encounter.date AS enc_date, form_encounter.pid AS enc_pid, " .
            "form_encounter.encounter AS enc_encounter, form_encounter.provider_id AS enc_provider_id, billing.* " .
            "FROM form_encounter " .
            "LEFT OUTER JOIN billing ON " .
            "billing.encounter = form_encounter.encounter AND " .
            "billing.pid = form_encounter.pid AND " .
            "billing.code_type LIKE '$code_type' AND " .
            "billing.activity = 1 " .
            "LEFT OUTER JOIN patient_data on patient_data.pid = form_encounter.pid " .
            "LEFT OUTER JOIN claims on claims.patient_id = form_encounter.pid and claims.encounter_id = form_encounter.encounter " .
            "LEFT OUTER JOIN insurance_data on insurance_data.pid = form_encounter.pid and insurance_data.type = 'primary' ".
            "WHERE 1=1 $query_part  " . " $auth " ." $billstring " .
            "ORDER BY form_encounter.encounter, form_encounter.pid, billing.code_type, billing.code ASC";
            //echo $sql;
        $res = sqlStatement($sql);

        for($iter=0; $row=sqlFetchArray($res); $iter++)
        {
            $all[$iter] = $row;
        }

        return $all;
    }
    function getBillsListBetween( $code_type,
        $cols = "billing.id, form_encounter.date, billing.pid, billing.code_type, billing.code, billing.user")
    {
        GenerateTheQueryPart();
        global $query_part,$billstring,$auth;
        // See above comment in getBillsBetween().
        //
        $sql = "select distinct $cols " .
            "from form_encounter, billing, patient_data, claims, insurance_data where " .
            "billing.encounter = form_encounter.encounter and " .
            "billing.pid = form_encounter.pid and " .
            "patient_data.pid = form_encounter.pid and " .
            "claims.patient_id = form_encounter.pid and claims.encounter_id = form_encounter.encounter and ".
            "insurance_data.pid = form_encounter.pid and insurance_data.type = 'primary' ".
            $auth  .
            $billstring . $query_part . " and " .
            "billing.code_type like '$code_type' and " .
            "billing.activity = 1 " .
            "order by billing.pid, billing.date ASC";

        $res = sqlStatement($sql);
        $string = "( ";
        for($iter=0; $row=sqlFetchArray($res); $iter++)
        {
            $string .= $row{"id"}.",";
        }
        $string = substr($string,0,strlen($string)-1);
        $string .= ")";
        return $string;
    }

    function billCodesList($list,$skip = "()") {
        if ($list == "()")
            return;

        if ($skip == "()")
            sqlStatement("update billing set billed=1 where id in $list");
        else
            sqlStatement("update billing set billed=1 where id in $list and id not in $skip");

        return;
    }
    
    function ReturnOFXSql()
     {
        GenerateTheQueryPart();
        global $query_part,$billstring,$auth;
        
          $sql = "SELECT distinct billing.*, concat(patient_data.fname, ' ', patient_data.lname) as name from billing "
          . "join patient_data on patient_data.pid = billing.pid "
          . "join form_encounter on "
          . "billing.encounter = form_encounter.encounter AND " 
          . "billing.pid = form_encounter.pid " 
          . "join claims on claims.patient_id = form_encounter.pid and claims.encounter_id = form_encounter.encounter " 
          . "join insurance_data on insurance_data.pid = form_encounter.pid and insurance_data.type = 'primary' "
          . "where billed = '1' "
          . "$auth " 
          . "$billstring  $query_part  " 
          . "order by billing.pid,billing.encounter";
          
         return $sql;
     }

?>
