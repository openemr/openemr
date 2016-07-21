<?php
/**
 * $Id: d26c94795e93844f84a602ed31361de6606989ed $
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

require_once 'phing/system/io/PhingFile.php';
require_once 'phing/tasks/ext/pdo/PDOResultFormatter.php';

/**
 * XML formatter for PDO results.
 *
 * This class reprsents the output of a query using a simple XML schema.
 *
 * <results>
 *  <row>
 *   <col name="id">value</col>
 *   <col name="name">value2</col>
 *  </row>
 *  <row>
 *   <col name="id">value</col>
 *   <col name="name">value2</col>
 *  </row>
 * </results>
 *
 * The actual names of the colums will depend on the fetchmode that was used
 * with PDO.
 *
 * @author Hans Lellelid <hans@xmpl.org>
 * @package phing.tasks.ext.pdo
 * @since 2.3.0
 */
class XMLPDOResultFormatter extends PDOResultFormatter
{

    /**
     * The XML document being created.
     * @var DOMDocument
     */
    private $doc;

    /**
     * @var DOMElement
     */
    private $rootNode;

    /**
     * XML document encoding
     *
     * @var string
     */
    private $encoding;

    /**
     * @var boolean
     */
    private $formatOutput = true;

    /**
     * Set the DOM document encoding.
     * @param string $v
     */
    public function setEncoding($v)
    {
        $this->encoding = $v;
    }

    /**
     * @param boolean $v
     */
    public function setFormatOutput($v)
    {
        $this->formatOutput = (boolean) $v;
    }

    public function initialize()
    {
        $this->doc = new DOMDocument("1.0", $this->encoding);
        $this->rootNode = $this->doc->createElement('results');
        $this->doc->appendChild($this->rootNode);
        $this->doc->formatOutput = $this->formatOutput;
    }

    /**
     * Processes a specific row from PDO result set.
     *
     * @param array $row Row of PDO result set.
     */
    public function processRow($row)
    {

        $rowNode = $this->doc->createElement('row');
        $this->rootNode->appendChild($rowNode);

        foreach ($row as $columnName => $columnValue) {

            $colNode = $this->doc->createElement('column');
            $colNode->setAttribute('name', $columnName);

            if ($columnValue != null) {
                $columnValue = trim($columnValue);
                $colNode->nodeValue = $columnValue;
            }
            $rowNode->appendChild($colNode);
        }

    }

    /**
     * Gets a preferred filename for an output file.
     *
     * If no filename is specified, this is where the results will be placed
     * (unless usefile=false).
     *
     * @return string
     */
    public function getPreferredOutfile()
    {
        return new PhingFile('results.xml');
    }

    /**
     * Write XML to file and free the DOM objects.
     */
    public function close()
    {
        $this->out->write($this->doc->saveXML());
        $this->rootNode = null;
        $this->doc = null;
        parent::close();
    }
}
