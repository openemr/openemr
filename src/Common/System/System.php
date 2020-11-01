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
