<?php
/*
 *  $Id: 6bf80f75d0676da89348d0bf5682995079e67270 $
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

require_once 'phing/Task.php';
include_once 'phing/input/InputRequest.php';
include_once 'phing/input/YesNoInputRequest.php';
include_once 'phing/input/MultipleChoiceInputRequest.php';

/**
 * Reads input from the InputHandler.
 *
 * @see       Project::getInputHandler()
 * @author    Hans Lellelid <hans@xmpl.org> (Phing)
 * @author    Ulrich Schmidt <usch@usch.net> (Ant)
 * @author    Stefan Bodewig <stefan.bodewig@epost.de> (Ant)
 * @version   $Id: 6bf80f75d0676da89348d0bf5682995079e67270 $
 * @package   phing.tasks.system
 */
class InputTask extends Task
{

    private $validargs;
    private $message = ""; // required
    private $propertyName; // required
    private $defaultValue;
    private $promptChar;

    /**
     * Defines valid input parameters as comma separated strings. If set, input
     * task will reject any input not defined as accepted and requires the user
     * to reenter it. Validargs are case sensitive. If you want 'a' and 'A' to
     * be accepted you need to define both values as accepted arguments.
     *
     * @param string $validargs A comma separated String defining valid input args.
     */
    public function setValidargs($validargs)
    {
        $this->validargs = $validargs;
    }

    /**
     * Defines the name of a property to be set from input.
     *
     * @param string $name Name for the property to be set from input
     */
    public function setPropertyName($name)
    {
        $this->propertyName = $name;
    }

    /**
     * Sets the Message which gets displayed to the user during the build run.
     * @param The $message
     * @internal param The $message message to be displayed.
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Set a multiline message.
     * @param $msg
     */
    public function addText($msg)
    {
        $this->message .= $this->project->replaceProperties($msg);
    }

    /**
     * Add a default value.
     * @param string $v
     */
    public function setDefaultValue($v)
    {
        $this->defaultValue = $v;
    }

    /**
     * Set the character/string to use for the prompt.
     * @param string $c
     */
    public function setPromptChar($c)
    {
        $this->promptChar = $c;
    }

    /**
     * Actual method executed by phing.
     * @throws BuildException
     */
    public function main()
    {

        if ($this->propertyName === null) {
            throw new BuildException("You must specify a value for propertyName attribute.");
        }

        if ($this->message === "") {
            throw new BuildException("You must specify a message for input task.");
        }

        if ($this->validargs !== null) {
            $accept = preg_split('/[\s,]+/', $this->validargs);

            // is it a boolean (yes/no) inputrequest?
            $yesno = false;
            if (count($accept) == 2) {
                $yesno = true;
                foreach ($accept as $ans) {
                    if (!StringHelper::isBoolean($ans)) {
                        $yesno = false;
                        break;
                    }
                }
            }
            if ($yesno) {
                $request = new YesNoInputRequest($this->message, $accept);
            } else {
                $request = new MultipleChoiceInputRequest($this->message, $accept);
            }
        } else {
            $request = new InputRequest($this->message);
        }

        // default default is curr prop value
        $request->setDefaultValue($this->project->getProperty($this->propertyName));

        $request->setPromptChar($this->promptChar);

        // unless overridden...
        if ($this->defaultValue !== null) {
            $request->setDefaultValue($this->defaultValue);
        }

        $this->project->getInputHandler()->handleInput($request);

        $value = $request->getInput();

        if ($value !== null) {
            $this->project->setUserProperty($this->propertyName, $value);
        }
    }

}
