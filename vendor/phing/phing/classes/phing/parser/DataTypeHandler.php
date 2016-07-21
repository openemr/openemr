<?php
/*
 *  $Id: b8f555762a9eb7b012fb7712a208979dc18f82ab $
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

include_once 'phing/RuntimeConfigurable.php';

/**
 * Configures a Project (complete with Targets and Tasks) based on
 * a XML build file.
 * <p>
 * Design/ZE2 migration note:
 * If PHP would support nested classes. All the phing/parser/*Filter
 * classes would be nested within this class
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright 2001,2002 THYRELL. All rights reserved
 * @version   $Id: b8f555762a9eb7b012fb7712a208979dc18f82ab $
 * @package   phing.parser
 */

class DataTypeHandler extends AbstractHandler
{

    private $target;
    private $element;
    private $wrapper;

    /**
     * Constructs a new DataTypeHandler and sets up everything.
     *
     * @param AbstractSAXParser   $parser        The XML parser (default: ExpatParser)
     * @param AbstractHandler     $parentHandler The parent handler that invoked this handler.
     * @param ProjectConfigurator $configurator  The ProjectConfigurator object
     * @param Target              $target        The target object this datatype is contained in (null for top-level datatypes).
     */
    public function __construct(
        AbstractSAXParser $parser,
        AbstractHandler $parentHandler,
        ProjectConfigurator $configurator,
        $target = null
    ) { // FIXME b2 typehinting
        parent::__construct($parser, $parentHandler);
        $this->target = $target;
        $this->configurator = $configurator;
    }

    /**
     * Executes initialization actions required to setup the data structures
     * related to the tag.
     * <p>
     * This includes:
     * <ul>
     * <li>creation of the datatype object</li>
     * <li>calling the setters for attributes</li>
     * <li>adding the type to the target object if any</li>
     * <li>adding a reference to the task (if id attribute is given)</li>
     * </ul>
     *
     * @param  string  the tag that comes in
     * @param  array   attributes the tag carries
     * @throws ExpatParseException if attributes are incomplete or invalid
     */
    public function init($propType, $attrs)
    {
        // shorthands
        $project = $this->configurator->project;
        $configurator = $this->configurator;

        try { //try
            $this->element = $project->createDataType($propType);

            if ($this->element === null) {
                throw new BuildException("Unknown data type $propType");
            }

            if ($this->target !== null) {
                $this->wrapper = new RuntimeConfigurable($this->element, $propType);
                $this->wrapper->setAttributes($attrs);
                $this->target->addDataType($this->wrapper);
            } else {
                $configurator->configure($this->element, $attrs, $project);
                $configurator->configureId($this->element, $attrs);
            }

        } catch (BuildException $exc) {
            throw new ExpatParseException($exc, $this->parser->getLocation());
        }
    }

    /**
     * Handles character data.
     *
     * @param $data
     * @throws ExpatParseException
     * @internal param the $string CDATA that comes in
     */
    public function characters($data)
    {
        $project = $this->configurator->project;
        try { //try
            $this->configurator->addText($project, $this->element, $data);
        } catch (BuildException $exc) {
            throw new ExpatParseException($exc->getMessage(), $this->parser->getLocation());
        }
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
        $nef = new NestedElementHandler($this->parser, $this, $this->configurator, $this->element, $this->wrapper, $this->target);
        $nef->init($name, $attrs);
    }

    /**
     * Overrides endElement for data types. Tells the type
     * handler that processing the element had been finished so
     * handlers know they can perform actions that need to be
     * based on the data contained within the element.
     *
     * @param  string  the name of the XML element
     * @return void
     */
    public function endElement($name)
    {
        $this->element->parsingComplete();
        parent::endElement($name);
    }

}
