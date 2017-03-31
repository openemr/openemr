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
require_once ( dirname( __FILE__ ) . "/../library/log.inc" );

function runCheck(){
	if( !socket_status( 'localhost', '6661', 'status' ) ){
		server_logit( 1, "Execute C-CDA Service Start", 0, "Task" );
		execInBackground( '' );
		sleep( 2 );
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
function service_shutdown($soft=1){
	if( socket_status( 'localhost', '6661', 'status' ) ){
		// shut down service- this can take a few seconds on windows so throw up notice to user.
		flush();
		echo '<h3 style="position: absolute; top: 25%; left: 42%">'. xlt("Shutting Down Service ...") . '</h3><img style="position: absolute; top: 40%; left: 45%; width: 125px; height: 125px" src="../../portal/sign/assets/loading.gif" />';
		ob_flush(); flush();
		server_logit( 1, "C-CDA Service shutdown request", 0, "Task" );
		if(!IS_WINDOWS){
			chdir(dirname(__FILE__));
			$cmd = 'pkill -f "nodejs serveccda.njs"';
			exec( $cmd . " > /dev/null &" );
		}
		else{
			chdir(dirname(__FILE__));
			$cmd = 'node unservice';
			pclose( popen( $cmd, "r" ) );
		}
		sleep( 1 );
		if( !socket_status( 'localhost', '6661', 'status' ) ){
			server_logit( 1, "Service Status : " . $soft ? "Process Terminated" : "Terminated and Disabled." );
			if($soft > 1) return true; // Just terminate process/service and allow background to restart. Restart if you will.
			$service_name = 'ccdaservice';
			// with ccdaservice and background service and running = 1 bs will not attempt restart of service while still available/active.
			// not sure if needed but here it is anyway. Otherwise, service is disabled.
			$sql = 'UPDATE background_services SET running = ?, active = ? WHERE name = ?';
			$res = sqlStatementNoLog($sql, array($soft, $soft, $service_name));
			return true;
		}
		else{
			server_logit( 1, "Service Status : Failed Shutdown." );
			return false;
		}
	} else{
		server_logit( 1, "Service Status : Not active.", 0, "Shutdown Request" );
		return true;
	}
}
function execInBackground( $cmd ){
	if( IS_WINDOWS ){
		chdir(dirname(__FILE__));
		$cmd = 'node winservice';
		pclose( popen( $cmd, "r" ) ); 
	} else{
		chdir(dirname(__FILE__));
        $cmd = 'nodejs serveccda.njs';
		exec( $cmd . " > /dev/null &" );
	}
}
function socket_status( $ip, $port, $data ){
	$output = "";
	$socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
	if( $socket === false ){
		server_logit( 1, "Creation of Socket Failed. Start/Restart Service" );
		return false;
	}
	$result = socket_connect( $socket, $ip, $port );
	if( $result === false ){
		socket_close( $socket );
		server_logit( 1, "Service Not Running" );
		return false;
	}
	$data = $data  . "\r\n";
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
function service_command( $ip, $port, $doaction ){
	$output = "";
	$socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
	if( $socket === false ){
		server_logit( 1, "Service not resident." );
		return false;
	}
	$result = socket_connect( $socket, $ip, $port );
	if( $result === false ){
		socket_close( $socket );
		server_logit( 1, "Service Not Running." );
		return false;
	}
	$doaction = $doaction  . "\r\n";
	$out = socket_write( $socket, $doaction, strlen( $doaction ) );
	do{
		$line = "";
		$line = socket_read( $socket, 1024, PHP_NORMAL_READ );
		$output .= $line;
	} while( $line != "\r" );
	$output = substr( trim( $output ), 0, strlen( $output ) - 3 );
	socket_close( $socket );
	return true;
}
function server_logit( $success, $text, $pid = 0, $event = "ccdaservice-manager" ){
	$pid = isset($_SESSION['pid'])?$_SESSION['pid']:$pid;
	$event = isset($_SESSION['ptName']) ? ('Ccda Access: ' . $_SESSION['ptName']) : "Ccda Service Access";
	$where = isset($_SESSION['ptName']) ? "Portal Patient" : 'OpenEMR:  ' . $_SESSION['authUser'];
	
	newEvent( $event, "Service_Manager", $where, $success, $text, $pid,'server','s2','s3' );
}
