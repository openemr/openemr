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
 * Attaches a suffix to every line.
 *
 * Example:
 * <pre><suffixlines suffix="Foo"/></pre>
 *
 * Or:
 *
 * <pre><filterreader classname="phing.filters.SuffixLines">
 *  <param name="suffix" value="Foo"/>
 * </filterreader></pre>
 *
 * @author    Siad Ardroumli <siad.ardroumli@gmail.com>
 * @see       FilterReader
 * @package   phing.filters
 */
class SuffixLines extends BaseParamFilterReader implements ChainableReader
{
    /**
     * Parameter name for the suffix.
     * @var string
     */
    const SUFFIX_KEY = "suffix";

    /**
     * The suffix to be used.
     * @var string
     */
    private $suffix = null;

    /**
     * Adds a suffix to each line of input stream and returns resulting stream.
     *
     * @param null $len
     * @return mixed buffer, -1 on EOF
     */
    public function read($len = null)
    {
        if (!$this->getInitialized()) {
            $this->_initialize();
            $this->setInitialized(true);
        }

        $buffer = $this->in->read($len);

        if ($buffer === -1) {
            return -1;
        }
        $lines = preg_split("~\R~", $buffer);
        $filtered = array();

        foreach ($lines as $line) {
            $filtered[] = $line . $this->suffix;
        }

        $filtered_buffer = implode(PHP_EOL, $filtered);

        return $filtered_buffer;
    }

    /**
     * Sets the suffix to add at the end of each input line.
     *
     * @param string $suffix The suffix to add at the start of each input line.
     *                       May be <code>null</code>, in which case no suffix
     *                       is added.
     */
    public function setSuffix($suffix)
    {
        $this->suffix = (string) $suffix;
    }

    /**
     * Returns the suffix which will be added at the end of each input line.
     *
     * @return string The suffix which will be added at the end of each input line
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * Creates a new PrefixLines filter using the passed in
     * Reader for instantiation.
     *
     * @param Reader $reader
     * @internal param A $object Reader object providing the underlying stream.
     *               Must not be <code>null</code>.
     *
     * @return object A new filter based on this configuration, but filtering
     *                the specified reader
     */
    public function chain(Reader $reader)
    {
        $newFilter = new SuffixLines($reader);
        $newFilter->setSuffix($this->getSuffix());
        $newFilter->setInitialized(true);
        $newFilter->setProject($this->getProject());

        return $newFilter;
    }

    /**
     * Initializes the suffix if it is available from the parameters.
     */
    private function _initialize()
    {
        $params = $this->getParameters();
        if ($params !== null) {
            for ($i = 0, $_i = count($params); $i < $_i; $i++) {
                if (self::SUFFIX_KEY == $params[$i]->getName()) {
                    $this->suffix = (string) $params[$i]->getValue();
                    break;
                }
            }
        }
    }
}
