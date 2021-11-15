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

// security - hide paths
if (!defined('ADODB_SESSION')) die();

include_once ADODB_SESSION . '/crypt.inc.php';

/**
 */
class ADODB_Encrypt_MD5 {
	/**
	 */
	function write($data, $key) {
		$md5crypt = new MD5Crypt();
		return $md5crypt->encrypt($data, $key);
	}

	/**
	 */
	function read($data, $key) {
		$md5crypt = new MD5Crypt();
		return $md5crypt->decrypt($data, $key);
	}

}

return 1;
