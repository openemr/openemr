<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     cycle
 * Version:  1.3
 * Date:     May 3, 2002
 * Author:	 Monte Ohrt <monte@ispi.net>
 * Credits:  Mark Priatel <mpriatel@rogers.com>
 *           Gerard <gerard@interfold.com>
 *           Jason Sweat <jsweat_php@yahoo.com>
 * Purpose:  cycle through given values
 * Input:    name = name of cycle (optional)
 *           values = comma separated list of values to cycle,
 *                    or an array of values to cycle
 *                    (this can be left out for subsequent calls)
 *
 *           reset = boolean - resets given var to true
 *			 print = boolean - print var or not. default is true
 *           advance = boolean - whether or not to advance the cycle
 *           delimiter = the value delimiter, default is ","
 *           assign = boolean, assigns to template var instead of
 *                    printed.
 * 
 * Examples: {cycle values="#eeeeee,#d0d0d0d"}
 *           {cycle name=row values="one,two,three" reset=true}
 *           {cycle name=row}
 * -------------------------------------------------------------
 */
function smarty_function_cycle($params, &$smarty)
{
	static $cycle_vars;
	
    extract($params);

    if (empty($name)) {
        $name = 'default';
    }

    if (!isset($print)) {
        $print = true;
    }

    if (!isset($advance)) {
        $advance = true;		
    }	

    if (!isset($reset)) {
        $reset = false;		
    }		
			
    if (!in_array('values', array_keys($params))) {
		if(!isset($cycle_vars[$name]['values'])) {
        	$smarty->trigger_error("cycle: missing 'values' parameter");
        	return;
		}
    } else {
		if(isset($cycle_vars[$name]['values'])
			&& $cycle_vars[$name]['values'] != $values ) {
			$cycle_vars[$name]['index'] = 0;
		}
		$cycle_vars[$name]['values'] = $values;
	}

    if (isset($delimiter)) {
		$cycle_vars[$name]['delimiter'] = $delimiter;
    } elseif (!isset($cycle_vars[$name]['delimiter'])) {
		$cycle_vars[$name]['delimiter'] = ',';		
	}
	
	if(!is_array($cycle_vars[$name]['values'])) {
		$cycle_array = explode($cycle_vars[$name]['delimiter'],$cycle_vars[$name]['values']);
	} else {
		$cycle_array = $cycle_vars[$name]['values'];	
	}
	
	if(!isset($cycle_vars[$name]['index']) || $reset ) {
		$cycle_vars[$name]['index'] = 0;
	}
	
    if (isset($assign)) {
        $print = false;
        $smarty->assign($assign, $cycle_array[$cycle_vars[$name]['index']]);
    }
		
	if($print) {
		echo $cycle_array[$cycle_vars[$name]['index']];
	}

	if($advance) {
		if ( $cycle_vars[$name]['index'] >= count($cycle_array) -1 ) {
			$cycle_vars[$name]['index'] = 0;			
		} else {
			$cycle_vars[$name]['index']++;
		}
	}
}

/* vim: set expandtab: */

?>
