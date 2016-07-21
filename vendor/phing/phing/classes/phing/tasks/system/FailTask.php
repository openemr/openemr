<?php
/**
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
require_once 'phing/ExitStatusException.php';
require_once 'phing/tasks/system/condition/NestedCondition.php';

/**
 * Exits the active build, giving an additional message
 * if available.
 *
 * @author    Hans Lellelid <hans@xmpl.org> (Phing)
 * @author    Nico Seessle <nico@seessle.de> (Ant)
 *
 * @package   phing.tasks.system
 */
class FailTask extends Task
{
    /** @var string $message */
    protected $message;
    protected $ifCondition;
    protected $unlessCondition;
    protected $nestedCondition;
    protected $status;

    /**
     * A message giving further information on why the build exited.
     *
     * @param string $value message to output
     *
     * @return void
     */
    public function setMsg($value)
    {
        $this->setMessage($value);
    }

    /**
     * A message giving further information on why the build exited.
     *
     * @param string $value message to output
     *
     * @return void
     */
    public function setMessage($value)
    {
        $this->message = $value;
    }

    /**
     * Only fail if a property of the given name exists in the current project.
     *
     * @param $c property name
     *
     * @return void
     */
    public function setIf($c)
    {
        $this->ifCondition = $c;
    }

    /**
     * Only fail if a property of the given name does not
     * exist in the current project.
     *
     * @param $c property name
     *
     * @return void
     */
    public function setUnless($c)
    {
        $this->unlessCondition = $c;
    }

    /**
     * Set the status code to associate with the thrown Exception.
     * @param int $int the <code>int</code> status
     */
    public function setStatus($int)
    {
        $this->status = (int) $int;
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     *
     * @throws BuildException
     */
    public function main()
    {
        if ($this->testIfCondition() && $this->testUnlessCondition()) {
            $text = null;
            if ($this->message !== null && strlen(trim($this->message)) > 0) {
                $text = trim($this->message);
            } else {
                if ($this->ifCondition !== null && $this->ifCondition !== "" && $this->testIfCondition()) {
                    $text = "if=" . $this->ifCondition;
                }
                if ($this->unlessCondition !== null && $this->unlessCondition !== "" && $this->testUnlessCondition()) {
                    if ($text === null) {
                        $text = "";
                    } else {
                        $text .= " and ";
                    }
                    $text .= "unless=" . $this->unlessCondition;
                }
                if ($this->nestedConditionPresent()) {
                    $text = "condition satisfied";
                } else {
                    if ($text === null) {
                        $text = "No message";
                    }
                }
            }

            $this->log("failing due to " . $text, Project::MSG_DEBUG);
            if ($this->status === null) {
                throw new BuildException($text);
            }

            throw new ExitStatusException($text, $this->status);
        }
    }

    /**
     * Add a condition element.
     * @return ConditionBase
     * @throws BuildException
     */
    public function createCondition()
    {
        if ($this->nestedCondition !== null) {
            throw new BuildException("Only one nested condition is allowed.");
        }
        $this->nestedCondition = new NestedCondition();
        return $this->nestedCondition;
    }

    /**
     * Set a multiline message.
     *
     * @param string $msg
     *
     * @return void
     */
    public function addText($msg)
    {
        if ($this->message === null) {
            $this->message = "";
        }
        $this->message .= $this->project->replaceProperties($msg);
    }

    /**
     * @return boolean
     */
    protected function testIfCondition()
    {
        if ($this->ifCondition === null || $this->ifCondition === "") {
            return true;
        }

        return $this->project->getProperty($this->ifCondition) !== null;
    }

    /**
     * @return boolean
     */
    protected function testUnlessCondition()
    {
        if ($this->unlessCondition === null || $this->unlessCondition === "") {
            return true;
        }

        return $this->project->getProperty($this->unlessCondition) === null;
    }

    /**
     * test the nested condition
     * @return bool true if there is none, or it evaluates to true
     * @throws BuildException
     */
    private function testNestedCondition()
    {
        $result = $this->nestedConditionPresent();

        if ($result && $this->ifCondition !== null || $this->unlessCondition !== null) {
            throw new BuildException("Nested conditions not permitted in conjunction with if/unless attributes");
        }

        return $result && $this->nestedCondition->evaluate();
    }

    /**
     * test whether there is a nested condition.
     * @return boolean
     */
    private function nestedConditionPresent()
    {
        return (bool) $this->nestedCondition;
    }
}
