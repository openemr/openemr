<?php

/**
 * CcdaServiceDocumentRequestor handles the communication with the node ccda service in sending and receiving data
 * over the socket.
 *
 * @package   openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Carecoordination\Model;

use Exception;
use OpenEMR\Common\System\System;

class CcdaServiceDocumentRequestor
{
    /**
     * @throws CcdaServiceConnectionException
     */
    public function socket_get($data)
    {
        $output = "";
        $system = new System();

        // Create a TCP Stream Socket
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            throw new CcdaServiceConnectionException("Socket Creation Failed");
        }
        // Let's check if server is already running but suppress warning with @ operator
        $server_active = @socket_connect($socket, "127.0.0.1", "6661");

        if ($server_active === false) {
            // 1 -> Care coordination module, 2-> portal, 3 -> Both so the local service is on if it's greater than 0
            if ($GLOBALS['ccda_alt_service_enable'] > 0) { // we're local service
                $path = $GLOBALS['fileroot'] . "/ccdaservice";
                if (IS_WINDOWS) {
                    // node server is quite with errors(hidden process) so we'll do redirect of tty
                    // to generally Windows/Temp.
                    $nodePath    = 'node';
                    $scriptPath  = ($path . '\\serveccda.js');
                    $logPath     = ($GLOBALS['temporary_files_dir'] . "\\ccdaserver.log");  // redirect logs here if desired
                    $cmd = sprintf(
                        'start /B "%s" "%s" > "%s" 2>&1',
                        $nodePath,
                        $scriptPath,
                        $logPath
                    );
                    $pipeHandle = popen($cmd, 'r');
                    if ($pipeHandle === false) {
                        error_log("Failed to start Node process via popen()");
                    } else {
                        // close the pipe
                        pclose($pipeHandle);
                    }
                } else {
                    $command = 'node';
                    if (!$system->command_exists($command)) {
                        if ($system->command_exists('nodejs')) {
                            // older or custom Ubuntu systems that have nodejs rather than node command
                            $command = 'nodejs';
                        } else {
                            error_log("Node is not installed on the system.  Connection failed");
                            throw new CcdaServiceConnectionException('Connection Failed.');
                        }
                    }
                    $cmd = $system->escapeshellcmd("$command " . $path . "/serveccda.js");
                    exec($cmd . " > /dev/null &");
                }
                sleep(5); // give cpu a rest
                // now try to connect to the server
                $result = socket_connect($socket, "127.0.0.1", (int)6661);
                if ($result === false) {
                    $errorCode = socket_last_error($socket);
                    $errorMsg = socket_strerror($errorCode);
                    error_log("Socket connection error $errorCode: $errorMsg");
                    throw new CcdaServiceConnectionException("Connection Failed: $errorMsg");
                }
            } else {
                error_log("C-CDA Service is not enabled in Global Settings");
                throw new CcdaServiceConnectionException("Please Enable C-CDA Alternate Service in Global Settings");
            }
        }
        // add file separator character for server end of message
        $data = $data . chr(28) . chr(28);
        $len = strlen($data);
        // Set default buffer size to target data array size.
        $good_buf = socket_set_option($socket, SOL_SOCKET, SO_SNDBUF, $len);
        if ($good_buf === false) { // Can't set buffer
            error_log("Failed to set socket buffer to " . $len);
        }
        // make writeSize chunk either the size set above or the default buffer size (64Kb).
        $writeSize = socket_get_option($socket, SOL_SOCKET, SO_SNDBUF);
        $pos = 0;
        $currentCounter = 0;
        $maxLineAttempts = ($len / $writeSize) + 1;
        do {
            $line = substr($data, $pos, min($writeSize, $len - $pos));
            $out = socket_write($socket, $line, strlen($line));
            if ($out !== false) {
                $pos += $out; // bytes written lets advance our position
            } else {
                break;
            }
            // pause for the receiving side
            usleep(200000);
        } while ($out !== false && $pos < $len && $currentCounter++ <= $maxLineAttempts);

        socket_set_nonblock($socket);
        //Read back rendered document from node service!
        do {
            $line = "";
            $line = trim(socket_read($socket, 1024, PHP_NORMAL_READ));
            $output .= $line;
        } while (!empty($line));

        $output = substr(trim($output), 0, strlen($output) - 1);
        // Close and return.
        socket_close($socket);
        if ($output == "Authentication Failure") {
            throw new CcdaServiceConnectionException("Authentication Failure");
        }
        if (empty(trim($output))) {
            throw new CcdaServiceConnectionException("Ccda document generated was empty.  Check node service logs.");
        }
        return $output;
    }
}
