<?php
/*
 * phpGACL - Generic Access Control List
 * Copyright (C) 2002 Mike Benoit
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * For questions, help, comments, discussion, etc., please join the
 * phpGACL mailing list. http://sourceforge.net/mail/?group_id=57103
 *
 * You may contact the author of phpGACL by e-mail at:
 * ipso@snappymail.ca
 *
 * The latest version of phpGACL can be obtained from:
 * http://phpgacl.sourceforge.net/
 *
 */


// Include standard libraries/classes
require_once(dirname(__FILE__).'/../../vendor/autoload.php');

use OpenEMR\Gacl\GaclAdminApi;

// phpGACL Configuration file.
if ( !isset($config_file) ) {
#	$config_file = '../gacl.ini.php';
	$config_file = dirname(__FILE__).'/../gacl.ini.php';
}

//Values supplied in $gacl_options array overwrite those in the config file.
if ( file_exists($config_file) ) {
	$config = parse_ini_file($config_file);

	if ( is_array($config) ) {
		if ( isset($gacl_options) ) {
			$gacl_options = array_merge($config, $gacl_options);
		} else {
			$gacl_options = $config;
		}
	}
	unset($config);
}

$gacl_api = new GaclAdminApi($gacl_options);

$gacl = &$gacl_api;

$db = &$gacl->db;

$smarty = new Smarty;
$smarty->compile_check = TRUE;
$smarty->template_dir = $gacl_options['smarty_template_dir'];
$smarty->compile_dir = $GLOBALS['OE_SITE_DIR'] . '/documents/smarty/gacl';

/*
 * Email address used in setup.php, please do not change.
 */
$author_email = 'ipso@snappymail.ca';

/*
 * Don't need to show notices, some of them are pretty lame and people get overly worried when they see them.
 * Mean while I will try to fix most of these. ;) Please submit patches if you find any I may have missed.
 */
//commented out below to instead have php decide which errors to show.
//error_reporting (E_ALL ^ E_NOTICE);
?>
