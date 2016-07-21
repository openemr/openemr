<?php

/*
 *  $Id: e2d3229326c8a6b49e75ef4e9bf892816e26e350 $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

require_once "phing/Task.php";
require_once dirname(__FILE__) . "/Arg.php";

/**
 * Symfony Console Task
 * @author nuno costa <nuno@francodacosta.com>
 * @license GPL
 * @version $Id: e2d3229326c8a6b49e75ef4e9bf892816e26e350 $
 * @package phing.tasks.ext.symfony
 */
class SymfonyConsoleTask extends Task
{

    /**
     *
     * @var Arg[] a collection of Arg objects
     */
    private $args = array();

    /**
     *
     * @var string the Symfony console command to execute
     */
    private $command = null;

    /**
     *
     * @var string path to symfony console application
     */
    private $console = 'app/console';

    /**
     *
     * @var string property to be set
     */
    private $propertyName = null;

    /**
     * Whether to check the return code.
     * @var boolean
     */
    private $checkreturn = false;

    /**
     * Is the symfony cli debug mode set? (true by default)
     * @var boolean
     */
    private $debug = true;
    
    /**
     * sets the symfony console command to execute
     * @param string $command
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * return the symfony console command to execute
     * @return String
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * sets the path to symfony console application
     * @param string $console
     */
    public function setConsole($console)
    {
        $this->console = $console;
    }

    /**
     * returns the path to symfony console application
     * @return string
     */
    public function getConsole()
    {
        return $this->console;
    }

    /**
     * Set the name of the property to store the application output in
     * @param $property
     * @return void
     */
    public function setPropertyName($property)
    {
        $this->propertyName = $property;
    }

    /**
     * Whether to check the return code.
     *
     * @param boolean $checkreturn If the return code shall be checked
     *
     * @return void
     */
    public function setCheckreturn($checkreturn)
    {
        $this->checkreturn = (bool) $checkreturn;
    }

    
    /**
     * Whether to set the symfony cli debug mode
     *
     * @param boolean $debug If the symfony cli debug mode is set
     *
     * @return void
     */
    public function setDebug($debug)
    {
        $this->debug = (bool) $debug;
    }

    /**
     * Get if the symfony cli debug mode is set
     * @return boolean
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * appends an arg tag to the arguments stack
     *
     * @return Arg Argument object
     */

    public function createArg()
    {
        $num = array_push($this->args, new Arg());

        return $this->args[$num - 1];
    }

    /**
     * return the argumments passed to this task
     * @return array of Arg()
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Check if the no-debug option was added via args
     * @return boolean
     */
    private function isNoDebugArgPresent()
    {
        foreach($this->args as $arg) {
            if ($arg->getName() == "no-debug") {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Gets the command string to be executed
     * @return string
     */
    public function getCmdString()
    {
        // Add no-debug arg if it isn't already present
        if (!$this->debug && !$this->isNoDebugArgPresent()) {
            $this->createArg()->setName("no-debug");
        }
        $cmd = array(
            Commandline::quoteArgument($this->console),
            $this->command,
            implode(' ', $this->args)
        );
        $cmd = implode(' ', $cmd);

        return $cmd;
    }

    /**
     * executes the synfony console application
     */
    public function main()
    {
        $cmd = $this->getCmdString();

        $this->log("executing $cmd");
        $return = null;
        $output = array();
        exec($cmd, $output, $return);

        $lines = implode("\r\n", $output);

        $this->log($lines, Project::MSG_INFO);

        if ($this->propertyName != null) {
            $this->project->setProperty($this->propertyName, $lines);
        }

        if ($return != 0 && $this->checkreturn) {
            $this->log('Task exited with code: ' . $return, Project::MSG_ERR);
            throw new BuildException("SymfonyConsole execution failed");
        }
    }
}
