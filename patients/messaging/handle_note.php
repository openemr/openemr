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

session_start();
if( isset( $_SESSION['pid'] ) && isset( $_SESSION['patient_portal_onsite'] ) ){
	$owner = $_SESSION['pid'];
	$ignoreAuth = true;
	$fake_register_globals=false;
	$sanitize_all_escapes=true;
	require_once ( dirname( __FILE__ ) . "/../../interface/globals.php" );
} else{
	session_destroy();
	$ignoreAuth = false;
	$sanitize_all_escapes = true;
	$fake_register_globals = false;
	require_once ( dirname( __FILE__ ) . "/../../interface/globals.php" );
	if( ! isset( $_SESSION['authUserID'] ) ){
		$landingpage = "index.php";
		header( 'Location: ' . $landingpage );
		exit();
	}
}

require_once (dirname( __FILE__ ) . "/../lib/portal_mail.inc");

$task = $_POST ['task'];
if (! $task)
	return 'no task';

$noteid = $_POST ['noteid'] ? $_POST ['noteid'] : 0;
$owner = isset($_POST ['owner']) ? $_POST ['owner'] : $_SESSION ['pid'];
$note = $_POST ['inputBody'];
$title = $_POST ['title'];
$sid=(int)$_POST ['sender_id'];
$sn=$_POST ['sender_name'];
$rid=(int)$_POST ['recipient_id'];
$rn=$_POST ['recipient_name'];
$header = '';

switch ($task) {
	case "add" :
		{
			sendMail ( $owner, $note, $title, $header, $noteid,$sid,$sn,$rid,$rn );
		}
		break;
	case "reply" :
		{
			sendMail ( $owner, $note, $title, $header, $noteid,$sid,$sn,$rid,$rn );
		}
		break;
	case "delete" :
		{
			updatePortalMailMessageStatus ( $noteid, 'Delete' );
		}
		break;
	case "setread" :
		{
			if ($noteid > 0) {
				updatePortalMailMessageStatus ( $noteid, 'Read' );
			} else
				echo 'missing note id';
		}
		break;
		case "getinbox" :
			{
				if ($owner) {
					$result = getMails($owner,'inbox','','');
					echo json_encode($result);
				} else
					echo 'error';
			}
			break;
	case "getsent" :
		{
			if ($owner) {
				$result = getMails($owner,'sent','','');
				echo json_encode($result);
			} else
				echo 'error';
		}
		break;
		case "getall" :
			{
				if ($owner) {
					$result = getMails($owner,'all','','');
					echo json_encode($result);
				} else
					echo 'error';
			}
			break;
			case "getdeleted" :
				{
					if ($owner) {
						$result = getMails($owner,'deleted','','');
						echo json_encode($result);
					} else
						echo 'error';
				}
				break;
}
if(isset($_REQUEST["submit"]))
	header("Location: {$_REQUEST["submit"]}");
?>