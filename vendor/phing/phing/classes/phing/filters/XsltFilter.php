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

include_once 'phing/filters/BaseParamFilterReader.php';
include_once 'phing/filters/ChainableReader.php';

/**
 * Applies XSL stylesheet to incoming text.
 *
 * Uses PHP XSLT support (libxslt).
 *
 * @author    Hans Lellelid <hans@velum.net>
 * @author    Yannick Lecaillez <yl@seasonfive.com>
 * @author    Andreas Aderhold <andi@binarycloud.com>
 *
 * @see       FilterReader
 * @package   phing.filters
 */
class XsltFilter extends BaseParamFilterReader implements ChainableReader
{

    /**
     * Path to XSL stylesheet.
     * @var string
     */
    private $xslFile = null;

    /**
     * Whether XML file has been transformed.
     * @var boolean
     */
    private $processed = false;

    /**
     * XSLT Params.
     * @var array
     */
    private $xsltParams = array();

    /**
     * Whether to use loadHTML() to parse the input XML file.
     */
    private $html = false;

    /**
     * Whether to resolve entities in the XML document (see
     * {@link http://www.php.net/manual/en/class.domdocument.php#domdocument.props.resolveexternals}
     * for more details).
     *
     * @var bool
     *
     * @since 2.4
     */
    private $resolveDocumentExternals = false;

    /**
     * Whether to resolve entities in the stylesheet.
     *
     * @var bool
     *
     * @since 2.4
     */
    private $resolveStylesheetExternals = false;

    /**
     * Create new XSLT Param object, to handle the <param/> nested element.
     * @return XSLTParam
     */
    public function createParam()
    {
        $num = array_push($this->xsltParams, new XSLTParam());

        return $this->xsltParams[$num - 1];
    }

    /**
     * Sets the XSLT params for this class.
     * This is used to "clone" this class, in the chain() method.
     * @param array $params
     */
    public function setParams($params)
    {
        $this->xsltParams = $params;
    }

    /**
     * Returns the XSLT params set for this class.
     * This is used to "clone" this class, in the chain() method.
     * @return array
     */
    public function getParams()
    {
        return $this->xsltParams;
    }

    /**
     * Set the XSLT stylesheet.
     * @param mixed $file PhingFile object or path.
     */
    public function setStyle(PhingFile $file)
    {
        $this->xslFile = $file;
    }

    /**
     * Whether to use HTML parser for the XML.
     * This is supported in libxml2 -- Yay!
     * @return boolean
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * Whether to use HTML parser for XML.
     * @param boolean $b
     */
    public function setHtml($b)
    {
        $this->html = (boolean) $b;
    }

    /**
     * Get the path to XSLT stylesheet.
     * @return mixed XSLT stylesheet path.
     */
    public function getStyle()
    {
        return $this->xslFile;
    }

    /**
     * Whether to resolve entities in document.
     *
     * @param bool $resolveExternals
     *
     * @since 2.4
     */
    public function setResolveDocumentExternals($resolveExternals)
    {
        $this->resolveDocumentExternals = (bool) $resolveExternals;
    }

    /**
     * @return bool
     *
     * @since 2.4
     */
    public function getResolveDocumentExternals()
    {
        return $this->resolveDocumentExternals;
    }

    /**
     * Whether to resolve entities in stylesheet.
     *
     * @param bool $resolveExternals
     *
     * @since 2.4
     */
    public function setResolveStylesheetExternals($resolveExternals)
    {
        $this->resolveStylesheetExternals = (bool) $resolveExternals;
    }

    /**
     * @return bool
     *
     * @since 2.4
     */
    public function getResolveStylesheetExternals()
    {
        return $this->resolveStylesheetExternals;
    }

    /**
     * Reads stream, applies XSLT and returns resulting stream.
     * @param null $len
     * @throws BuildException
     * @return string         transformed buffer.
     */
    public function read($len = null)
    {

        if (!class_exists('XSLTProcessor')) {
            throw new BuildException("Could not find the XSLTProcessor class. Make sure PHP has been compiled/configured to support XSLT.");
        }

        if ($this->processed === true) {
            return -1; // EOF
        }

        if (!$this->getInitialized()) {
            $this->_initialize();
            $this->setInitialized(true);
        }

        // Read XML
        $_xml = null;
        while (($data = $this->in->read($len)) !== -1) {
            $_xml .= $data;
        }

        if ($_xml === null) { // EOF?

            return -1;
        }

        if (empty($_xml)) {
            $this->log("XML file is empty!", Project::MSG_WARN);

            return ''; // return empty string, don't attempt to apply XSLT
        }

        // Read XSLT
        $_xsl = null;
        $xslFr = new FileReader($this->xslFile);
        $xslFr->readInto($_xsl);

        $this->log(
            "Tranforming XML " . $this->in->getResource() . " using style " . $this->xslFile->getPath(),
            Project::MSG_VERBOSE
        );

        $out = '';
        try {
            $out = $this->process($_xml, $_xsl);
            $this->processed = true;
        } catch (IOException $e) {
            throw new BuildException($e);
        }

        return $out;
    }

    // {{{ method _ProcessXsltTransformation($xml, $xslt) throws BuildException
    /**
     * Try to process the XSLT transformation
     *
     * @param string $xml XML to process.
     * @param string $xsl XSLT sheet to use for the processing.
     *
     * @return string
     *
     * @throws BuildException On XSLT errors
     */
    protected function process($xml, $xsl)
    {

        $processor = new XSLTProcessor();

        // Create and setup document.
        $xmlDom = new DOMDocument();
        $xmlDom->resolveExternals = $this->resolveDocumentExternals;

        // Create and setup stylesheet.
        $xslDom = new DOMDocument();
        $xslDom->resolveExternals = $this->resolveStylesheetExternals;

        if ($this->html) {
            $xmlDom->loadHTML($xml);
        } else {
            $xmlDom->loadXML($xml);
        }

        $xslDom->loadxml($xsl);

        if (defined('XSL_SECPREF_WRITE_FILE')) {
            if (version_compare(PHP_VERSION, '5.4', "<")) {
                ini_set("xsl.security_prefs", XSL_SECPREF_WRITE_FILE | XSL_SECPREF_CREATE_DIRECTORY);
            } else {
                $processor->setSecurityPrefs(XSL_SECPREF_WRITE_FILE | XSL_SECPREF_CREATE_DIRECTORY);
            }
        }
        $processor->importStylesheet($xslDom);

        // ignoring param "type" attrib, because
        // we're only supporting direct XSL params right now
        foreach ($this->xsltParams as $param) {
            $this->log("Setting XSLT param: " . $param->getName() . "=>" . $param->getExpression(), Project::MSG_DEBUG);
            $processor->setParameter(null, $param->getName(), $param->getExpression());
        }

        $errorlevel = error_reporting();
        error_reporting($errorlevel & ~E_WARNING);
        @$result = $processor->transformToXML($xmlDom);
        error_reporting($errorlevel);

        if (false === $result) {
            //$errno = xslt_errno($processor);
            //$err   = xslt_error($processor);
            throw new BuildException("XSLT Error");
        } else {
            return $result;
        }
    }

    /**
     * Creates a new XsltFilter using the passed in
     * Reader for instantiation.
     *
     * @param Reader A Reader object providing the underlying stream.
     *               Must not be <code>null</code>.
     *
     * @return XsltFilter A new filter based on this configuration, but filtering
     *                    the specified reader
     */
    public function chain(Reader $reader)
    {
        $newFilter = new XsltFilter($reader);
        $newFilter->setProject($this->getProject());
        $newFilter->setStyle($this->getStyle());
        $newFilter->setInitialized(true);
        $newFilter->setParams($this->getParams());
        $newFilter->setHtml($this->getHtml());

        return $newFilter;
    }

    /**
     * Parses the parameters to get stylesheet path.
     */
    private function _initialize()
    {
        $params = $this->getParameters();
        if ($params !== null) {
            for ($i = 0, $_i = count($params); $i < $_i; $i++) {
                if ($params[$i]->getType() === null) {
                    if ($params[$i]->getName() === "style") {
                        $this->setStyle($params[$i]->getValue());
                    }
                } elseif ($params[$i]->getType() == "param") {
                    $xp = new XSLTParam();
                    $xp->setName($params[$i]->getName());
                    $xp->setExpression($params[$i]->getValue());
                    $this->xsltParams[] = $xp;
                }
            }
        }
    }
}

/**
 * Class that holds an XSLT parameter.
 *
 * @package   phing.filters
 */
class XSLTParam
{

    private $name;

    /** @var RegisterSlot */
    private $expr;

    /**
     * Sets param name.
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get param name.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets expression value (alias to the setExpression()) method.
     *
     * @param string $v
     * @see setExpression()
     */
    public function setValue($v)
    {
        $this->setExpression($v);
    }

    /**
     * Gets expression value (alias to the getExpression()) method.
     *
     * @return string
     * @see getExpression()
     */
    public function getValue()
    {
        return $this->getExpression();
    }

    /**
     * Sets expression value.
     * @param string $expr
     */
    public function setExpression($expr)
    {
        $this->expr = $expr;
    }

    /**
     * Sets expression to dynamic register slot.
     * @param RegisterSlot $expr
     */
    public function setListeningExpression(RegisterSlot $expr)
    {
        $this->expr = $expr;
    }

    /**
     * Returns expression value -- performs lookup if expr is registerslot.
     *
     * @return string
     */
    public function getExpression()
    {
        if ($this->expr instanceof RegisterSlot) {
            return $this->expr->getValue();
        } else {
            return $this->expr;
        }
    }
}
