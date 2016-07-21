<?php
/**
 *  Patches a file by applying a 'diff' file to it
 *
 *  Requires "patch" to be on the execution path.
 *
 *  Based on Apache Ant PatchTask:
 *
 *  Licensed to the Apache Software Foundation (ASF) under one or more
 *  contributor license agreements.  See the NOTICE file distributed with
 *  this work for additional information regarding copyright ownership.
 *  The ASF licenses this file to You under the Apache License, Version 2.0
 *  (the "License"); you may not use this file except in compliance with
 *  the License.  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

require_once 'phing/Task.php';

/**
 * Patches a file by applying a 'diff' file to it
 *
 * Requires "patch" to be on the execution path.
 *
 * @package phing.tasks.ext
 */
class PatchTask extends Task
{
    /**
     * Base command to be executed (must end with a space character!)
     * @var string
     */
    const CMD = 'patch --batch --silent ';

    /**
     * File to be patched
     * @var string
     */
    private $originalFile;

    /**
     * Patch file
     *
     * @var string
     */
    private $patchFile;

    /**
     * Value for a "-p" option
     * @var int
     */
    private $strip;

    /**
     * Command line arguments for patch binary
     * @var array
     */
    private $cmdArgs = array();

    /**
     * Halt on error return value from patch invocation.
     * @var bool
     */
    private $haltOnFailure = false;

    /**
     * The file containing the diff output
     *
     * Required.
     *
     * @param  string         $file File containing the diff output
     * @return void
     * @throws BuildException if $file not exists
     */
    public function setPatchFile($file)
    {
        if (!is_file($file)) {
            throw new BuildException(sprintf('Patchfile %s doesn\'t exist', $file));
        }
        $this->patchFile = $file;
    }

    /**
     * The file to patch
     *
     * Optional if it can be inferred from the diff file.
     *
     * @param  string $file File to patch
     * @return void
     */
    public function setOriginalFile($file)
    {
        $this->originalFile = $file;
    }

    /**
     * The name of a file to send the output to, instead of patching
     * the file(s) in place
     *
     * Optional.
     *
     * @param  string $file File to send the output to
     * @return void
     */
    public function setDestFile($file)
    {
        if ($file !== null) {
            $this->cmdArgs [] = "--output=$file";
        }
    }

    /**
     * Flag to create backups
     *
     * Optional, default - false
     *
     * @param  bool $backups If true create backups
     * @return void
     */
    public function setBackups($backups)
    {
        if ($backups) {
            $this->cmdArgs [] = '--backup';
        }
    }

    /**
     * Flag to ignore whitespace differences;
     *
     * Default - false
     *
     * @param  bool $ignore If true ignore whitespace differences
     * @return void
     */
    public function setIgnoreWhiteSpace($ignore)
    {
        if ($ignore) {
            $this->cmdArgs [] = '--ignore-whitespace';
        }
    }

    /**
     * Strip the smallest prefix containing <i>num</i> leading slashes
     * from filenames.
     *
     * patch's <i>--strip</i> option.
     *
     * @param  int            $num number of lines to strip
     * @return void
     * @throws BuildException if num is < 0, or other errors
     */
    public function setStrip($num)
    {
        if ($num < 0) {
            throw new BuildException('strip has to be >= 0');
        }

        $this->strip = $num;
    }

    /**
     * Work silently unless an error occurs
     *
     * Optional, default - false
     * @param  bool $flag If true suppress set the -s option on the patch command
     * @return void
     */
    public function setQuiet($flag)
    {
        if ($flag) {
            $this->cmdArgs [] = '--silent';
        }
    }

    /**
     * Assume patch was created with old and new files swapped
     *
     * Optional, default - false
     *
     * @param  bool $flag If true set the -R option on the patch command
     * @return void
     */
    public function setReverse($flag)
    {
        if ($flag) {
            $this->cmdArgs [] = '--reverse';
        }
    }

    /**
     * The directory to run the patch command in
     *
     * Defaults to the project's base directory.
     *
     * @param  string $directory Directory to run the patch command in
     * @return void
     */
    public function setDir($directory)
    {
        $this->cmdArgs [] = "--directory=$directory";
    }

    /**
     * Ignore patches that seem to be reversed or already applied
     *
     * @param  bool $flag If true set the -N (--forward) option
     * @return void
     */
    public function setForward($flag)
    {
        if ($flag) {
            $this->cmdArgs [] = "--forward";
        }
    }

    /**
     * Set the maximum fuzz factor
     *
     * Defaults to 0
     *
     * @param  string $value Value of a fuzz factor
     * @return void
     */
    public function setFuzz($value)
    {
        $this->cmdArgs [] = "--fuzz=$value";
    }

    /**
     * If true, stop the build process if the patch command
     * exits with an error status.
     *
     * The default is "false"
     *
     * @param  bool $value "true" if it should halt, otherwise "false"
     * @return void
     */
    public function setHaltOnFailure($value)
    {
        $this->haltOnFailure = $value;
    }

    /**
     * Main task method
     *
     * @return void
     * @throws BuildException when it all goes a bit pear shaped
     */
    public function main()
    {
        if ($this->patchFile == null) {
            throw new BuildException('patchfile argument is required');
        }

        // Define patch file
        $this->cmdArgs [] = '-i ' . $this->patchFile;
        // Define strip factor
        if ($this->strip != null) {
            $this->cmdArgs [] = '--strip=' . $this->strip;
        }
        // Define original file if specified
        if ($this->originalFile != null) {
            $this->cmdArgs [] = $this->originalFile;
        }

        $cmd = self::CMD . implode(' ', $this->cmdArgs);

        $this->log('Applying patch: ' . $this->patchFile);

        exec($cmd, $output, $exitCode);

        foreach ($output as $line) {
            $this->log($line, Project::MSG_VERBOSE);
        }

        if ($exitCode != 0 && $this->haltOnFailure) {
            throw new BuildException("Task exited with code $exitCode");
        }

    }
}
