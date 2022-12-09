<?php
/** **************************************************************************
 *	wmtSFTP.class.php
 *
 *	Copyright (c)2018 - Medical Technology Services <MDTechSvcs.com>
 *
 *	This program is free software: you can redistribute it and/or modify it under the 
 *  terms of the GNU General Public License as published by the Free Software Foundation, 
 *  either version 3 of the License, or (at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT ANY
 *	WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 *  PARTICULAR PURPOSE. DISTRIBUTOR IS NOT LIABLE TO USER FOR ANY DAMAGES, INCLUDING 
 *  COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL DAMAGES, 
 *  CONNECTED WITH OR RESULTING FROM THIS AGREEMENT OR USE OF THIS SOFTWARE.
 *
 *	See the GNU General Public License <http://www.gnu.org/licenses/> for more details.
 *
 *  @package wmt
 *  @subpackage sftp
 *  @version 1.0.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

/**
 * All new classes are defined in the WMT namespace
 */
namespace wmt;

use Zend\Http\Header\ContentSecurityPolicy;

/**
 * Provides standardized processing for TCP sftp messages.
 *
 * @package wmt
 * @subpackage sftp
 * 
 */
class SFTP {
	
	private $link;
	private $server;
	private $port;
	private $username;
	private $password;
	private $task;
	private $error;
	private $status;
	private $sftp;
	private $pid;
	private $uid;
	
	
	/**
	 * Constructor for the 'sftp' class which generates all types 
	 * of sFTP transaction messages exchanged with external servers.
	 *
	 * @return object instance of sftp class
	 * 
	 */
	public function __construct($link, $uid='', $pid='') {
		global $srcdir;
		
		// Store defaults
		$this->link = $link;
		$this->task = "CONSTRUCT";
		$this->error = false;
		$this->status = '';

		// Retrieve sFTP parameters
		$link_list = new Options('sFTP_' . $link);
		
		$this->username = $link_list->getItem('username');
		$this->password = $link_list->getItem('password');
		$this->server = $link_list->getItem('server');
		$this->port = $link_list->getItem('port');
		$this->import = $link_list->getItem('import', '/');
		$this->export = $link_list->getItem('export', '/');
		
		// Store defaults for logging
		$this->uid = $uid;
		$this->pid = $pid;
		
		// Validate require library
		if (!file_exists("$srcdir/phpseclib")) {
			$this->error = "SETUP";
			$this->status = "sftp_setup() failed: Missing PHPSECLIB library";
			$this->logSFTP(false);
			
			throw new \Exception("wmtSFTP:construct - " . $this->status);
		}
			
		// Validate required extension
		if (!extension_loaded("openssl")) {
			$this->error = "SETUP";
			$this->status = "sftp_setup() failed: Openssl extension is not enabled";
			$this->logSFTP(false);
			
			throw new \Exception("wmtSFTP:construct - " . $this->status);
		}
		
		// Validate parameters
		if (empty($this->server) || empty($this->port) || empty($this->username) || empty($this->password)) {
			$this->error = "SETUP";
			$this->status = "sftp_setup() failed: Missing sFTP link parameters";
			$this->logSFTP(false);
			
			throw new \Exception("wmtSFTP:construct - " . $this->status);
		}
			
		// Must have phpseclib in path
		$current_path = get_include_path();
		if (strpos($current_path, 'phpseclib') === false)
			set_include_path($current_path . PATH_SEPARATOR . $GLOBALS['srcdir'] . "/phpseclib");

		// Include necessary libraries
		require('Net/SSH2.php');
		require('Net/SFTP.php');
			
		// Create a sFTP connection
		$this->sftp = new \phpseclib\Net\SFTP($this->server, $this->port);
		if (!$this->sftp->login($this->username, $this->password)) {
			$this->error = "LOGIN";
			$this->status = "sftp_setup() failed: sFTP session did not initialize";
			$this->logSFTP(false);
			
			throw new \Exception("wmtSFTP:construct - " . $this->status);
		}
			
		// Verify sFTP connection
		$pwd = $this->sftp->pwd();
		if (empty($pwd)) {
			$this->error = "LOGIN";
			$this->status = "sftp_setup() failed: sFTP session did not respond";
			$this->logSFTP(false);
			
			throw new \Exception("wmtSFTP:construct - " . $this->status);
		}
			
		return true;
	}

	/**
	 * Destructor for the 'sftp' class which closes connections if the
	 * object is no longer referenced.
	 */
	public function __destruct() {
		$this->closeSFTP();
	}
	
	/**
	 * Graceful shutdown and close of sftp.
	 */
	public function closeSFTP() {
		// Close connection
		unset($this->sftp);
		
		// Clear sftp variable
		$this->sftp = null;
	}
	
	/**
	 * The 'listSFTP' method retrieves a list of the contents of the specified directory
	 * on the sftp connection managed by this object.
	 * 
	 * @param string $dir - directory to be listed
	 * @return array $list - directory detailed contents
	 * 
	 */
	public function listSFTP($dir) {
		// Set task being performed
		$this->task = 'LIST';
		
		// Verify sftp is active
		if (empty($this->sftp) || !is_object($this->sftp)) {
			$this->error = "CLOSED";
			$this->status = "failed: SFTP connection is closed or inactive";
			$this->logSFTP(false);
			
			throw new \Exception("wmtSFTP:listSFTP - " . $this->status );
		}
		
		$this->error = false;
		$this->status = '';
			
		// Read directory
		$list = $this->sftp->rawlist($dir);
		
		// Done
		return $list;
		
	}

	/**
	 * The 'sendSFTP' method transmits the string data to the server over the
	 * SFTP connection managed by this object.
	 */
	public function sendSFTP($message) {
		// Set task being performed
		$this->task = 'SEND';
		
		// Verify SFTP is active
		if (empty($this->sftp) || !is_object($this->sftp)) {
			$this->error = "CLOSED";
			$this->status = "sftp_write() failed: SFTP connection is closed or inactive";
			$this->logSFTP(false);
			
			throw new \Exception("wmtSFTP:sendSFTP - " . $this->status );
		}
		
		// Remote file name
		$filename = $this->link .'-'. strtotime('NOW') . '.hl7';
		
		// Retry loop process
		$retry = 0;

		while ($retry++ < 5) {
			$this->error = false;
			$this->status = '';
			
			// Change directory 
			$result = $this->sftp->chdir($this->export);
			if ($result === false) {
				$this->error = $this->sftp->getSFTPLog();
				$this->status = 'sftp->chdir() failed';
				LogError($this->error, $this->status);
				$this->logSFTP('PUT ABORTED');
			
				throw new \Exception("wmtSFTP:sendSFTP - " . $this->status );
			}
		
			// Send the data 
			$result = $this->sftp->put($filename, $message, NET_SFTP_LOCAL_FILE);
			if ($result === false) {
				$this->error = $this->sftp->getSFTPLog();
				$this->status = 'sftp->put() failed';
				LogError($this->error, $this->status);
				$this->logSFTP('PUT ABORTED');
			
				throw new \Exception("wmtSFTP:sendSFTP - " . $this->status );
			}
		
			// Verify file
			$ack = $this->sftp->stat($filename);
			if ($ack === false) { 
				$this->error = 'Upload file verification failed'; 
				$this->status = 'sftp->put() failed';
				LogError($this->error, $this->status);
				$this->logSFTP('PUT ABORTED');
			} else {
				$this->error = false;
				$this->status = 'sftp->put() success';
				$this->logSFTP('PUT SUCCESSFUL');
				break;
			}
			
		} // end of retry loop
		
		// Result status
		if ($this->error) {
			$this->error = "ABORT";
			$this->status = "Put retry limit reached";
			$this->logSFTP('PUT ABORTED');
			
			throw new \Exception("wmtSFTP:sendSFTP - " . $this->status );
		}
		
		// Done
		return $ack;
	}

	/**
	 * The 'readSFTP' method receives a file from the server and returns the contents
	 * using the sftp connection managed by this object.
	 * 
	 * @param string $input - remote input file requested
	 * @return string $content - contents of file
	 */
	public function readSFTP($input) {
		// Set task being performed
		$this->task = 'READ';
		
		// Verify sftp is active
		if (empty($this->sftp) || !is_object($this->sftp)) {
			$this->error = "CLOSED";
			$this->status = "failed: SFTP connection is closed or inactive";
			$this->logSFTP(false);
			
			throw new \Exception("wmtSFTP:readSFTP - " . $this->status );
		}
		
		// Read the data
		$content = $this->get($input);
		
		// Result status
		if ($content === false || empty($content)) {
			$this->error = "FAILED";
			$this->status = "failed: unable to read file '$input'";
			$this->logSFTP(false);
			
			throw new \Exception("wmtSFTP:readSFTP - " . $this->status );
		}
		
		// Log and return
		$this->log($content);
		return $content;
		
	}

	/**
	 * The 'copySFTP' method receives a file from the server stores contents in given file
	 * using the sftp connection managed by this object.
	 * 
	 * @param string $input - remote input file requested
	 * @param string $output - file where output is stored
	 *
	 */
	public function copySFTP($input, $output) {
		// Set task being performed
		$this->task = 'COPY';
		
		// Verify sftp is active
		if (empty($this->sftp) || !is_object($this->sftp)) {
			$this->error = "CLOSED";
			$this->status = "failed: SFTP connection is closed or inactive";
			$this->logSFTP(false);
			
			throw new \Exception("wmtSFTP:copySFTP - " . $this->status );
		}
		
		// Read the data
		$result = $this->get($input, $output);
		
		// Result status
		if ($result === false || !file_exists($output)) {
			$this->error = "FAILED";
			$this->status = "failed: unable to copy file '$input' to '$output'";
			$this->logSFTP(false);
			
			throw new \Exception("wmtSFTP:copySFTP - " . $this->status );
		}
		
		// Log and return
		$content = file_get_contents($output);
		$this->log($content);
		return;
	}

	/**
	 * The 'logSFTP' method stores a copy of the messages which are exchanged.
	 */
	public function logSFTP($message) {
		// Deal with false message
		if (!$message) $message = '';
		
		// Store data elements
		$binds = array();
		$binds['date'] = date('Y-m-d H:i:s');
		$binds['pid'] = $this->pid;
		$binds['user'] = $this->uid;
		$binds['link'] = $this->link;
		$binds['task'] = $this->task;
		$binds['error'] = $this->error;
		$binds['status'] = $this->status;
		$binds['message'] = $message;
		
		// Write log record
		$sql = "INSERT INTO `sftp_log` SET ";
		$sql .= "`date` = ?, `pid` = ?, `user` = ?, `link` = ?, `task` = ?, `error` = ?, `status` = ?, `message` = ?";
		sqlInsert($sql, $binds);
	}

}

?>