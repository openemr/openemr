<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     counter
 * Purpose:  print out a counter value
 * -------------------------------------------------------------
 */
function smarty_function_counter($params, &$smarty)
{
    static $count = array();
    static $skipval = array();
    static $dir = array();
    static $name = "default";
    static $printval = array();
    static $assign = "";

    extract($params);

    if (!isset($name)) {
		if(isset($id)) {
			$name = $id;
		} else {		
        	$name = "default";
		}
	}

    if (isset($start))
        $count[$name] = $start;
    else if (!isset($count[$name]))
        $count[$name]=1;

    if (!isset($print))
        $printval[$name]=true;
    else
        $printval[$name]=$print;
    
    if (!empty($assign)) {
        $printval[$name] = false;
        $smarty->assign($assign, $count[$name]);
    }

    if ($printval[$name])
        echo $count[$name];

    if (isset($skip))
        $skipval[$name] = $skip;
    else if (empty($skipval[$name]))
        $skipval[$name] = 1;
    
    if (isset($direction))
        $dir[$name] = $direction;
    else if (!isset($dir[$name]))
        $dir[$name] = "up";

    if ($dir[$name] == "down")
        $count[$name] -= $skipval[$name];
    else
        $count[$name] += $skipval[$name];
}

/* vim: set expandtab: */

?>
