<?php
/**
 * phpGACL - Generic Access Control List
 * Copyright (C) 2002,2003 Mike Benoit
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
 * @package phpGACL
 *
 */

/*
 *
 * For examples, see example.php or the Administration interface,
 * as it makes use of nearly every API Call.
 *
 */
/**
 * gacl_admin_api Custom Extended API Class
 *
 * Class gacl_api should be used for applications that must interface directly with
 * phpGACL's data structures, objects, and rules.
 *
 * @package phpGACL
 * @author Mike Benoit <ipso@snappymail.ca>
 *
 */

class gacl_admin_api extends gacl_api {

	/*
	 * Administration interface settings
	 */
 	/** @var int Number of items to display per page in the phpGACL interface. */
	var $_items_per_page = 100;
 	/** @var int Maximum number of items to display in a select box. Override to manage large collections via ACL Admin */
	var $_max_select_box_items = 100;
 	/** @var int Maximum number of items to return in an ACL Search. */
	var $_max_search_return_items = 100;

	/*
	 *
	 * Misc admin functions.
	 *
	 */

	/**
	 * return_page()
	 *
	 * Sends the user back to a passed URL, unless debug is enabled, then we don't redirect.
	 * 				If no URL is passed, try the REFERER
	 * @param string URL to return to.
	 */
	function return_page($url="") {
		global $_SERVER, $debug;

		if (empty($url) AND !empty($_SERVER[HTTP_REFERER])) {
			$this->debug_text("return_page(): URL not set, using referer!");
			$url = $_SERVER[HTTP_REFERER];
		}

		if (!$debug OR $debug==0) {
			header("Location: $url\n\n");
		} else {
			$this->debug_text("return_page(): URL: $url -- Referer: $_SERVER[HTTP_REFERRER]");
		}
	}

	/**
	 * get_paging_data()
	 *
	 * Creates a basic array for Smarty to deal with paging large recordsets.
	 *
	 * @param ADORecordSet ADODB recordset.
	 */
	function get_paging_data($rs) {
                return array(
                                'prevpage' => $rs->absolutepage() - 1,
                                'currentpage' => $rs->absolutepage(),
                                'nextpage' => $rs->absolutepage() + 1,
                                'atfirstpage' => $rs->atfirstpage(),
                                'atlastpage' => $rs->atlastpage(),
                                'lastpageno' => $rs->lastpageno()
                        );
	}

}
?>
