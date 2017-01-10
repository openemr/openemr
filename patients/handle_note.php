<?php
/** 
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 */

require_once ("verify_session.php");
require_once ("./lib/portal_pnotes.inc");

$pid = $_SESSION ['pid'];
$task = $_POST ['task'];
if (! $task)
	return 'no task';
$noteid = $_POST ['noteid'] ? $_POST ['noteid'] : 0;
$note = $_POST ['inputBody'];
$title = $_POST ['title'];
$to = $_POST ['sendto']?$_POST ['sendto']:$_SESSION ['providerUName'];

switch ($task) {
	case "add" :
		{
			sendMail ( $pid, $note, $title, $to, $noteid );
			echo 'done';
		}
		break;
	case "reply" :
		{
			sendMail ( $pid, $note, $title, $to, $noteid );
			echo 'done';
		}
		break;
	case "setread" :
		{
			if ($noteid > 0) {
				updatePortalPnoteMessageStatus ( $noteid, 'Read' );
				echo 'done';
			} else
				echo 'missing note id';
		}
		break;
	case "getsent" :
		{
			if ($pid) {
				$result = getMails($pid,'sent','','');
				echo json_encode($result);
				
			} else
				echo 'error';
		}
		break;
		case "getall" :
			{
				if ($pid) {
					$iresult = getMails($pid,'inbox','','');
					$sresult = getMails($pid,'sent','','');
					$result = array_merge((array)$iresult,(array)$sresult);
					echo json_encode($result);
				} else
					echo 'error';
			}
			break;		
}

?>