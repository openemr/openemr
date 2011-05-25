<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * amcCollect() version for smarty templates
 * 
 * Copyright (C) 2011 Brady Miller <brady@sparmy.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 */


/**
 * Smarty {amcCollect} function plugin
 *
 * Type:     function<br>
 * Name:     amcCollect<br>
 * Purpose:  amcCollect in OpenEMR - Smarty templates<br>
 * 
 * @param array
 * @param Smarty
 */

require_once(dirname(__FILE__) . '../../amc.php');

function smarty_function_amcCollect($params, &$smarty)
{
	$amc_id = $params['amc_id'];
        $patient_id = $params['patient_id'];
        $object_category = $params['object_category'];
        $object_id = $params['object_id'];

	$returnArray = amcCollect($amc_id,$patient_id,$object_category,$object_id);
        $smarty->assign('amcCollectReturn', $returnArray);
}

/* vim: set expandtab: */

?>
