<?php


/*
@version   v5.21.0  2021-02-27
@copyright (c) 2000-2013 John Lim (jlim#natsoft.com). All rights reserved.
@copyright (c) 2014      Damien Regad, Mark Newnham and the ADOdb community
         Contributed by Ross Smith (adodb@netebb.com).
  Released under both BSD license and Lesser GPL library license.
  Whenever there is any discrepancy between the two licenses,
  the BSD license will take precedence.
	  Set tabs to 4 for best viewing.
*/

/*

This file is provided for backwards compatibility purposes

*/

if (!defined('ADODB_SESSION')) {
	require_once dirname(__FILE__) . '/adodb-session.php';
}
ADODB_Session::clob('CLOB');
