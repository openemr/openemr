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

require_once 'phing/filters/BaseParamFilterReader.php';

/**
 * Reads the last <code>n</code> lines of a stream. (Default is last10 lines.)
 *
 * Example:
 *
 * <pre><tailfilter lines="3" /></pre>
 *
 * Or:
 *
 * <pre><filterreader classname="phing.filters.TailFilter">
 *   <param name="lines" value="3">
 * </filterreader></pre>
 *
 * @author    <a href="mailto:yl@seasonfive.com">Yannick Lecaillez</a>
 * @author    hans lellelid, hans@velum.net
 * @copyright 2003 seasonfive. All rights reserved
 *
 * @see       BaseParamFilterReader
 *
 * @package   phing.filters
 */
class TailFilter extends BaseParamFilterReader implements ChainableReader
{
    /**
     * Parameter name for the number of lines to be returned.
     * @var string
     */
    const LINES_KEY = "lines";

    /**
     * Number of lines to be returned in the filtered stream.
     * @var integer
     */
    private $_lines = 10;

    /**
     * Array to hold lines.
     * @var array
     */
    private $_lineBuffer = array();

    /**
     * Returns the last n lines of a file.
     * @param  int   $len Num chars to read.
     * @return mixed The filtered buffer or -1 if EOF.
     */
    public function read($len = null)
    {

        while (($buffer = $this->in->read($len)) !== -1) {
            // Remove the last "\n" from buffer for
            // prevent explode to add an empty cell at
            // the end of array
            $buffer = trim($buffer, "\n");

            $lines = explode("\n", $buffer);

            if (count($lines) >= $this->_lines) {
                // Buffer have more (or same) number of lines than needed.
                // Fill lineBuffer with the last "$this->_lines" lasts ones.
                $off = count($lines) - $this->_lines;
                $this->_lineBuffer = array_slice($lines, $off);
            } else {
                // Some new lines ...
                // Prepare space for insert these new ones
                $this->_lineBuffer = array_slice($this->_lineBuffer, count($lines) - 1);
                $this->_lineBuffer = array_merge($this->_lineBuffer, $lines);
            }
        }

        if (empty($this->_lineBuffer)) {
            $ret = -1;
        } else {
            $ret = implode("\n", $this->_lineBuffer);
            $this->_lineBuffer = array();
        }

        return $ret;
    }

    /**
     * Sets the number of lines to be returned in the filtered stream.
     *
     * @param integer $lines the number of lines to be returned in the filtered stream.
     */
    public function setLines($lines)
    {
        $this->_lines = (int) $lines;
    }

    /**
     * Returns the number of lines to be returned in the filtered stream.
     *
     * @return integer The number of lines to be returned in the filtered stream.
     */
    public function getLines()
    {
        return $this->_lines;
    }

    /**
     * Creates a new TailFilter using the passed in
     * Reader for instantiation.
     *
     * @param Reader $reader A Reader object providing the underlying stream.
     *                       Must not be <code>null</code>.
     *
     * @return TailFilter A new filter based on this configuration, but filtering
     *                    the specified reader.
     */
    public function chain(Reader $reader)
    {
        $newFilter = new TailFilter($reader);
        $newFilter->setLines($this->getLines());
        $newFilter->setInitialized(true);
        $newFilter->setProject($this->getProject());

        return $newFilter;
    }

    /**
     * Scans the parameters list for the "lines" parameter and uses
     * it to set the number of lines to be returned in the filtered stream.
     */
    private function _initialize()
    {
        $params = $this->getParameters();
        if ($params !== null) {
            for ($i = 0, $_i = count($params); $i < $_i; $i++) {
                if (self::LINES_KEY == $params[$i]->getName()) {
                    $this->_lines = (int) $params[$i]->getValue();
                    break;
                }
            }
        }
    }
}
