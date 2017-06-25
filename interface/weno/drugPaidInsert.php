<?php
/**
 * weno drug paid insert
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */



require_once('../globals.php');

$drugs = file_get_contents('drugspaidinsert.sql'); 

sqlInsert($drugs);

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
