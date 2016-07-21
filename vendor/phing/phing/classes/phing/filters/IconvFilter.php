<?php

/*
 *  $Id: fdd2765ea9675f36fde778b3faf373505cc0ccc2 $
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

include_once 'phing/filters/BaseParamFilterReader.php';
include_once 'phing/filters/ChainableReader.php';

/**
 * Encode data from <code>in</code> encoding to <code>out</code> encoding.
 *
 * Example:
 * <pre>
 * <iconvfilter inputencoding="UTF-8" outputencoding="CP1251" />
 * </pre>
 * Or:
 * <pre>
 * <filterreader classname="phing.filters.IconvFilter">
 *    <param name="inputencoding" value="UTF-8" />
 *    <param name="outputencoding" value="CP1251" />
 * </filterreader>
 * </pre>
 *
 * @author    Alexey Shockov, <alexey@shockov.com>
 * @version   $Id: fdd2765ea9675f36fde778b3faf373505cc0ccc2 $
 * @package   phing.filters
 */
class IconvFilter
    extends BaseParamFilterReader
    implements ChainableReader
{

    private $_inputEncoding;

    private $_outputEncoding;

    /**
     * Returns first n lines of stream.
     * @param null $len
     * @return the resulting stream, or -1
     *             if the end of the resulting stream has been reached
     *
     * @exception IOException if the underlying stream throws an IOException
     * during reading
     */
    public function read($len = null)
    {
        $this->_initialize();

        // Process whole text at once.
        $text = null;
        while (($data = $this->in->read($len)) !== -1) {
            $text .= $data;
        }

        // At the end.
        if (null === $text) {
            return -1;
        }

        $this->log(
            "Encoding " . $this->in->getResource() . " from " . $this->getInputEncoding(
            ) . " to " . $this->getOutputEncoding(),
            Project::MSG_VERBOSE
        );

        return iconv($this->_inputEncoding, $this->_outputEncoding, $text);
    }

    /**
     *
     * @param string $encoding Input encoding.
     */
    public function setInputEncoding($encoding)
    {
        $this->_inputEncoding = $encoding;
    }

    /**
     *
     * @return string
     */
    public function getInputEncoding()
    {
        return $this->_inputEncoding;
    }

    /**
     *
     * @param string $encoding Output encoding.
     */
    public function setOutputEncoding($encoding)
    {
        $this->_outputEncoding = $encoding;
    }

    /**
     *
     * @return string
     */
    public function getOutputEncoding()
    {
        return $this->_outputEncoding;
    }

    /**
     * Creates a new IconvFilter using the passed in Reader for instantiation.
     *
     * @param Reader $reader
     * @internal param A $object Reader object providing the underlying stream. Must not be <code>null</code>.
     *
     * @return object A new filter based on this configuration, but filtering the specified reader.
     */
    public function chain(Reader $reader)
    {
        $filter = new self($reader);

        $filter->setInputEncoding($this->getInputEncoding());
        $filter->setOutputEncoding($this->getOutputEncoding());

        $filter->setInitialized(true);
        $filter->setProject($this->getProject());

        return $filter;
    }

    /**
     * Configuring object from the parameters list.
     */
    private function _initialize()
    {
        if ($this->getInitialized()) {
            return;
        }

        $params = $this->getParameters();
        if ($params !== null) {
            foreach ($params as $param) {
                if ('in' == $param->getName()) {
                    $this->setInputEncoding($param->getValue());
                } else {
                    if ('out' == $param->getName()) {
                        $this->setOutputEncoding($param->getValue());
                    }
                }
            }
        }

        $this->setInitialized(true);
    }
}
