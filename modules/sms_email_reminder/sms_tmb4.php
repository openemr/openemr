<?php
////////////////////////////////////////////////////////////////////
// Class:	TM4B SMS Api
// Usage:
// <code>
// require_once("sms_tm4b.php");
// $sms = new sms( "user", "pass" );
// $sms->send("123456789","sender","message");
// </code>
// 
// Package:	sms_tm4b
// Created by:	Avasiloaei Dorin
// Modified by:	Larry Lart
////////////////////////////////////////////////////////////////////
class sms
{
       // init vars
 	var $username = "";
 	var $password = "";
	
 	function sms( $strUser, $strPass )
 	{
 	  $this->username = $strUser;
          $this->password = $strPass;	
 	}
 	
 	/**
 	 * Send sms method
 	 * @access public
 	 * @return string response
 	 */
 	 
 	function send($phoneNo, $sender, $message)
 	{
 		/* Prepare the server request */
 		$request = "";
 		$request .= "username=".urlencode($this->username);
 		$request .= "&password=".urlencode($this->password);
 		$request .= "&revision=2.0";
 		$request .= "&type=broadcast";
 		$request .= "&msg=".urlencode($message);
 		$request .= "&to=".urlencode($phoneNo);

        	// larry :: default if not defined - TODO  replace 
        	if( !$sender )
                  $request .= "&from=BosmanGGZ";
                else
 		  $request .= "&from=".urlencode($sender);
 		
 		$request .= "&route=GD02";
 		
 		/**
 		 * Send the request to the server
 		 * @TODO make sure the request was sent
 		 */
 		 
 		$response = $this->_send($request);
 		// larry :: debug
 		echo "DEBUG :SMS ENGINE: sms sent with code =".$response." for req= ".$request."\n"; 
 		
 		/**
 		 * Return the server response
 		 * @TODO parse the server response
 		 */
 		 
 		return $response;
 	}

 	/**
 	 * Send sms method
 	 * @access private
 	 * @return string response
 	 */
 	function _send($request)
 	{
 		if(extension_loaded('curl'))
 		{
 			/** 
 			 * cURL extension is installed 
 			 * call the method that sends the sms through cURL
 			 */	 
 			 
 			$response = $this->_send_curl($request);
 		}
 		elseif(!extension_loaded('sockets'))
 		{
 			/**
 			 * Sockets extension is installed
 			 * call the method that sends the sms through sockets
 			 */
 			 
 			 $response = $this->_send_sock($request);
 		}
 		else
 		{
 			/**
 			 * The required extensions are not installed
 			 * call the method that sends the sms using file_get_contents
 			 */
 			 
 			 $response = file_get_contents("https://www.tm4b.com/client/api/http.php?".$request);
 		}
 		
 		/* Return the server response */
 		 return $response;
 	}
 	
 	/**
 	 * Send SMS through cURL
 	 * @access private
 	 * @return string response
 	 */
 	
 	function _send_curl($request)
 	{
 		/* Initiate a cURL session */
 		$ch = curl_init();
 		
 		/* Set cURL variables */
 		curl_setopt($ch, CURLOPT_URL, "https://www.tm4b.com/client/api/http.php"); 
 		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
 		curl_setopt($ch, CURLOPT_POST, 1); 
 		curl_setopt($ch, CURLOPT_POSTFIELDS, $request); 
 		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
 		
 		/* Send the request through cURL */ 
 		$response = curl_exec($ch); 
 		
 		/* End the cURL session */
 		curl_close($ch);
 		
 		/* Return the server response */
 		return $response;
 	}
 	
 	/**
 	 * Send SMS using the sockets extension
 	 * @access private
 	 * @return string response
 	 */
 	function _send_sock($request)
 	{
 		/* Prepare the HTTP headers */
 		$http_header = "POST /client/api/http.php HTTP/1.1\r\n";
 		$http_header .= "Host: tm4b.com\r\n";
 		$http_header .= "User-Agent: HTTP/1.1\r\n";
 		$http_header .= "Content-Type: application/x-www-form-urlencoded\r\n"; 
 		$http_header .= "Content-Length: ".strlen($request)."\r\n";
 		$http_header .= "Connection: close\r\n\r\n";
 		$http_header .= $request."\r\n";
 		
 		/* Set the host that we are connecting to and the port number */
 		$host = "ssl://tm4b.com";
 		$port = 443;
 		
 		/* Connect to the TM4B server */
 		$out = @fsockopen($host, $port, $errno, $errstr);
 		
 		/* Make sure that the connection succeded */
 		if($out)
 		{
 			/* Send the request */
 			fputs($out, $http_header);
 			
 			/* Get the response */
 			while(!feof($out)) $result[] = fgets($out);
 			
 			/* Terminate the connection */
 			fclose($out);
 		}
 		/* Get the response from the returned string */
 		$response = $result[9];
 	}
 }

?>
