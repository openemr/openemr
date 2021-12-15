<?php

// TODO: @adunsulag move these into src/
class Controller extends Smarty
{
    public $template_mod;
    public $_current_action;
    public $_state;
    public $_args = array();

    public function __construct()
    {
         parent::__construct();
         $this->template_mod = "general";
         $this->_current_action = "";
         $this->_state = true;
         $this->compile_dir = $GLOBALS['OE_SITE_DIR'] . '/documents/smarty/main';
         $this->compile_check = true;
         $this->plugins_dir = array(__DIR__ . "/../smarty/plugins", $GLOBALS['vendor_dir'] . "/smarty/smarty/libs/plugins");
         $this->assign("PROCESS", "true");
         $this->assign("HEADER", "<html><head></head><body>");
         $this->assign("FOOTER", "</body></html>");
         $this->assign("CONTROLLER", "controller.php?");
         $this->assign("CONTROLLER_THIS", "controller.php?" . ($_SERVER['QUERY_STRING'] ?? ''));
         $this->assign('GLOBALS', $GLOBALS);
    }

    public function set_current_action($action)
    {
         $this->_current_action = $action;
    }

    public function default_action()
    {
         echo "<html><body></body></html>";
    }

    public function process_action()
    {
         $this->default_action();
    }

    public function populate_object(&$obj)
    {
        if (!is_object($obj)) {
            $this->function_argument_error();
        }

        foreach ($_POST as $varname => $var) {
            $varname = preg_replace("/[^A-Za-z0-9_]/", "", $varname);
            $func = "set_" . $varname;
            if ((!(str_starts_with("_", $varname))) && is_callable(array($obj,$func))) {
                //echo "c: $func on w: "  . $var . "<br />";

                $obj->$func($var, $_POST);
            }
        }

            return true;
    }

    public function function_argument_error()
    {
         $this->display($GLOBALS['template_dir'] . "error/" . $this->template_mod . "_function_argument.html");
         exit;
    }

    public function i_once($file)
    {
         return include_once($file);
    }

    public function act($qarray)
    {

        if (isset($_GET['process'])) {
            unset($_GET['process']);
            unset($qarray['process']);
            $_POST['process'] = "true";
        }

        $args = array_reverse(array_keys($qarray));
        $c_name = preg_replace("/[^A-Za-z0-9_]/", "", array_pop($args));
        $parts = explode("_", $c_name);
        $name = "";

        foreach ($parts as $p) {
            $name .= ucfirst($p);
        }

            $c_name = $name;
            $c_action = preg_replace("/[^A-Za-z0-9_]/", "", array_pop($args));
            $args = array_reverse($args);

        if (!$this->i_once($GLOBALS['fileroot'] . "/controllers/C_" . $c_name . ".class.php")) {
            echo "Unable to load controller $name\n, please check the first argument supplied in the URL and try again";
            exit;
        }

            $obj_name = "C_" . $c_name;
            $c_obj = new $obj_name();

        if (empty($c_action)) {
            $c_action = "default";
        }

            $c_obj->_current_action = $c_action;
            $args_array = array();

        foreach ($args as $arg) {
            $arg = preg_replace("/[^A-Za-z0-9_]/", "", $arg);
            //this is a workaround because call user func does funny things with passing args if they have no assigned value
            //2013-02-10 EMR Direct: workaround modified since "0" is also considered empty;
            if (empty($qarray[$arg]) && $qarray[$arg] != "0") {
                //if argument is empty pass null as value
                $args_array[] = null;
            } else {
                $args_array[] = $qarray[$arg];
            }
        }

            $output = "";
            //print_r($args_array);
        if (isset($_POST['process']) && ($_POST['process'] == "true")) {
            if (is_callable(array(&$c_obj,$c_action . "_action_process"))) {
                //echo "ca: " . $c_action . "_action_process";
                $output .= call_user_func_array(array(&$c_obj,$c_action . "_action_process"), $args_array);
                if ($c_obj->_state == false) {
                    return $output;
                }
            }

            //echo "ca: " . $c_action . "_action";
            $output .=  call_user_func_array(array(&$c_obj,$c_action . "_action"), $args_array);
        } elseif (is_callable(array(&$c_obj,$c_action . "_action"))) {
            //echo "ca: " . $c_action . "_action";
            $output .=  call_user_func_array(array(&$c_obj,$c_action . "_action"), $args_array);
        } else {
            echo "The action trying to be performed: " . $c_action . " does not exist controller: " . $name;
        }


            return $output;
    }

    public function _link($action = "default", $inlining = false)
    {
         $url_parts = explode("&", $_SERVER['REQUEST_URI']);
         $link = array_shift($url_parts);
         //print_r($url_parts);

        if (strpos($url_parts[0], "=") === false) {
            $inline_arg = $url_parts[0];
            $url_parts[0] = $action;
        } else {
            array_unshift($url_parts, $action);
        }

        if ($inlining) {
            $link .= "&" . urlencode($inline_arg);
            $link .= "&action=" . urlencode($url_parts[0]);
        } else {
            $link .= "&" . urlencode($url_parts[0]);
        }

        foreach ($this->_args as $arg_name => $arg) {
            $link .= "&" . urlencode($arg_name) . "=" . urlencode($arg);
        }

            $link .= "&";
            return  $link;
    }
}
