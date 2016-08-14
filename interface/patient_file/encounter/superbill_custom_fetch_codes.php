<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

      $fake_register_globals=false;
      $sanitize_all_escapes=true;
      require_once("../../globals.php");
      require_once("../../../custom/code_types.inc.php");
      $start = intval($_GET['start']);
      $limit = intval($_GET['limit']);
      if(!isset($_POST['itemTypes'])){
        die('999');
      }else{
          $itemsToDisplay['count'] = 0;
          $itemsToDisplay['items'] = array();
          $filter_elements = array();
          if (isset($_POST['diagRepOnly'])) $filter_elements['reportable'] = 1;
          if (isset($_POST['serRepOnly'])) $filter_elements['financial_reporting'] = 1;
          $filter = array();
          $filter_key = array();
          foreach ($_POST['itemTypes'] as $var) {
            $var = $var+0;
            if($var == 0) continue;
            array_push($filter,$var);
            $var_key = convert_type_id_to_key($var);
            array_push($filter_key,$var_key);
          }
          $count = main_code_set_search($filter_key,trim($_POST['sTerm']),NULL,NULL,false,NULL,true,$start,$limit,$filter_elements);
          $itemsToDisplay['count'] = $count;
          $itemsToDisplayRes = main_code_set_search($filter_key,trim($_POST['sTerm']),NULL,NULL,false,NULL,false,$start,$limit,$filter_elements);
          while($row = sqlFetchArray($itemsToDisplayRes)){
            $itemsToDisplay['items'][] = $row;
          }
      }
      
      echo json_encode($itemsToDisplay);