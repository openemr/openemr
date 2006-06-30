<?
include_once("../../globals.php");
include_once("../../../library/billing.inc");	

function content_parser($input) {

   // parse content field
   $content = $input;

//   comments should really be removed in save.php
//   $content = remove_comments($content);

   //reduce more than two empty lines to no more than two.
   $content = preg_replace("/([^\n]\r[^\n]){2,}/","\r\r",$content);
   $content = preg_replace("/([^\r]\n[^\r]){2,}/","\n\n",$content);
   $content = preg_replace("/(\r\n){2,}/","\r\n\r\n",$content);


   return $content;
}

// implement C style comments ie remove anything between /* and */
function remove_comments($string_to_process) {
   return preg_replace("/\/\*.*?\*\//","",$string_to_process);
}
// This function is useless for now, don't use it
function remove_dangling_comments($string_to_process) {
   return preg_replace("/(\/\*)|(\*\/)/","",$string_to_process);
}

//process commands embedded in C style comments where function name is first
//followed by args separated by :: delimiter and nothing else
//must run this before remove_comments or there will be nothing left to process :)

function process_commands($string_to_process) {

  $command_array = array();  //to be filled with potential commands
  $matches= array();  //to be filled with potential commands
  if (!preg_match_all("/\/\*.*?\*\//",$string_to_process, $matches)) {return 0;}
  $command_array = $matches[0];
  foreach($command_array as $val) {
    //process each command 
    $comm = preg_replace("/(\/\*)|(\*\/)/","",$val);
    $comm_array = split('::', $comm); //array where first element is command and rest are args
    //Here is where we process particular commands
    if (trim($comm_array[0]) == 'billing') {
      //insert data into the billing table, see, easy!
      $type = trim($comm_array[1]);  
      $code = trim($comm_array[2]);  
      $text = trim($comm_array[3]);  
      $modifier = trim($comm_array[4]);  
      $units = trim($comm_array[5]);  
      $fee = trim($comm_array[6]);  
      $encounter = $_SESSION["encounter"];
      $pid = $_SESSION["pid"];
      addBilling($encounter, $type, $code, $text, $pid, $userauthorized,$_SESSION['authUserID'],$modifier,$units,sprintf("%01.2f", $fee));
    }
  }
} 
