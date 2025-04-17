<?php
/**
 * GaclAdminApi class - phpGACL custom extended API Class
 *
 * Original code from phpGACL - Generic Access Control List
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Mike Benoit <ipso@snappymail.ca>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2002-2003 Mike Benoit <ipso@snappymail.ca>
 * @copyright Copyright (c) 2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://www.gnu.org/licenses/old-licenses/lgpl-2.1.html GNU Lesser General Public License 2.1
 */


namespace OpenEMR\Gacl;

class GaclAdminApi extends GaclApi {

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
	 * Sends the user back to a passed URL
	 * @param string URL to return to.
	 */
	function return_page($url="") {
        $return_page = basename($url);
        header('Location: ' . $GLOBALS['web_root'] . "/gacl/admin/" . $return_page);
        exit;
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
