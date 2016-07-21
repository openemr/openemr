<?php
/*
 *  $Id: 98f1ceac9bbf413fc3986dfaf4fc7a63f8217b70 $
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

/**
 * Call another target in the same project.
 *
 * <samp>
 *    <target name="foo">
 *      <phingcall target="bar">
 *        <property name="property1" value="aaaaa" />
 *        <property name="foo" value="baz" />
 *       </phingcall>
 *    </target>
 *
 *    <target name="bar" depends="init">
 *      <echo message="prop is ${property1} ${foo}" />
 *    </target>
 * </samp>
 *
 * This only works as expected if neither property1 nor foo are defined in the project itself.
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright 2001,2002 THYRELL. All rights reserved
 * @version   $Id: 98f1ceac9bbf413fc3986dfaf4fc7a63f8217b70 $
 * @package   phing.tasks.system
 */
class PhingCallTask extends Task
{

    /**
     * The called Phing task.
     *
     * @var PhingTask
     */
    private $callee;

    /**
     * The target to call.
     *
     * @var string
     */
    private $subTarget;

    /**
     * Whether to inherit all properties from current project.
     *
     * @var boolean
     */
    private $inheritAll = true;

    /**
     * Whether to inherit refs from current project.
     *
     * @var boolean
     */
    private $inheritRefs = false;

    /**
     *  If true, pass all properties to the new Phing project.
     *  Defaults to true. Future use.
     * @param boolean new value
     */
    public function setInheritAll($inherit)
    {
        $this->inheritAll = (boolean) $inherit;
    }

    /**
     *  If true, pass all references to the new Phing project.
     *  Defaults to false. Future use.
     *
     * @param boolean new value
     */
    public function setInheritRefs($inheritRefs)
    {
        $this->inheritRefs = (boolean) $inheritRefs;
    }

    /**
     * Alias for createProperty
     * @see createProperty()
     */
    public function createParam()
    {
        if ($this->callee === null) {
            $this->init();
        }

        return $this->callee->createProperty();
    }

    /**
     * Property to pass to the invoked target.
     */
    public function createProperty()
    {
        if ($this->callee === null) {
            $this->init();
        }

        return $this->callee->createProperty();
    }

    /**
     * Target to execute, required.
     * @param $target
     */
    public function setTarget($target)
    {
        $this->subTarget = (string) $target;
    }

    /**
     *  init this task by creating new instance of the phing task and
     *  configuring it's by calling its own init method.
     */
    public function init()
    {
        $this->callee = $this->project->createTask("phing");
        $this->callee->setOwningTarget($this->getOwningTarget());
        $this->callee->setTaskName($this->getTaskName());
        $this->callee->setHaltOnFailure(true);
        $this->callee->setLocation($this->getLocation());
        $this->callee->init();
    }

    /**
     *  hand off the work to the phing task of ours, after setting it up
     * @throws BuildException on validation failure or if the target didn't
     *                        execute
     */
    public function main()
    {

        $this->log("Running PhingCallTask for target '" . $this->subTarget . "'", Project::MSG_DEBUG);
        if ($this->callee === null) {
            $this->init();
        }

        if ($this->subTarget === null) {
            throw new BuildException("Attribute target is required.", $this->getLocation());
        }

        $this->callee->setPhingfile($this->project->getProperty("phing.file"));
        $this->callee->setTarget($this->subTarget);
        $this->callee->setInheritAll($this->inheritAll);
        $this->callee->setInheritRefs($this->inheritRefs);
        $this->callee->main();
    }

}
