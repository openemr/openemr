<?php
/*
 *  $Id: 53455515ad7c75ce22073d8bfbc06779e2ec7e54 $
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
include_once 'phing/parser/ExpatParseException.php';
include_once 'phing/parser/ProjectHandler.php';

/**
 * Root filter class for a phing buildfile.
 *
 * The root filter is called by the parser first. This is where the phing
 * specific parsing starts. RootHandler decides what to do next.
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright 2001,2002 THYRELL. All rights reserved
 * @version   $Id: 53455515ad7c75ce22073d8bfbc06779e2ec7e54 $
 * @package   phing.parser
 */
class RootHandler extends AbstractHandler
{

    /**
     * The phing project configurator object
     */
    private $configurator;

    /**
     * @var PhingXMLContext
     */
    private $context;

    /**
     * Constructs a new RootHandler
     *
     * The root filter is required so the parser knows what to do. It's
     * called by the ExpatParser that is instatiated in ProjectConfigurator.
     *
     * It receives the expat parse object ref and a reference to the
     * configurator
     *
     * @param AbstractSAXParser $parser The ExpatParser object.
     * @param ProjectConfigurator $configurator The ProjectConfigurator object.
     * @param PhingXMLContext $context
     */
    public function __construct(AbstractSAXParser $parser, ProjectConfigurator $configurator, PhingXMLContext $context)
    {
        $this->configurator = $configurator;
        $this->context = $context;

        parent::__construct($parser, $this);
    }

    /**
     * Kick off a custom action for a start element tag.
     *
     * The root element of our buildfile is the &lt;project&gt; element. The
     * root filter handles this element if it occurs, creates ProjectHandler
     * to handle any nested tags & attributes of the &lt;project&gt; tag,
     * and calls init.
     *
     * @param  string              $tag   The xml tagname
     * @param  array               $attrs The attributes of the tag
     * @throws ExpatParseException if the first element within our build file
     *                                   is not the &gt;project&lt; element
     */
    public function startElement($tag, $attrs)
    {
        if ($tag === "project") {
            $ph = new ProjectHandler($this->parser, $this, $this->configurator, $this->context);
            $ph->init($tag, $attrs);
        } else {
            throw new ExpatParseException("Unexpected tag <$tag> in top-level of build file.", $this->parser->getLocation(
            ));
        }
    }
}
