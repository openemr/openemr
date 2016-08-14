<?php

$fake_register_globals=false;
$sanitize_all_escapes=true;

require_once("../../globals.php");
require_once("../../../custom/code_types.inc.php");
require_once("$srcdir/sql.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/formdata.inc.php");

// print_r($_GET);
// 
// die;
$action = trim($_GET['action']); 

// echo $action;
// die;

if($action == 'UpdateItem'){
  updateItem();
}
elseif($action == 'UpdatePrices'){
  updatePrices();
}  
elseif($action == 'AddItem'){
  addItem();
}

function updateItem(){ 
  $code_id = intval(trim(preg_replace('~[^0-9,.]~','',$_GET['code_id'])));
  
  if($code_id == 0){
    addItem();
  }else{
    $code = trim($_GET['code']);                
    $code_text = trim($_GET['code_text']);
  //   echo $code_text;
    $units = 0;// no longer used trim($_GET['units']);
    $modifier = trim($_GET['modifier']);           
    $code_type = trim($_GET['code_type']);      
    $fee = intval(trim(preg_replace('~[^0-9,.]~','',$_GET['fee'])));
    $active = isset($_GET['active']) ? 1 : 0;
    $reportable = isset($_GET['reportable']) ? 1 : 0;
    $financial_reporting = isset($_GET['financial_reporting']) ? 1 : 0;
        
    // validate required data
    if($code_id == 0 || $code == '') die("please check the data");
    ini_set("display_errors","1");
    $res = sqlStatement("UPDATE codes SET
                                          code_type=?,
                                          code=?,
                                          code_text=?,
                                          code_text_short=?,
                                          units=?,
                                          modifier=?,
                                          fee=?,
                                          active=?,
                                          reportable=?,
                                          financial_reporting=?
                              WHERE id=?;",array($code_type,$code,$code_text,$code_text,$units,$modifier,$fee,$active,$reportable,$financial_reporting,$code_id));
    echo '1';
  }
}

function updatePrices(){ 
    unset($_GET['action']);
    $pr_id = $_GET['pr_id']; 
    unset($_GET['pr_id']);
    foreach($_GET as $col=>$val){
//       $res = sqlStatement('INSERT INTO prices (UPDATE prices SET pr_price = ? WHERE pr_level = ? AND pr_id = ?',array($val,$col,$pr_id));
      
      $res = sqlStatement('INSERT IGNORE INTO `prices` (`pr_id`,`pr_selector`,`pr_level`,`pr_price`) VALUES (?, "", ?, ?) ON DUPLICATE KEY UPDATE `pr_price` = ?',array($pr_id,$col,$val,$val));
//       print_r($res);
//       echo '<hr />'; 
    }
    echo '1';
    
}

function addItem(){
  $code = trim($_GET['code']);         
  $code_type = trim($_GET['code_type']);                 
  $code_text = trim($_GET['code_text']);
   
  $units = intval(trim(preg_replace('~[^0-9,.]~','',$_GET['units'])));
  $modifier = trim($_GET['modifier']);      
  $fee = intval(trim(preg_replace('~[^0-9,.]~','',$_GET['fee'])));
  $active = isset($_GET['active']) ? 1 : 0;
  $reportable = isset($_GET['reportable']) ? 1 : 0;
  $financial_reportinge = isset($_GET['financial_reporting']) ? 1 : 0;
  
  /// check if this code exists
  $codeRes = sqlStatement("SELECT count(id) as c FROM codes WHERE code = ? AND modifier = ?",array($code,$modifier));
  $codeRow = sqlFetchArray($codeRes);
  if($codeRow['c'] > 0){
    die('This code already exists');
  } 
  
  // validate required data
  if($code == '') die("please check the data");
  
  $res = sqlInsert("INSERT INTO codes (code_type,code,code_text,code_text_short,units,modifier,fee,active,reportable,financial_reporting)
                            VALUES(?,?,?,?,?,?,?,?,?,?);",
                    array($code_type,$code,$code_text,$code_text,$units,$modifier,$fee,$active,$reportable,$financial_reporting));
  echo '1';
}


?>