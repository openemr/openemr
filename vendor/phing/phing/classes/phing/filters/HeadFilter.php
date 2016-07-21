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
 * Reads the first <code>n</code> lines of a stream.
 * (Default is first 10 lines.)
 * <p>
 * Example:
 * <pre><headfilter lines="3"/></pre>
 * Or:
 * <pre><filterreader classname="phing.filters.HeadFilter">
 *    <param name="lines" value="3"/>
 * </filterreader></pre>
 *
 * @author    <a href="mailto:yl@seasonfive.com">Yannick Lecaillez</a>
 * @author    hans lellelid, hans@velum.net
 *
 * @see       FilterReader
 *
 * @package   phing.filters
 */
class HeadFilter extends BaseParamFilterReader implements ChainableReader
{
    /**
     * Parameter name for the number of lines to be returned.
     */
    const LINES_KEY = "lines";

    /**
     * Number of lines currently read in.
     *
     * @var integer
     */
    private $_linesRead = 0;

    /**
     * Number of lines to be returned in the filtered stream.
     *
     * @var integer
     */
    private $_lines = 10;

    /**
     * Returns first n lines of stream.
     *
     * @param null $len
     * @return string|int the resulting stream, or -1
     *                    if the end of the resulting stream has been reached
     *
     */
    public function read($len = null)
    {

        if (!$this->getInitialized()) {
            $this->_initialize();
            $this->setInitialized(true);
        }

        // note, if buffer contains fewer lines than
        // $this->_lines this code will not work.

        if ($this->_linesRead < $this->_lines) {

            $buffer = $this->in->read($len);

            if ($buffer === -1) {
                return -1;
            }

            // now grab first X lines from buffer

            $lines = explode("\n", $buffer);

            $linesCount = count($lines);

            // must account for possibility that the num lines requested could
            // involve more than one buffer read.
            $len = ($linesCount > $this->_lines ? $this->_lines - $this->_linesRead : $linesCount);
            $filtered_buffer = implode("\n", array_slice($lines, 0, $len));
            $this->_linesRead += $len;

            return $filtered_buffer;

        }

        return -1; // EOF, since the file is "finished" as far as subsequent filters are concerned.
    }

    /**
     * Sets the number of lines to be returned in the filtered stream.
     *
     * @param integer $lines the number of lines to be returned in the filtered stream.
     *
     * @return void
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
     * Creates a new HeadFilter using the passed in
     * Reader for instantiation.
     *
     * @param Reader $reader A Reader object providing the underlying stream.
     *                       Must not be <code>null</code>.
     *
     * @return HeadFilter A new filter based on this configuration, but filtering
     *                    the specified reader.
     */
    public function chain(Reader $reader)
    {
        $newFilter = new HeadFilter($reader);
        $newFilter->setLines($this->getLines());
        $newFilter->setInitialized(true);
        $newFilter->setProject($this->getProject());

        return $newFilter;
    }

    /**
     * Scans the parameters list for the "lines" parameter and uses
     * it to set the number of lines to be returned in the filtered stream.
     *
     * @return void
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
