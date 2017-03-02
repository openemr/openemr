<?php
$ignoreAuth = true;
// require_once(dirname(__file__) . './../../interface/globals.php');
require_once ( dirname( __FILE__ ) . "/../../library/log.inc" );
require_once ( dirname( __FILE__ ) . "/../../library/sql.inc" );
set_time_limit( 0 );
ob_implicit_flush();
session_write_close(); // Release session lock to prevent freezing of other scripts
ignore_user_abort( 1 );

function runCheck(){
	if( !socket_status( 'localhost', '6661', 'status' ) ){
		server_logit( 1, "Execute C-CDA Service Start", 0, "Sanity Check" );
		execInBackground( '' );
		sleep( 5 );
		if( socket_status( 'localhost', '6661', 'status' ) )
			server_logit( 1, "Service Status : Started." );
		else 
			server_logit( 1, "Service Status : Failed Start." );
		return true;
	} else{
		server_logit( 1, "Service Status : Alive.", 0, "Sanity Check" );
		return true;
	}
}
function execInBackground( $cmd ){
	if( substr( php_uname(), 0, 7 ) == "Windows" ){
		chdir(dirname(__FILE__));
		$cmd = 'node winservice.js';
		pclose( popen( $cmd, "r" ) ); 
	} else{
		chdir(dirname(__FILE__));
        $cmd = 'node serveccda.njs';
		//$cmd = 'node /var/www/openemr_demo/services/ccdaservice/serveccda.njs';
		//$cmd = 'node winservice.js'; // linux service
		exec( $cmd . " > /dev/null &" );
	}
}
function socket_status( $ip, $port, $data ){
	$output = "";
	$socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
	if( $socket === false ){
		server_logit( 1, "Creation Socket Failed. Start:Restart service" );
		return false;
	}
	$result = socket_connect( $socket, $ip, $port );
	if( $result === false ){
		socket_close( $socket );
		server_logit( 1, "Service Not Running:Start/Restart Service" );
		return false;
	}
	$data = $data  . "\r\n";
	//server_logit( 1, 'Send for Service status.' );
	$out = socket_write( $socket, $data, strlen( $data ) );
	do{
		$line = "";
		$line = socket_read( $socket, 1024, PHP_NORMAL_READ );
		$output .= $line;
	} while( $line != "\r" );
	$output = substr( trim( $output ), 0, strlen( $output ) - 3 );
	socket_close( $socket );
return true;
}
function server_logit( $success, $text, $pid = 0, $event = "ccda-service-manager" ){
	newEvent( $event, "Service_Manager", "CCDA", $success, $text, $pid,'server','s2','s3' );
}
