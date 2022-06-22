<?php

/**
 * CcdaServiceDocumentRequestor handles the communication with the node ccda service in sending and receiving data
 * over the socket.
 *
 * @package openemr
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
        $server_active = @socket_connect($socket, "localhost", "6661");

        if ($server_active === false) {
            // 1 -> Care coordination module, 2-> portal, 3 -> Both so the local service is on if it's greater than 0
            if ($GLOBALS['ccda_alt_service_enable'] > 0) { // we're local service
                $path = $GLOBALS['fileroot'] . "/ccdaservice";
                if (IS_WINDOWS) {
                    // node server is quite with errors(hidden process) so we'll do redirect of tty
                    // to generally Windows/Temp.
                    $redirect_errors = " > " .
                        $system->escapeshellcmd($GLOBALS['temporary_files_dir'] . "/ccdaserver.log") . " 2>&1";
                    $cmd = $system->escapeshellcmd("node " . $path . "/serveccda.js") . $redirect_errors;
                    $pipeHandle = popen("start /B " . $cmd, "r");
                    if ($pipeHandle === false) {
                        throw new CcdaServiceConnectionException("Failed to start local ccdaservice");
                    }
                    if (pclose($pipeHandle) === -1) {
                        error_log("Failed to close pipehandle for ccdaservice");
                    }
                } else {
                    $command = 'nodejs';
                    if (!$system->command_exists($command)) {
                        if ($system->command_exists('node')) {
                            // older or custom Ubuntu systems that have node rather than nodejs command
                            $command = 'node';
                        } else {
                            error_log("Node is not installed on the system.  Connection failed");
                            throw new CcdaServiceConnectionException('Connection Failed.');
                        }
                    }
                    $cmd = $system->escapeshellcmd("$command " . $path . "/serveccda.js");
                    exec($cmd . " > /dev/null &");
                }
                sleep(2); // give cpu a rest
                $result = socket_connect($socket, "localhost", "6661");
                if ($result === false) { // hmm something is amiss with service. user will likely try again.
                    error_log("Failed to start and connect to local ccdaservice server on port 6661");
                    throw new CcdaServiceConnectionException("Connection Failed");
                }
            } else {
                error_log("C-CDA Service is not enabled in Global Settings");
                throw new CcdaServiceConnectionException("Please Enable C-CDA Alternate Service in Global Settings");
            }
        }

        $data = chr(11) . $data . chr(28) . "\r";
        if (strlen($data) > 1024 * 128) {
            throw new CcdaServiceConnectionException("Export document exceeds the maximum size of 128KB");
        }
        // Write to socket!
        if (strlen($data) > 1024 * 64) {
            $data1 = substr($data, 0, floor(strlen($data) / 2));
            $data2 = substr($data, floor(strlen($data) / 2));
            $out = socket_write($socket, $data1, strlen($data1));
            // give distance a chance to clear buffer
            // we could handshake with a little effort
            sleep(1);
            $out = socket_write($socket, $data2, strlen($data2));
        } else {
            $out = socket_write($socket, $data, strlen($data));
        }

        socket_set_nonblock($socket);
        //Read from socket!
        do {
            $line = "";
            $line = trim(socket_read($socket, 1024, PHP_NORMAL_READ));
            $output .= $line;
        } while (!empty($line) && $line !== false);

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
