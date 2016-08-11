<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

      $fake_register_globals=false;
      $sanitize_all_escapes=true;
      
      require_once("../../globals.php");
      require_once("../../../custom/code_types.inc.php");
      require_once("$srcdir/sql.inc");
      require_once("$srcdir/options.inc.php");
      require_once("$srcdir/formatting.inc.php");
      require_once("$srcdir/formdata.inc.php");
      
       
      $selectedItemType = intval($_POST['itemTypes']);   
      $searchTerm = trim($_POST['sTerm']);
      if(isset($_POST['diagRepOnly']) &&isset($_POST['serRepOnly'])){
        $reportingOnly = '(c.reportable = 1 OR c.financial_reporting = 1) AND ';
        $financial_reportingOnly = '';
      }else{
        $reportingOnly = isset($_POST['diagRepOnly']) ? 'c.reportable = 1 AND ' : '';
        $financial_reportingOnly = isset($_POST['serRepOnly']) ? 'c.financial_reporting = 1 AND ' : '';
      }
      
      $stWhere = "";
      $args = array();
      if($searchTerm != ''){
        $stWhere .= 'AND (';
        $stWhere .= 'c.code = ? ';  
        $args[] = $searchTerm;    
        $stWhere .= 'OR c.code like ? '; 
        $args[] = $searchTerm.'%';  
        $stWhere .= 'OR c.code like ? ';  
        $args[] = '%'.$searchTerm.'%';   
        $stWhere .= 'OR c.code_text = ? ';
        $args[] = $searchTerm;      
        $stWhere .= 'OR c.code_text like ?';  
        $args[] = $searchTerm.'%'; 
        $stWhere .= 'OR c.code_text like ?';  
        $args[] = '%'.$searchTerm.'%';    
        $stWhere .= 'OR c.modifier = ? ';   
        $args[] = $searchTerm;   
        $stWhere .= 'OR c.modifier like ? '; 
        $args[] = $searchTerm.'%';  
        $stWhere .= 'OR c.modifier like ? ';   
        $args[] = '%'.$searchTerm.'%';   
        
        $stWhere .= ')';
      }
      $itemsToDisplay = array();
    
      if($selectedItemType == 0){ // 
          $itemSQL = "SELECT c.*, ct.ct_label, ct.ct_id FROM codes c JOIN code_types ct ON c.code_type = ct.ct_id WHERE $reportingOnly $financial_reportingOnly ct.ct_fee != 0 $stWhere ORDER BY ct.ct_seq asc, ";
      }else{
          $itemSQL = "SELECT c.*, ct.ct_label, ct.ct_id FROM codes c JOIN code_types ct ON c.code_type = ct.ct_id WHERE $reportingOnly $financial_reportingOnly ct.ct_fee != 0 AND c.code_type = $selectedItemType $stWhere ORDER BY ";
          //echo $itemSQL;
      }
      if($searchTerm != ''){
          $itemSQL .= " (c.code_text = ?)  desc, (c.modifier = ?)  desc, (c.code = ?)  desc, ";
          $args[] = $searchTerm;
          $args[] = $searchTerm;
          $args[] = $searchTerm;      
      }
          
      $itemSQL .= "code_text limit 0,500";
      
      $itemRes = sqlStatement($itemSQL,$args);
      while($row = sqlFetchArray($itemRes)){
        $itemsToDisplay[] = $row;
      }
      
      echo json_encode($itemsToDisplay);