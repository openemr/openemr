<?php
/**
 *  $Id: 8eb3563703d50b210a90b2847d7446698b4e9c43 $
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

include_once 'phing/Task.php';
require_once 'phing/tasks/system/SequentialTask.php';

/**
 * Task definition for the phing task to switch on a particular value.
 *
 * Task calling syntax:
 * ```
 * <switch value="value" [caseinsensitive="true|false"] >
 *   <case value="val">
 *     <property name="propname" value="propvalue" /> |
 *     <phingcall target="targetname" /> |
 *     any other tasks
 *   </case>
 *   [
 *   <default>
 *     <property name="propname" value="propvalue" /> |
 *     <phingcall target="targetname" /> |
 *     any other tasks
 *   </default>
 *   ]
 * </switch>
 * ```
 *
 *
 * Attributes:
 * value           -> The value to switch on
 * caseinsensitive -> Should we do case insensitive comparisons?
 *                    (default is false)
 *
 * Subitems:
 * case     --> An individual case to consider, if the value that
 *              is being switched on matches to value attribute of
 *              the case, then the nested tasks will be executed.
 * default  --> The default case for when no match is found.
 *
 *
 * Crude Example:
 *
 * ```
 * <switch value="${foo}">
 *     <case value="bar">
 *       <echo message="The value of property foo is bar" />
 *     </case>
 *     <case value="baz">
 *       <echo message="The value of property foo is baz" />
 *     </case>
 *     <default>
 *       <echo message="The value of property foo is not sensible" />
 *     </default>
 * </switch>
 * ```
 *
 * @author Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class SwitchTask extends Task
{
    /** @var mixed $value */
    private $value = null;

    /** @var array $cases */
    private $cases = array();

    /** @var SequentialTask $defaultCase */
    private $defaultCase = null;

    /** @var bool $caseInsensitive */
    private $caseInsensitive = false;

    /***
     * Sets the value being switched on.
     *
     * @param mixed $value
     *
     * @return void
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Adds a CaseTask.
     *
     * @param CaseTask $case
     *
     * @return void
     */
    public function addCase(CaseTask $case)
    {
        $this->cases[] = $case;
    }

    /**
     * @param $bool
     *
     * @return void
     */
    public function setCaseInsensitive($bool)
    {
        $this->caseInsensitive = $bool;
    }

    /**
     * Creates the `<default>` tag
     */
    public function addDefault(SequentialTask $res)
    {
        if ($this->defaultCase !== null) {
            throw new BuildException("Cannot specify multiple default cases");
        }

        $this->defaultCase = $res;
    }

    public function main()
    {
        if ($this->value === null) {
            throw new BuildException("Value is missing <switch>");
        }

        if (empty($this->cases) && $this->defaultCase === null) {
            throw new BuildException("No cases supplied <switch>");
        }

        $selectedCase = $this->defaultCase;

        /** @var CaseTask $case */
        foreach ($this->cases as $case) {
            $cValue = $case->getValue();

            if (empty($case)) {
                throw new BuildException("Value is required for case.");
            }

            $mValue = $this->value;
            if ($this->caseInsensitive) {
                $cValue = strtoupper($case->getValue());
                $mValue = strtoupper($this->value);
            }

            if ($cValue === $mValue && $case != $this->defaultCase) {
                $selectedCase = $case;
            }
        }

        if ($selectedCase === null) {
            throw new BuildException("No case matched the value " . $this->value . " and no default has been specified.");
        }

        $selectedCase->perform();
    }
}

/**
 * "Inner" class for SwitchTask.
 *
 * @package phing.tasks.system
 */
class CaseTask extends SequentialTask
{
    /** @var mixed $value */
    private $value = null;

    /**
     * @param $value
     *
     * @return void
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function main()
    {
        /** @var Task $task */
        foreach ($this->nestedTasks as $task) {
            $task->perform();
        }
    }
}
