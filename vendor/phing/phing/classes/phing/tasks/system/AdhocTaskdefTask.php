<?php

/*
 * $Id: c0cdcd2c07fd4b81ac8f1b7cbc50a4031b3d5ddd $
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

require_once 'phing/tasks/system/AdhocTask.php';

/**
 * A class for creating adhoc tasks in build file.
 *
 * <target name="test-adhoc">
 *        <adhoc-task name="foo"><![CDATA[
 *
 *            class FooTest extends Task {
 *                private $bar;
 *
 *                function setBar($bar) {
 *                    $this->bar = $bar;
 *                }
 *
 *                function main() {
 *                    $this->log("In FooTest: " . $this->bar);
 *                }
 *            }
 *
 *        ]]></adhoc-task>
 *
 *      <foo bar="B.L.I.N.G"/>
 * </target>
 *
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Id: c0cdcd2c07fd4b81ac8f1b7cbc50a4031b3d5ddd $
 * @package   phing.tasks.system
 */
class AdhocTaskdefTask extends AdhocTask
{

    /**
     * The tag that refers to this task.
     */
    private $name;

    /**
     * Set the tag that will represent this adhoc task/type.
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /** Main entry point */
    public function main()
    {
        if ($this->name === null) {
            throw new BuildException("The name attribute is required for adhoc task definition.", $this->location);
        }

        $taskdefs = $this->getProject()->getTaskDefinitions();

        if (!isset($taskdefs[$this->name])) {
            $this->execute();

            $classes = $this->getNewClasses();

            if (count($classes) < 1) {
                throw new BuildException("You must define at least one class for AdhocTaskdefTask.");
            }

            $classname = array_pop($classes);

            // instantiate it to make sure it is an instance of Task
            $t = new $classname();
            if (!($t instanceof Task)) {
                throw new BuildException("The adhoc class you defined must be an instance of phing.Task", $this->location);
            }

            $this->log("Task " . $this->name . " will be handled by class " . $classname, Project::MSG_VERBOSE);
            $this->project->addTaskDefinition($this->name, $classname);
        }
    }
}
