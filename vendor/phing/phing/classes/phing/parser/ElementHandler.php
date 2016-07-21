<?php
/*
 *  $Id: cf184abc517b45b04db2dd95827cd0e3998b0712 $
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

require_once 'phing/parser/AbstractHandler.php';
require_once 'phing/UnknownElement.php';

/**
 * The generic element handler class.
 *
 * This class handles the occurrence of runtime registered tags like
 * datatypes (fileset, patternset, etc) and it's possible nested tags. It
 * introspects the implementation of the class and sets up the data structures.
 *
 * @author    Michiel Rook <mrook@php.net>
 * @copyright 2001,2002 THYRELL. All rights reserved
 * @version   $Id: cf184abc517b45b04db2dd95827cd0e3998b0712 $
 * @package   phing.parser
 */
class ElementHandler extends AbstractHandler
{

    /**
     * Reference to the parent object that represents the parent tag
     * of this nested element
     * @var object
     */
    private $parent;

    /**
     * Reference to the child object that represents the child tag
     * of this nested element
     * @var object
     */
    private $child;

    /**
     *  Reference to the parent wrapper object
     * @var object
     */
    private $parentWrapper;

    /**
     *  Reference to the child wrapper object
     * @var object
     */
    private $childWrapper;

    /**
     *  Reference to the related target object
     * @var object the target instance
     */
    private $target;

    /**
     *  Constructs a new NestedElement handler and sets up everything.
     *
     * @param  object  the ExpatParser object
     * @param  object  the parent handler that invoked this handler
     * @param  object  the ProjectConfigurator object
     * @param  object  the parent object this element is contained in
     * @param  object  the parent wrapper object
     * @param  object  the target object this task is contained in
     */
    public function __construct(
        $parser,
        $parentHandler,
        $configurator,
        $parent = null,
        $parentWrapper = null,
        $target = null
    ) {
        parent::__construct($parser, $parentHandler);
        $this->configurator = $configurator;
        if ($parentWrapper != null) {
            $this->parent = $parentWrapper->getProxy();
        } else {
            $this->parent = $parent;
        }
        $this->parentWrapper = $parentWrapper;
        $this->target = $target;
    }

    /**
     * Executes initialization actions required to setup the data structures
     * related to the tag.
     * <p>
     * This includes:
     * <ul>
     * <li>creation of the nested element</li>
     * <li>calling the setters for attributes</li>
     * <li>adding the element to the container object</li>
     * <li>adding a reference to the element (if id attribute is given)</li>
     * </ul>
     *
     * @param  string  the tag that comes in
     * @param  array   attributes the tag carries
     * @throws ExpatParseException if the setup process fails
     */
    public function init($propType, $attrs)
    {
        $configurator = $this->configurator;
        $project = $this->configurator->project;

        try {
            $this->child = new UnknownElement(strtolower($propType));
            $this->child->setTaskName($propType);
            $this->child->setTaskType($propType);
            $this->child->setProject($project);
            $this->child->setLocation($this->parser->getLocation());

            if ($this->target !== null) {
                $this->child->setOwningTarget($this->target);
            }

            if ($this->parent !== null) {
                $this->parent->addChild($this->child);
            } elseif ($this->target !== null) {
                $this->target->addTask($this->child);
            }

            $configurator->configureId($this->child, $attrs);

            $this->childWrapper = new RuntimeConfigurable($this->child, $propType);
            $this->childWrapper->setAttributes($attrs);

            if ($this->parentWrapper !== null) {
                $this->parentWrapper->addChild($this->childWrapper);
            }
        } catch (BuildException $exc) {
            throw new ExpatParseException("Error initializing nested element <$propType>", $exc, $this->parser->getLocation(
            ));
        }
    }

    /**
     * Handles character data.
     *
     * @param  string  the CDATA that comes in
     * @throws ExpatParseException if the CDATA could not be set-up properly
     */
    public function characters($data)
    {
        $configurator = $this->configurator;
        $project = $this->configurator->project;

        $this->childWrapper->addText($data);
    }

    /**
     * Checks for nested tags within the current one. Creates and calls
     * handlers respectively.
     *
     * @param  string  the tag that comes in
     * @param  array   attributes the tag carries
     */
    public function startElement($name, $attrs)
    {
        $eh = new ElementHandler($this->parser, $this, $this->configurator, $this->child, $this->childWrapper, $this->target);
        $eh->init($name, $attrs);
    }
}
