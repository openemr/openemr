<?php

/*
 * $Id: f9449e6f47abcdaf727d3e42eeac9ab1c43998a2 $
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

include_once 'phing/parser/ExpatParseException.php';

/**
 * This is an abstract class all SAX handler classes must extend
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright 2001,2002 THYRELL. All rights reserved
 * @version   $Id: f9449e6f47abcdaf727d3e42eeac9ab1c43998a2 $
 * @package   phing.parser
 */
abstract class AbstractHandler
{

    public $parentHandler = null;
    public $parser = null;

    /**
     * Constructs a SAX handler parser.
     *
     * The constructor must be called by all derived classes.
     *
     * @param   object  the parser object
     * @param   object  the parent handler of this handler
     */
    protected function __construct($parser, $parentHandler)
    {
        $this->parentHandler = $parentHandler;
        $this->parser = $parser;
        $this->parser->setHandler($this);
    }

    /**
     * Gets invoked when a XML open tag occurs
     *
     * Must be overloaded by the child class. Throws an ExpatParseException
     * if there is no handler registered for an element.
     *
     * @param $name
     * @param $attribs
     * @throws ExpatParseException
     * @internal param the $string name of the XML element
     * @internal param the $array attributes of the XML element
     */
    public function startElement($name, $attribs)
    {
        throw new ExpatParseException("Unexpected element $name");
    }

    /**
     * Gets invoked when element closes method.
     *
     */
    protected function finished()
    {
    }

    /**
     * Gets invoked when a XML element ends.
     *
     * Can be overloaded by the child class. But should not. It hands
     * over control to the parentHandler of this.
     *
     * @param  string  the name of the XML element
     */
    public function endElement($name)
    {
        $this->finished();
        $this->parser->setHandler($this->parentHandler);
    }

    /**
     * Invoked by occurrence of #PCDATA.
     *
     * @param     string  the name of the XML element
     * @throws ExpatParseException
     * @exception ExpatParserException if there is no CDATA but method
     *            was called
     */
    public function characters($data)
    {
        $s = trim($data);
        if (strlen($s) > 0) {
            throw new ExpatParseException("Unexpected text '$s'", $this->parser->getLocation());
        }
    }
}
