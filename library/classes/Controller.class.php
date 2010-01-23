<?php

require_once(dirname(__FILE__) . "/../Smarty.class.php");
require_once(dirname(__FILE__) . "/../formdata.inc.php");
define("SMARTY_DIR", dirname(__FILE__) . "/../");

class Controller extends Smarty {

       var $_current_action;
       var $_state;
       var $_args = array();

       function Controller() {
               parent::Smarty();
               $this->template_mod = "general";
               $this->_current_action = "";
               $this->_state = true;
               $this->compile_dir = $GLOBALS['fileroot'] . "/interface/main/calendar/modules/PostCalendar/pntemplates/compiled";
               $this->compile_check = true;
               $this->assign("PROCESS", "true");
               $this->assign("HEADER", "<html><head>
<? html_header_show();?></head><body>");
               $this->assign("FOOTER", "</body></html>");
               $this->assign("CONTROLLER", "controller.php?");
               $this->assign("CONTROLLER_THIS", "controller.php?" . $_SERVER['QUERY_STRING']);
               $this->assign("WEBROOT", $GLOBALS['webroot']);
       }

       function set_current_action($action) {
               $this->_current_action = $action;
       }

       function default_action() {
               echo "<html><body></body></html>";
       }

       function process_action() {
               $this->default_action();
       }

       function populate_object(&$obj) {
               if(!is_object($obj)) {
                       $this->function_argument_error();
               }

               foreach($_POST as $varname => $var) {
                       $varname = preg_replace("/[^A-Za-z0-9_]/","",$varname);
                       $func = "set_" . $varname;
                       if (    (!(strpos("_",$varname) === 0)) && is_callable(array($obj,$func))       ) {
                               //echo "c: $func on w: "  . $var . "<br />";
			       
			       //modified 01-2010 by BGM to centralize to formdata.inc.php
			       // have place several debug statements to allow standardized testing over next several months
                               if (!is_array($var)) {
				       //DEBUG LINE - error_log("Controller populate before strip: ".$var, 0); 
                                       $var = strip_escape_custom($var);
				       //DEBUG LINE - error_log("Controller populate after strip: ".$var, 0);
                               }
			   
                               call_user_func_array(array(&$obj,$func),array($var, $_POST));
                       }
               }

               return true;
       }

       function function_argument_error() {
               $this->display($GLOBALS['template_dir'] . "error/" . $this->template_mod . "_function_argument.html");
               exit;
       }

       function i_once($file) {
               return include_once($file);
       }

       function act($qarray) {

               if (isset($_GET['process'])){
         unset($_GET['process']);
         unset($qarray['process']);
         $_POST['process'] = "true";
       }
               $args = array_reverse(array_keys($qarray));
               $c_name = preg_replace("/[^A-Za-z0-9_]/","",array_pop($args));
               $parts = split("_",$c_name);
               $name = "";

               foreach($parts as $p) {
                       $name .= ucfirst($p);
               }

               $c_name = $name;
               $c_action = preg_replace("/[^A-Za-z0-9_]/","",array_pop($args));
               $args = array_reverse($args);

               // load dutch version for C_Prescription.class
               if ( ($GLOBALS['dutchpc']) && ($c_name == "Prescription") ) $c_name .= 'dutch';

               if(!@call_user_func(array(Controller,"i_once"),$GLOBALS['fileroot'] ."/controllers/C_" . $c_name . ".class.php")) {
                       echo "Unable to load controller $name\n, please check the first argument supplied in the URL and try again";
                       exit;
               }

               $obj_name = "C_" . $c_name;
               $c_obj = new $obj_name();

               if (empty ($c_action)) {
                       $c_action = "default";
               }

               $c_obj->_current_action = $c_action;
               $args_array = array();

               foreach ($args as $arg) {
                       $arg = preg_replace("/[^A-Za-z0-9_]/","",$arg);
                       //this is a workaround because call user func does funny things with passing args if they have no assigned value
                       if (empty($qarray[$arg])) {
                               //if argument is empty pass null as value and arg as assoc array key
                               $args_array[$arg] = null;
                       }
                       else {
                               $args_array[$arg] = $qarray[$arg];
                       }
               }

               $output = "";
               //print_r($args_array);
               if ($_POST['process'] == "true") {

                       if (is_callable(array(&$c_obj,$c_action . "_action_process"))) {
                               //echo "ca: " . $c_action . "_action_process";
                               $output .= call_user_func_array(array(&$c_obj,$c_action . "_action_process"),$args_array);
                               if ($c_obj->_state == false) {
                                       return $output;
                               }
                       }
                       //echo "ca: " . $c_action . "_action";
                       $output .=  call_user_func_array(array(&$c_obj,$c_action . "_action"),$args_array);

               }
               else {
                               if (is_callable(array(&$c_obj,$c_action . "_action"))) {
                                       //echo "ca: " . $c_action . "_action";
                                       $output .=  call_user_func_array(array(&$c_obj,$c_action . "_action"),$args_array);
                               }
                               else {
                                       echo "The action trying to be performed: " . $c_action ." does not exist controller: ". $name;
                               }
               }


               return $output;
       }

       function _link($action = "default",$inlining = false) {
               $url_parts = split("&",$_SERVER['REQUEST_URI']);
               $link = array_shift($url_parts);
               //print_r($url_parts);

               if (strpos($url_parts[0],"=") === false) {
                       $inline_arg = $url_parts[0];
                       $url_parts[0] = $action;
               }
               else {
                       array_unshift($url_parts,$action);
               }
               if ($inlining) {
                       $link .= "&" . $inline_arg;
                       $link .= "&action=" . $url_parts[0];
               }
               else {
                       $link .= "&" . $url_parts[0];
               }

               foreach ($this->_args as $arg_name => $arg) {
                       $link .= "&" . $arg_name . "=" . $arg;
               }
               $link .= "&";
               return  $link;
       }

}

?>
