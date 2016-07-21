<?php
/*
 *  $Id: 5238ec4e1da8704e3062a4c29d6e680afbe5efb0 $
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
 * Wrapper class that holds all information necessary to create a task
 * that did not exist when Phing started.
 *
 * <em> This has something to do with phing encountering an task XML element
 * it is not aware of at start time. This is a situation where special steps
 * need to be taken so that the element is then known.</em>
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Id: 5238ec4e1da8704e3062a4c29d6e680afbe5efb0 $
 * @package   phing
 */
class UnknownElement extends Task
{

    private $elementName;
    private $realThing;
    private $children = array();

    /**
     * Constructs a UnknownElement object
     *
     * @param    string  The XML element name that is unknown
     */
    public function __construct($elementName)
    {
        $this->elementName = (string) $elementName;
    }

    /**
     * Return the XML element name that this <code>UnnownElement</code>
     * handles.
     *
     * @return string The XML element name that is unknown
     */
    public function getTag()
    {
        return (string) $this->elementName;
    }

    /**
     * Tries to configure the unknown element
     *
     * @throws BuildException if the element can not be configured
     */
    public function maybeConfigure()
    {

        $this->realThing = $this->makeObject($this, $this->wrapper);
        $this->wrapper->setProxy($this->realThing);
        if ($this->realThing instanceof Task) {
            $this->realThing->setRuntimeConfigurableWrapper($this->wrapper);
            $this->realThing->maybeConfigure();
        } else {
            $this->wrapper->maybeConfigure($this->getProject());
        }
        $this->handleChildren($this->realThing, $this->wrapper);

    }

    /**
     * Called when the real task has been configured for the first time.
     *
     * @throws BuildException if the task can not be created
     */
    public function main()
    {

        if ($this->realThing === null) {
            // plain impossible to get here, maybeConfigure should
            // have thrown an exception.
            throw new BuildException("Should not be executing UnknownElement::main() -- task/type: {$this->elementName}");
        }

        if ($this->realThing instanceof Task) {
            $this->realThing->main();
        }

    }

    /**
     * Add a child element to the unknown element
     *
     * @param UnknownElement $child
     * @internal param The $object object representing the child element
     */
    public function addChild(UnknownElement $child)
    {
        $this->children[] = $child;
    }

    /**
     *  Handle child elemets of the unknown element, if any.
     *
     * @param object $parent        The parent object the unknown element belongs to
     * @param object $parentWrapper The parent wrapper object
     */
    public function handleChildren($parent, $parentWrapper)
    {

        if ($parent instanceof TaskAdapter) {
            $parent = $parent->getProxy();
        }

        $parentClass = get_class($parent);
        $ih = IntrospectionHelper::getHelper($parentClass);

        for ($i = 0, $childrenCount = count($this->children); $i < $childrenCount; $i++) {

            $childWrapper = $parentWrapper->getChild($i);
            $child = $this->children[$i];

            $realChild = null;
            if ($parent instanceof TaskContainer) {
                $parent->addTask($child);
                continue;
            }

            $project = $this->project === null ? $parent->project : $this->project;
            $realChild = $ih->createElement($project, $parent, $child->getTag());

            $childWrapper->setProxy($realChild);
            if ($realChild instanceof Task) {
                $realChild->setRuntimeConfigurableWrapper($childWrapper);
            }

            $childWrapper->maybeConfigure($this->project);
            $child->handleChildren($realChild, $childWrapper);
        }
    }

    /**
     * @param IntrospectionHelper $ih
     * @param $parent
     * @param UnknownElement $child
     * @param RuntimeConfigurable $childWrapper
     * @return bool
     */
    public function handleChild(
        IntrospectionHelper $ih,
        $parent,
        UnknownElement $child,
        RuntimeConfigurable $childWrapper
    ) {
        $childWrapper->setProxy($realChild);
        if ($realChild instanceof Task) {
            $realChild->setRuntimeConfigurableWrapper($childWrapper);
        }

        $childWrapper->maybeConfigure($this->project);
        $child->handleChildren($realChild, $childWrapper);

        return true;
    }

    /**
     * Creates a named task or data type. If the real object is a task,
     * it is configured up to the init() stage.
     *
     * @param  UnknownElement $ue The unknown element to create the real object for.
     *                                 Must not be <code>null</code>.
     * @param  RuntimeConfigurable $w Ignored in this implementation.
     * @throws BuildException
     * @return object              The Task or DataType represented by the given unknown element.
     */
    protected function makeObject(UnknownElement $ue, RuntimeConfigurable $w)
    {
        $o = $this->makeTask($ue, $w, true);
        if ($o === null) {
            $o = $this->project->createDataType($ue->getTag());
        }
        if ($o === null) {
            throw new BuildException("Could not create task/type: '" . $ue->getTag(
                ) . "'. Make sure that this class has been declared using taskdef / typedef.");
        }

        return $o;
    }

    /**
     *  Create a named task and configure it up to the init() stage.
     *
     * @param  UnknownElement $ue The unknwon element to create a task from
     * @param  RuntimeConfigurable $w The wrapper object
     * @param  boolean $onTopLevel Whether to treat this task as if it is top-level.
     * @throws BuildException
     * @return Task                The freshly created task
     */
    protected function makeTask(UnknownElement $ue, RuntimeConfigurable $w, $onTopLevel = false)
    {

        $task = $this->project->createTask($ue->getTag());

        if ($task === null) {
            if (!$onTopLevel) {
                throw new BuildException("Could not create task of type: '" . $this->elementName . "'. Make sure that this class has been declared using taskdef.");
            }

            return null;
        }

        // used to set the location within the xmlfile so that exceptions can
        // give detailed messages

        $task->setLocation($this->getLocation());
        $attrs = $w->getAttributes();
        if (isset($attrs['id'])) {
            $this->project->addReference($attrs['id'], $task);
        }

        if ($this->target !== null) {
            $task->setOwningTarget($this->target);
        }

        $task->init();

        return $task;
    }

    /**
     *  Get the name of the task to use in logging messages.
     *
     * @return string The task's name
     */
    public function getTaskName()
    {
        return $this->realThing === null ? parent::getTaskName() : $this->realThing->getTaskName();
    }
}
