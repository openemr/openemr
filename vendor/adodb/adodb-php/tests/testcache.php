<html>
<body>
<?php
/*
@version   v5.20.2  27-Dec-2015
@copyright (c) 2000-2013 John Lim (jlim#natsoft.com). All rights reserved.
@copyright (c) 2014      Damien Regad, Mark Newnham and the ADOdb community
  Released under both BSD license and Lesser GPL library license.
  Whenever there is any discrepancy between the two licenses,
  the BSD license will take precedence.
  Set tabs to 4 for best viewing.

  Latest version is available at http://adodb.sourceforge.net
*/

$ADODB_CACHE_DIR = dirname(tempnam('/tmp',''));
include("../adodb.inc.php");

if (isset($access)) {
	$db=ADONewConnection('access');
	$db->PConnect('nwind');
} else {
	$db = ADONewConnection('mysql');
	$db->PConnect('mangrove','root','','xphplens');
}
if (isset($cache)) $rs = $db->CacheExecute(120,'select * from products');
else $rs = $db->Execute('select * from products');

$arr = $rs->GetArray();
print sizeof($arr);
