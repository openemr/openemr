<?php
/*
 *  $Id: de25faf5f70bf92026b2d934db52a6a05d8dce5e $
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

/**
 * Deprecated task that uses console to prompt user for property values.
 *
 * This class is very slightly simpler than the InputTask, but lacks the ability
 * to use a non-console input handler.  You should, therefore, use InputTask.  This
 * class can serve as a reference, but will be removed in the future.
 *
 * @author    Hans Lellelid <hans@xmpl.org> (Phing)
 * @author    Anthony J. Young-Garner <ajyoung@alum.mit.edu> (Ant)
 * @version   $Id: de25faf5f70bf92026b2d934db52a6a05d8dce5e $
 * @package   phing.tasks.system
 * @deprecated - in favor of the more capable InputTask
 */
class PropertyPromptTask extends Task
{

    /**
     * The property name to set with the output.
     * @var string
     */
    private $propertyName; // required

    /**
     * The default value to use if no input is entered.
     * @var string
     */
    private $defaultValue;

    /**
     * The entered value.
     * @var string
     */
    private $proposedValue;

    /**
     * The text to use for the prompt.
     * @var string
     */
    private $promptText;

    /**
     * The character to put after the text.
     * @var string
     */
    private $promptCharacter;

    /**
     *
     */
    private $useExistingValue;

    /**
     * Run the PropertyPrompt task.
     * @throws BuildException
     */
    public function main()
    {

        $this->proposedValue = $this->project->getProperty($this->propertyName);
        $currentValue = $this->defaultValue;

        if ($currentValue == "" && $this->proposedValue !== null) {
            $currentValue = $this->proposedValue;
        }

        if ($this->useExistingValue !== true || $this->proposedValue === null) {

            $this->log(
                "Prompting user for " . $this->propertyName . ". " . $this->getDefaultMessage(),
                Project::MSG_VERBOSE
            );

            $promptText = "\n" . $this->promptText . " [" . $currentValue . "] " . $this->promptCharacter . " ";

            try {
                $request = new InputRequest($promptText);
                $this->project->getInputHandler()->handleInput($request);
                $this->proposedValue = $request->getInput();
            } catch (IOException $e) {
                $this->log("Prompt failed. Using default. (Failure reason: " . $e->getMessage() . ")");
                $this->proposedValue = $this->defaultValue;
            }

            if ($this->proposedValue === "") {
                $this->log("No value specified, using default.", Project::MSG_VERBOSE);
                $this->proposedValue = $this->defaultValue;
            }

            if (isset($this->proposedValue)) {
                $this->project->setProperty($this->propertyName, $this->proposedValue);
            }

        }
    }

    /**
     * Returns a string to be inserted in the log message
     * indicating whether a default response was specified
     * in the build file.
     */
    private function getDefaultMessage()
    {
        if ($this->defaultValue == "") {
            return "No default response specified.";
        } else {
            return "Default response is " . $this->defaultValue . ".";
        }
    }

    /**
     * Returns defaultValue specified
     * in this task for the Property
     * being set.
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Returns the terminating character used to
     * punctuate the prompt text.
     * @return string
     */
    public function getPromptCharacter()
    {
        return $this->promptCharacter;
    }

    /**
     * Returns text of the prompt.
     * @return java.lang.String
     */
    public function getPromptText()
    {
        return $this->promptText;
    }

    /**
     * Returns name of the Ant Project Property
     * being set by this task.
     * @return string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * Initializes this task.
     */
    public function init()
    {
        parent::init();
        $this->defaultValue = "";
        $this->promptCharacter = "?";
        $this->useExistingValue = false;
    }

    /**
     * Insert the method's description here.
     * Creation date: (12/10/2001 8:16:16 AM)
     * @return boolean
     */
    public function isUseExistingValue()
    {
        return $this->useExistingValue;
    }

    /**
     * Sets defaultValue for the Property
     * being set by this task.
     * @param string $newDefaultvalue
     */
    public function setDefaultvalue($newDefaultvalue)
    {
        $this->defaultValue = $newDefaultvalue;
    }

    /**
     * Sets the terminating character used to
     * punctuate the prompt text (default is "?").
     * @param string $newPromptcharacter
     */
    public function setPromptCharacter($newPromptcharacter)
    {
        $this->promptCharacter = $newPromptcharacter;
    }

    /**
     * Sets text of the prompt.
     * @param string $newPrompttext
     */
    public function setPromptText($newPrompttext)
    {
        $this->promptText = $newPrompttext;
    }

    /**
     * Specifies the Phing Project Property
     * being set by this task.
     * @param java $newPropertyname
     * @internal param java $newPropertyname .lang.String
     */
    public function setPropertyName($newPropertyname)
    {
        $this->propertyName = $newPropertyname;
    }

    /**
     *
     * @param boolean $newUseExistingValue
     */
    public function setUseExistingValue($newUseExistingValue)
    {
        $this->useExistingValue = $newUseExistingValue;
    }

    /**
     * Sets the prompt text that will be presented to the user.
     * @param  string $prompt
     * @return void
     */
    public function addText($prompt)
    {
        $this->setPromptText($prompt);
    }

}
