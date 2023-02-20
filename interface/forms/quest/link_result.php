<?php
/** **************************************************************************
 *	QUEST/LINK_RESULT.PHP
 *
 *	Copyright (c)2014 - Williams Medical Technology, Inc.
 *
 *	This program is licensed software: licensee is granted a limited nonexclusive
 *  license to install this Software on more than one computer system, as long as all
 *  systems are used to support a single licensee. Licensor is and remains the owner
 *  of all titles, rights, and interests in program.
 *  
 *  Licensee will not make copies of this Software or allow copies of this Software 
 *  to be made by others, unless authorized by the licensor. Licensee may make copies 
 *  of the Software for backup purposes only.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 *  FOR A PARTICULAR PURPOSE. LICENSOR IS NOT LIABLE TO LICENSEE FOR ANY DAMAGES, 
 *  INCLUDING COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL 
 *  DAMAGES, CONNECTED WITH OR RESULTING FROM THIS LICENSE AGREEMENT OR LICENSEE'S 
 *  USE OF THIS SOFTWARE.
 *
 *  @package laboratory
 *  @subpackage quest
 *  @version 2.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Ron Criswell <info@keyfocusmedia.com>
 *  @uses quest/link_result.php
 * 
 *************************************************************************** */
require_once("../../globals.php");

$form_title = 'Quest Result Link';
$form_name = 'link_result';
$form_id = $_GET['id'];

?>
<!DOCTYPE HTML>
<html>
	<head>
		<?php //html_header_show();?>
		<title><?php echo $form_title; ?></title>
		
		<script>
		<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

		function refreshResults() {
			window.opener.refreshme();
		}
		</script>
		
	</head>
	
	<frameset>
		<frame src="link_search.php?id=<?php echo $form_id ?>" />
	</frameset>

</html>