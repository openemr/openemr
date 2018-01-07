<?php
/*
* Morphine Calculator
* @package OpenEMR
* @link http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/



      /*
      *  This section is to save the ME total 
      *
      *
      */
    function saveMEinfo($pid, $enc, $sum)
    {
        if(empty($enc)){
            return "<font color='red'>".xl('Please select an encounter to save this data')."</font>";
        } else {
        sqlStatement("INSERT INTO `morphine_eq` (`id`, `datetime`, `encounter`, `last`, `next`, `notes`, `pid`)". 
                      "VALUES ('', NOW(), $enc, $sum, '', '', $pid)");
                      
                      return xlt("Saved");
        }
    }
	/*
	*
	*  Retrieve last ME recorded
	*
	*/
	
    function fetchMEs($pid)
    {
         $sql = "SELECT * FROM morphine_eq WHERE pid = ? ORDER BY id DESC LIMIT 1";
         $query = sqlStatement($sql,array($pid));
         $res = sqlFetchArray($query);
         return $res;
    }

      /*
      *  This section is to grab meds from the prescription table.
      *
      *
      */

                        
    function getMeds(){
        
           $sql = "SELECT `drug`,`quantity`,`size`, `date_added` FROM `prescriptions` ".
                 " WHERE `patient_id`= ?  AND active = ?";
            $arr = array($GLOBALS['pid'], 1);
            $drugs = sqlStatement($sql,$arr);
        
        return $drugs;
    }

    /**
    * This section to retrieve the morphine list 
    *
    */
     function getList()
     {

      $sql = "SELECT * FROM morphine_list ORDER BY drugname";
      $list = sqlStatement($sql); 
        $wholeList = array();

        while($row = sqlFetchArray($list)){
          $wholeList[] = $row; 
        }
      return $wholeList;

     }
     /*
     *
     *   This section is to calculate the Morphine Equivalent of the morphine drugs found in the list. 
     *
     */
    
	function getMeCalculations($med, $size, $quantity ){
		
      $matchDrug = getList();
		
        foreach($matchDrug as $key => $value){
        
          if(preg_match("/^".$value['drugname']."/", $med)){
            $stat = $value['drugname'] . " = ";
            $num = ($size * $value['multiplier'] * $quantity)/$value['days'];
							   break;                              
          }
			}

        return array($stat, $num);  
	 } 

    /*
     *    Fetch Graph data
     *
     */	
	 
	 function getGraphData($pid){
		 $data = sqlStatement("SELECT datetime, last FROM morphine_eq WHERE pid = ".$pid . " ORDER BY last DESC");
		 return $data;
	 } 