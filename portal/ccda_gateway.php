<?php
require_once ("verify_session.php");
$dowhat = $_REQUEST['action'] ? $_REQUEST['action'] : '';
if( $dowhat ){ // do I need this?
	require_once ("./../ccdaservice/ssmanager.php");
	if(!runCheck()){ // woops, try again
		if(!runCheck())
			die("Document service start failed. Click back to return home."); // nuts! give up
	}
}
else{
	// maybe next time
	die("Don't know what to do! Click back to return home."); // Die an honorable death!!
}
//$ignoreAuth = true;
//$fake_register_globals = false;
//$sanitize_all_escapes = true;
//require_once ('../interface/globals.php');

//eventually below will qualify what document to fetch
$parameterArray = array();
$parameterArray ['encounter'];
$parameterArray ['combination'] = $pid;
$parameterArray ['components']; // = 'progress_note|consultation_note|continuity_care_document|diagnostic_image_reporting|discharge_summary|history_physical_note|operative_note|procedure_note|unstructured_document';
$parameterArray ['sections']; // = 'allergies|medications|problems|immunizations|procedures|results|plan_of_care|vitals|social_history|encounters|functional_status|referral|instructions';
$parameterArray ['downloadccda']=1;
$parameterArray ['sent_by'];
$parameterArray ['send'];
$parameterArray ['view'] = 1;
$parameterArray ['recipients'] = 'patient'; // emr_direct or hie else if not set $_SESSION['authUserID']
$parameterArray [0] [6] = $_SESSION ['portal_username']; // set to an onsite portal user
if(!isset($_SESSION ['site_id'])) $_SESSION ['site_id'] = 'default';

$ccdaxml = portalccdafetching($pid);

$h='';
if (!$parameterArray ['view']){
	header ( 'Content-Type: application/xml' );
}
else $h='<a href="./home.php" </a><button style="font-size:18px; color:red;" >Return Home</button><br>';
print_r ( $h.$ccdaxml.$h );


function portalccdafetching($pid,$parameterArray){
	$server_url = 'http://localhost'. $GLOBALS['web_root'];
	//$cookieFile = 'c:/xampp/htdocs/openemr/sites/default/documents/portal.txt';
	$site_id = $_SESSION ['site_id'];
	$parameters = http_build_query($parameterArray);
		try {
			$ch = curl_init();
			$url =  $server_url . "/interface/modules/zend_modules/public/encounterccdadispatch/index?site=$site_id&param=1&view=1&combination=$pid&recipient=patient";

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0); // set true for look see
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_COOKIESESSION, true);
			curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie");
			curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie");
			
			//curl_setopt ($ch, CURLOPT_COOKIE, 'XDEBUG_SESSION=1'); // break first line index
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			$result = curl_exec($ch) or die(curl_error($ch));
			curl_close($ch);
		}
		catch (Exception $e) {

		}
		return $result;
}
return 0;
?>
