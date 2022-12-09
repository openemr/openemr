<?php
/** **************************************************************************
 *	SoapAuthClient.PHP
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
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 * 
 *************************************************************************** */
class SoapAuthClient extends SoapClient {
	/**
	 * Since the PHP SOAP package does not support basic authentication
	 * this class downloads the WDSL file using the cURL package and
	 * creates a local copy of the wsdl on the server.
	 * 
	 * Make sure you provide the following additional parameter in the
	 * $options Array: wsdl_local_copy => true
	 */

	function SoapAuthClient($wsdl, $options) {
//		echo "\n" . $wsdl;
		if (isset($options['wsdl_local_copy']) &&
				isset($options['login']) &&
				isset($options['password']) &&
				isset($options['wsdl_path'])) {
			 
			$file = "/" . $options['wsdl_local_copy'].'.xml'; 
			
			$path = $options['wsdl_path'];
			if (!file_exists($path)) {
				if (!mkdir($path,0700)) {
					throw new Exception('Unable to create directory for WSDL file ('.$path.')');
				}
			}

			$path .= "/wsdl"; // subdirectory
			if (!file_exists($path)) {
				if (!mkdir($path,0700)) {
					throw new Exception('Unable to create subdirectory for WSDL file ('.$path.')');
				}
			}
				
			if (($fp = fopen($path.$file, "w+")) == false) {
				throw new Exception('Could not create local WSDL file ('.$path.$file.')');
			}
				 
			$ch = curl_init();
			$credit = ($options['login'].':'.$options['password']);
			curl_setopt($ch, CURLOPT_URL, $wsdl);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, $credit);
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);
			curl_setopt($ch, CURLOPT_FILE, $fp);
				
			// testing only!!
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					
			if (($xml = curl_exec($ch)) === false) {
				curl_close($ch);
				fclose($fp);
				unlink($path.$file);
				 
				$ch = curl_init();
				$credit = ($options['login'].':'.$options['password']);
				curl_setopt($ch, CURLOPT_URL, $wsdl);
				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
				curl_setopt($ch, CURLOPT_USERPWD, $credit);
				curl_setopt($ch, CURLOPT_TIMEOUT, 15);
				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				
				if (($xml = curl_exec($ch)) === false) {
					curl_close($ch);
					fclose($fp);
					unlink($path.$file);
				}
				 
				throw new Exception(curl_error($ch));
			}
				 
			curl_close($ch);
			fclose($fp);
			$wsdl = "file:///".$path.$file;
		}
		 
		unset($options['wsdl_local_copy']);
		unset($options['wsdl_force_local_copy']);
		 
//		echo "\n" . $wsdl;
		parent::__construct($wsdl, $options);
	}
}
?>