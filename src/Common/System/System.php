<?php

namespace OpenEMR\Common\System;

/**
 * Handles any commands dealing with the operating system.
 */
class System
{
    /**
     * Determines if a system command exists on the current environment
     * It will check on windows or on unix based systems to see if a command exists.
     *
     * @see https://stackoverflow.com/a/18540185/7884612
     *
     * @param string $command The command to check
     * @return bool True if the command has been found ; otherwise, false.
     */
    public function command_exists($command)
    {
        $exec_command = escapeshellcmd($command); // @see  https://www.php.net/manual/en/function.escapeshellcmd.php
        $whereIsCommand = (PHP_OS == 'WINNT') ? 'where' : 'which';

        $process = proc_open(
            "$whereIsCommand $exec_command",
            array(
            0 => array("pipe", "r"), //STDIN
            1 => array("pipe", "w"), //STDOUT
            2 => array("pipe", "w"), //STDERR
            ),
            $pipes
        );
        if ($process !== false) {
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);

            return $stdout != '';
        }

        return false;
    }

    /**
     * Start a process running in the background using the command parameter.
     *
     * Returns the process ID if successful, false otherwise.
     *
     * @param $command
     * @return false|mixed
     */
    public function run_node_background_process($command)
    {
        $cmd = $this->escapeshellcmd($command);
        $phandle = false;
        if (IS_WINDOWS) {
            $redirect_errors = " > " .
                $this->escapeshellcmd($GLOBALS['temporary_files_dir'] .
                    "/oe-system-start_server.log") . " 2>&1";
            $cmd = $cmd . $redirect_errors;
            $phandle = popen("start /B " . $cmd, "r");
            if ($phandle === false) {
                error_log("Failed to start local CQM");
            }
            if (pclose($phandle) === -1) {
                error_log("Failed to close pipe handle for CQM");
            }
            sleep(2); // need this on windows!
            return $phandle;
        }

        exec($this->escapeshellcmd($cmd) . ' > /dev/null &');

        return 1;
    }

    /**
     * Cleans up the system command by escaping any arguments or injections that could be inserted here
     * @param string $command a command or command string that needs to be sanitized.
     * @return string The cleaned up string.
     */
    public function escapeshellcmd($command)
    {
        // for now we just pass through to the php cleanup, any specifics we can add after
        return escapeshellcmd($command);
    }
}
