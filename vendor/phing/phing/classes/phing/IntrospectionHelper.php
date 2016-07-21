<?php

/*
 *  $Id: 9cb68248121b72e0921582078ba48eb02f994006 $
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

include_once 'phing/types/Reference.php';
include_once 'phing/types/Path.php';
include_once 'phing/util/StringHelper.php';
include_once 'phing/parser/CustomChildCreator.php';

/**
 * Helper class that collects the methods that a task or nested element
 * holds to set attributes, create nested elements or hold PCDATA
 * elements.
 *
 *<ul>
 * <li><strong>SMART-UP INLINE DOCS</strong></li>
 * <li><strong>POLISH-UP THIS CLASS</strong></li>
 *</ul>
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @author    Hans Lellelid <hans@xmpl.org>
 * @copyright 2001,2002 THYRELL. All rights reserved
 * @version   $Id: 9cb68248121b72e0921582078ba48eb02f994006 $
 * @package   phing
 */
class IntrospectionHelper
{

    /**
     * Holds the attribute setter methods.
     *
     * @var array string[]
     */
    private $attributeSetters = array();

    /**
     * Holds methods to create nested elements.
     *
     * @var array string[]
     */
    private $nestedCreators = array();

    /**
     * Holds methods to store configured nested elements.
     *
     * @var array string[]
     */
    private $nestedStorers = array();

    /**
     * Map from attribute names to nested types.
     */
    private $nestedTypes = array();

    /**
     * New idea in phing: any class can register certain
     * keys -- e.g. "task.current_file" -- which can be used in
     * task attributes, if supported.  In the build XML these
     * are referred to like this:
     *         <regexp pattern="\n" replace="%{task.current_file}"/>
     * In the type/task a listener method must be defined:
     *         function setListeningReplace($slot) {}
     * @var array string[]
     */
    private $slotListeners = array();

    /**
     * The method to add PCDATA stuff.
     *
     * @var string Method name of the addText (redundant?) method, if class supports it :)
     */
    private $methodAddText = null;

    /**
     * The Class that's been introspected.
     *
     * @var     object
     */
    private $bean;

    /**
     * The cache of IntrospectionHelper classes instantiated by getHelper().
     * @var array IntrospectionHelpers[]
     */
    private static $helpers = array();

    /**
     * Factory method for helper objects.
     *
     * @param string $class The class to create a Helper for
     */
    public static function getHelper($class)
    {
        if (!isset(self::$helpers[$class])) {
            self::$helpers[$class] = new IntrospectionHelper($class);
        }

        return self::$helpers[$class];
    }

    /**
     * This function constructs a new introspection helper for a specific class.
     *
     * This method loads all methods for the specified class and categorizes them
     * as setters, creators, slot listeners, etc.  This way, the setAttribue() doesn't
     * need to perform any introspection -- either the requested attribute setter/creator
     * exists or it does not & a BuildException is thrown.
     *
     * @param string $class The classname for this IH.
     * @throws BuildException
     */
    public function __construct($class)
    {

        $this->bean = new ReflectionClass($class);

        //$methods = get_class_methods($bean);
        foreach ($this->bean->getMethods() as $method) {

            if ($method->isPublic()) {

                // We're going to keep case-insensitive method names
                // for as long as we're allowed :)  It makes it much
                // easier to map XML attributes to PHP class method names.
                $name = strtolower($method->getName());

                // There are a few "reserved" names that might look like attribute setters
                // but should actually just be skipped.  (Note: this means you can't ever
                // have an attribute named "location" or "tasktype" or a nested element named "task".)
                if ($name === "setlocation" || $name === "settasktype" || $name === "addtask") {
                    continue;
                }

                if ($name === "addtext") {

                    $this->methodAddText = $method;

                } elseif (strpos($name, "setlistening") === 0) {

                    // Phing supports something unique called "RegisterSlots"
                    // These are dynamic values that use a basic slot system so that
                    // classes can register to listen to specific slots, and the value
                    // will always be grabbed from the slot (and never set in the project
                    // component).  This is useful for things like tracking the current
                    // file being processed by a filter (e.g. AppendTask sets an append.current_file
                    // slot, which can be ready by the XSLTParam type.)

                    if (count($method->getParameters()) !== 1) {
                        throw new BuildException($method->getDeclaringClass()->getName() . "::" . $method->getName(
                            ) . "() must take exactly one parameter.");
                    }

                    $this->slotListeners[$name] = $method;

                } elseif (strpos($name, "set") === 0) {

                    // A standard attribute setter.

                    if (count($method->getParameters()) !== 1) {
                        throw new BuildException($method->getDeclaringClass()->getName() . "::" . $method->getName(
                            ) . "() must take exactly one parameter.");
                    }

                    $this->attributeSetters[$name] = $method;

                } elseif (strpos($name, "create") === 0) {

                    if ($method->getNumberOfRequiredParameters() > 0) {
                        throw new BuildException($method->getDeclaringClass()->getName() . "::" . $method->getName(
                            ) . "() may not take any parameters.");
                    }

                    // Because PHP doesn't support return types, we are going to do
                    // two things here to guess return type:
                    //     1) parse comments for an explicit value
                    //     2) if that fails, assume that the part of the method after "create"
                    //    is the name of the return type (in many cases it is not)

                    // This isn't super important -- i.e. we're not instantaiting classes
                    // based on this information.  It's more just so that IntrospectionHelper
                    // can keep track of all the nested types -- and provide more helpful
                    // exception messages, etc.

                    preg_match('/@return[\s]+([\w]+)/', $method->getDocComment(), $matches);
                    if (!empty($matches[1]) && class_exists($matches[1], false)) {
                        $this->nestedTypes[$name] = $matches[1];
                    } else {
                        // assume that method createEquals() creates object of type "Equals"
                        // (that example would be false, of course)
                        $this->nestedTypes[$name] = $this->getPropertyName($name, "create");
                    }

                    $this->nestedCreators[$name] = $method;

                } elseif (strpos($name, "addconfigured") === 0) {

                    // *must* use class hints if using addConfigured ...

                    // 1 param only
                    $params = $method->getParameters();

                    if (count($params) < 1) {
                        throw new BuildException($method->getDeclaringClass()->getName() . "::" . $method->getName(
                            ) . "() must take at least one parameter.");
                    }

                    if (count($params) > 1) {
                        $this->warn(
                            $method->getDeclaringClass()->getName() . "::" . $method->getName(
                            ) . "() takes more than one parameter. (IH only uses the first)"
                        );
                    }

                    $classname = null;

                    if (($hint = $params[0]->getClass()) !== null) {
                        $classname = $hint->getName();
                    }

                    if ($classname === null) {
                        throw new BuildException($method->getDeclaringClass()->getName() . "::" . $method->getName(
                            ) . "() method MUST use a class hint to indicate the class type of parameter.");
                    }

                    $this->nestedTypes[$name] = $classname;

                    $this->nestedStorers[$name] = $method;

                } elseif (strpos($name, "add") === 0) {

                    // *must* use class hints if using add ...

                    // 1 param only
                    $params = $method->getParameters();
                    if (count($params) < 1) {
                        throw new BuildException($method->getDeclaringClass()->getName() . "::" . $method->getName(
                            ) . "() must take at least one parameter.");
                    }

                    if (count($params) > 1) {
                        $this->warn(
                            $method->getDeclaringClass()->getName() . "::" . $method->getName(
                            ) . "() takes more than one parameter. (IH only uses the first)"
                        );
                    }

                    $classname = null;

                    if (($hint = $params[0]->getClass()) !== null) {
                        $classname = $hint->getName();
                    }

                    // we don't use the classname here, but we need to make sure it exists before
                    // we later try to instantiate a non-existent class
                    if ($classname === null) {
                        throw new BuildException($method->getDeclaringClass()->getName() . "::" . $method->getName(
                            ) . "() method MUST use a class hint to indicate the class type of parameter.");
                    }

                    $this->nestedCreators[$name] = $method;
                }
            } // if $method->isPublic()
        } // foreach
    }

    /**
     * Sets the named attribute.
     * @param Project $project
     * @param string $element
     * @param string $attributeName
     * @param mixed $value
     * @throws BuildException
     */
    public function setAttribute(Project $project, $element, $attributeName, &$value)
    {

        // we want to check whether the value we are setting looks like
        // a slot-listener variable:  %{task.current_file}
        //
        // slot-listener variables are not like properties, in that they cannot be mixed with
        // other text values.  The reason for this disparity is that properties are only
        // set when first constructing objects from XML, whereas slot-listeners are always dynamic.
        //
        // This is made possible by PHP5 (objects automatically passed by reference) and PHP's loose
        // typing.

        if (StringHelper::isSlotVar($value)) {

            $as = "setlistening" . strtolower($attributeName);

            if (!isset($this->slotListeners[$as])) {
                $msg = $this->getElementName(
                        $project,
                        $element
                    ) . " doesn't support a slot-listening '$attributeName' attribute.";
                throw new BuildException($msg);
            }

            $method = $this->slotListeners[$as];

            $key = StringHelper::slotVar($value);
            $value = Register::getSlot(
                $key
            ); // returns a RegisterSlot object which will hold current value of that register (accessible using getValue())

        } else {

            // Traditional value options

            $as = "set" . strtolower($attributeName);

            if (!isset($this->attributeSetters[$as])) {
                $msg = $this->getElementName($project, $element) . " doesn't support the '$attributeName' attribute.";
                throw new BuildException($msg);
            }

            $method = $this->attributeSetters[$as];

            if ($as == "setrefid") {
                $value = new Reference($value);
            } else {
                // value is a string representation of a boolean type,
                // convert it to primitive
                if (StringHelper::isBoolean($value)) {

                    $value = StringHelper::booleanValue($value);
                }

                // does method expect a PhingFile object? if so, then
                // pass a project-relative file.
                $params = $method->getParameters();

                $classname = null;

                if (($hint = $params[0]->getClass()) !== null) {
                    $classname = $hint->getName();
                }

                // there should only be one param; we'll just assume ....
                if ($classname !== null) {
                    switch (strtolower($classname)) {
                        case "phingfile":
                            $value = $project->resolveFile($value);
                            break;
                        case "path":
                            $value = new Path($project, $value);
                            break;
                        case "reference":
                            $value = new Reference($value);
                            break;
                        // any other object params we want to support should go here ...
                    }

                } // if hint !== null

            } // if not setrefid

        } // if is slot-listener

        try {
            $project->log(
                "    -calling setter " . $method->getDeclaringClass()->getName() . "::" . $method->getName() . "()",
                Project::MSG_DEBUG
            );
            $method->invoke($element, $value);
        } catch (Exception $exc) {
            throw new BuildException($exc);
        }

    }

    /**
     * Adds PCDATA areas.
     *
     * @param Project $project
     * @param string $element
     * @param string $text
     * @throws BuildException
     */
    public function addText(Project $project, $element, $text)
    {
        if ($this->methodAddText === null) {
            $msg = $this->getElementName($project, $element) . " doesn't support nested text data.";
            throw new BuildException($msg);
        }
        try {
            $method = $this->methodAddText;
            $method->invoke($element, $text);
        } catch (Exception $exc) {
            throw new BuildException($exc);
        }
    }

    /**
     * Creates a named nested element.
     *
     * Valid creators can be in the form createFoo() or addFoo(Bar).
     *
     * @param  Project        $project
     * @param  object         $element     Object the XML tag is child of.
     *                                     Often a task object.
     * @param  string         $elementName XML tag name
     * @return object         Returns the nested element.
     * @throws BuildException
     */
    public function createElement(Project $project, $element, $elementName)
    {

        $addMethod = "add" . strtolower($elementName);
        $createMethod = "create" . strtolower($elementName);
        $nestedElement = null;

        if (isset($this->nestedCreators[$createMethod])) {

            $method = $this->nestedCreators[$createMethod];
            try { // try to invoke the creator method on object
                $project->log(
                    "    -calling creator " . $method->getDeclaringClass()->getName() . "::" . $method->getName(
                    ) . "()",
                    Project::MSG_DEBUG
                );
                $nestedElement = $method->invoke($element);
            } catch (Exception $exc) {
                throw new BuildException($exc);
            }

        } elseif (isset($this->nestedCreators[$addMethod])) {

            $method = $this->nestedCreators[$addMethod];

            // project components must use class hints to support the add methods

            try { // try to invoke the adder method on object

                $project->log(
                    "    -calling adder " . $method->getDeclaringClass()->getName() . "::" . $method->getName() . "()",
                    Project::MSG_DEBUG
                );
                // we've already assured that correct num of params
                // exist and that method is using class hints
                $params = $method->getParameters();

                $classname = null;

                if (($hint = $params[0]->getClass()) !== null) {
                    $classname = $hint->getName();
                }

                // create a new instance of the object and add it via $addMethod
                $clazz = new ReflectionClass($classname);
                if ($clazz->getConstructor() !== null && $clazz->getConstructor()->getNumberOfRequiredParameters() === 1) {
                    $nestedElement = new $classname(Phing::getCurrentProject());
                } else {
                    $nestedElement = new $classname();
                }

                $method->invoke($element, $nestedElement);

            } catch (Exception $exc) {
                throw new BuildException($exc);
            }
        } elseif ($this->bean->implementsInterface("CustomChildCreator")) {
            $method = $this->bean->getMethod('customChildCreator');

            try {
                $nestedElement = $method->invoke($element, strtolower($elementName), $project);
            } catch (Exception $exc) {
                throw new BuildException($exc);
            }
        } else {
            //try the add method for the element's parent class
            $typedefs = $project->getDataTypeDefinitions();
            if (isset($typedefs[$elementName])) {
                $elementClass = Phing::import($typedefs[$elementName]);
                $parentClass = get_parent_class($elementClass);
                $addMethod = 'add' . strtolower($parentClass);

                if (isset($this->nestedCreators[$addMethod])) {
                    $method = $this->nestedCreators[$addMethod];
                    try {
                        $project->log(
                            "    -calling parent adder "
                            . $method->getDeclaringClass()->getName() . "::" . $method->getName() . "()",
                            Project::MSG_DEBUG
                        );
                        $nestedElement = new $elementClass();
                        $method->invoke($element, $nestedElement);
                    } catch (Exception $exc) {
                        throw new BuildException($exc);
                    }
                }
            }
            if ($nestedElement === null) {
                $msg = $this->getElementName($project, $element) . " doesn't support the '$elementName' creator/adder.";
                throw new BuildException($msg);
            }
        }

        if ($nestedElement instanceof ProjectComponent) {
            $nestedElement->setProject($project);
        }

        return $nestedElement;
    }

    /**
     * Creates a named nested element.
     *
     * @param  Project        $project
     * @param  string         $element
     * @param  string         $child
     * @param  string|null    $elementName
     * @return void
     * @throws BuildException
     */
    public function storeElement($project, $element, $child, $elementName = null)
    {

        if ($elementName === null) {
            return;
        }

        $storer = "addconfigured" . strtolower($elementName);

        if (isset($this->nestedStorers[$storer])) {

            $method = $this->nestedStorers[$storer];

            try {
                $project->log(
                    "    -calling storer " . $method->getDeclaringClass()->getName() . "::" . $method->getName() . "()",
                    Project::MSG_DEBUG
                );
                $method->invoke($element, $child);
            } catch (Exception $exc) {
                throw new BuildException($exc);
            }
        }

    }

    /**
     * Does the introspected class support PCDATA?
     * @return boolean
     */
    public function supportsCharacters()
    {
        return ($this->methodAddText !== null);
    }

    /**
     * Return all attribues supported by the introspected class.
     * @return string[]
     */
    public function getAttributes()
    {
        $attribs = array();
        foreach (array_keys($this->attributeSetters) as $setter) {
            $attribs[] = $this->getPropertyName($setter, "set");
        }

        return $attribs;
    }

    /**
     * Return all nested elements supported by the introspected class.
     * @return string[]
     */
    public function getNestedElements()
    {
        return $this->nestedTypes;
    }

    /**
     * Get the name for an element.
     * When possible the full classnam (phing.tasks.system.PropertyTask) will
     * be returned.  If not available (loaded in taskdefs or typedefs) then the
     * XML element name will be returned.
     *
     * @param  Project $project
     * @param  object  $element The Task or type element.
     * @return string  Fully qualified class name of element when possible.
     */
    public function getElementName(Project $project, $element)
    {

        $taskdefs = $project->getTaskDefinitions();
        $typedefs = $project->getDataTypeDefinitions();

        // check if class of element is registered with project (tasks & types)
        // most element types don't have a getTag() method
        $elClass = get_class($element);

        if (!in_array('getTag', get_class_methods($elClass))) {
            // loop through taskdefs and typesdefs and see if the class name
            // matches (case-insensitive) any of the classes in there
            foreach (array_merge($taskdefs, $typedefs) as $elName => $class) {
                if (0 === strcasecmp($elClass, StringHelper::unqualify($class))) {
                    return $class;
                }
            }

            return "$elClass (unknown)";
        } else {
            // ->getTag() method does exist, so use it
            $elName = $element->getTag();
            if (isset($taskdefs[$elName])) {
                return $taskdefs[$elName];
            } elseif (isset($typedefs[$elName])) {
                return $typedefs[$elName];
            } else {
                return "$elName (unknown)";
            }
        }
    }

    /**
     * Extract the name of a property from a method name - subtracting  a given prefix.
     *
     * @param  string $methodName
     * @param  string $prefix
     * @return string
     */
    public function getPropertyName($methodName, $prefix)
    {
        $start = strlen($prefix);

        return strtolower(substr($methodName, $start));
    }

    /**
     * Prints warning message to screen if -debug was used.
     * @param string $msg
     */
    public function warn($msg)
    {
        if (Phing::getMsgOutputLevel() === Project::MSG_DEBUG) {
            print("[IntrospectionHelper] " . $msg . "\n");
        }
    }

}
