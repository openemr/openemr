<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     html_options
 * Purpose:  Prints the list of <option> tags generated from
 *           the passed parameters
 * -------------------------------------------------------------
 */
function smarty_function_html_options($params, &$smarty)
{
    $print_result = true;

    extract($params);

    $html_result = '';

    settype($selected, 'array');
    if (isset($options)) {
        settype($options, 'array');
        foreach ($options as $key => $value) {
			$html_result .= smarty_function_html_options_optoutput($key, $value, $selected);
        }
    } else {
        settype($output, 'array');
        settype($values, 'array');
        for ($i = 0, $for_max = count($output); $i < $for_max; $i++) {
            if ($i < count($values)) {
				$html_result .= smarty_function_html_options_optoutput($values[$i], $output[$i], $selected);
			} else {
				$html_result .= smarty_function_html_options_optoutput($output[$i], $output[$i], $selected);
			}
        }
    }

    if ($print_result)
        print $html_result;
    else
        return $html_result;
}

function smarty_function_html_options_optoutput($key, $value, $selected) {
	if(!is_array($value)) {
    	$html_result = "<option label=\"$key\" value=\"$key\"";
    	if (in_array($key, $selected))
        	$html_result .= " selected=\"selected\"";
    	$html_result .= ">$value</option>\n";
	} else {
		$html_result = smarty_function_html_options_optgroup($key, $value, $selected);
	}
		return $html_result;	
}

function smarty_function_html_options_optgroup($key, $values, $selected) {
	$optgroup_html = "<optgroup label=\"$key\">\n";
	foreach ($values as $key => $value) {
		$optgroup_html .= smarty_function_html_options_optoutput($key, $value, $selected);
	}
	$optgroup_html .= "</optgroup>\n";
	return $optgroup_html;
}

/* vim: set expandtab: */

?>
