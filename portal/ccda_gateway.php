<?php
//authencate for portal or main- never know where it gets used
session_start();
if ( isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two']) ) {
	$pid = $_SESSION['pid'];
	$ignoreAuth = true;
	$sanitize_all_escapes = true;
	$fake_register_globals = false;
	require_once ( dirname( __FILE__ ) . "/../interface/globals.php" );
	define('IS_DASHBOARD', false);
	define('IS_PORTAL', $_SESSION['pid']);
}
else {
	session_destroy();
	$ignoreAuth = false;
	$sanitize_all_escapes = true;
	$fake_register_globals = false;
	require_once ( dirname( __FILE__ ) . "/../interface/globals.php" );
	if ( ! isset($_SESSION['authUserID']) ){
		$landingpage = "index.php";
		header('Location: '.$landingpage);
		exit;
	}
	define('IS_DASHBOARD', $_SESSION['authUserID']);
	define('IS_PORTAL', false);
}
// give me something to do.
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

if(!isset($_SESSION ['site_id'])) $_SESSION ['site_id'] = 'default'; // do believe globals does this but I go rogue at times.
$server_url = 'http://localhost'. $GLOBALS['webroot'];  // I alias into openemr directory on my sights causing webroot to be empty.
																							//I've have accually seen this return 'default' due to apache config'ed with localhost alias on more than one virtual host?? Watch
//global $server_url; // can't find where this is defined!
// CCM returns entire cda with service doing templates
$ccdaxml = portalccdafetching($pid, $server_url, $parameterArray);
// disposal decisions will be here.
$h='';
if (!$parameterArray ['view']){
	header ( 'Content-Type: application/xml' );
}
else $h='<a href="./home.php" </a><button style="font-size:18px; color:red;" >Return Home</button><br>';
print_r ( $h.$ccdaxml.$h );

exit;

function portalccdafetching($pid, $server_url, $parameterArray){
	$site_id = $_SESSION ['site_id'];
	$parameters = http_build_query($parameterArray); // future use
		try {
			$ch = curl_init();
			$url = $server_url . "/interface/modules/zend_modules/public/encounterccdadispatch/index?site=$site_id&param=1&view=1&combination=$pid&recipient=patient";
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0); // set true for look see
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_COOKIESESSION, true);
			curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie");
			curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie");
			//curl_setopt ($ch, CURLOPT_COOKIE, 'XDEBUG_SESSION=1'); // break on first line in public/index.php - uncomment and start any xdebug session and fetch a ccda in app.
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
