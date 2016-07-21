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
include_once 'phing/util/StringHelper.php';

/**
 * <p>
 * Sort a file before and/or after the file.
 * </p>
 *
 * <p>
 * Examples:
 * </p>
 *
 * <pre>
 *   &lt;copy todir=&quot;build&quot;&gt;
 *       &lt;fileset dir=&quot;input&quot; includes=&quot;*.txt&quot;/&gt;
 *       &lt;filterchain&gt;
 *           &lt;sortfilter/&gt;
 *       &lt;/filterchain&gt;
 *   &lt;/copy&gt;
 * </pre>
 *
 * <p>
 * Sort all files <code>*.txt</code> from <i>src</i> location and copy
 * them into <i>build</i> location. The lines of each file are sorted
 * in ascendant order comparing the lines.
 * </p>
 *
 * <pre>
 *   &lt;copy todir=&quot;build&quot;&gt;
 *       &lt;fileset dir=&quot;input&quot; includes=&quot;*.txt&quot;/&gt;
 *       &lt;filterchain&gt;
 *           &lt;sortfilter reverse=&quot;true&quot;/&gt;
 *       &lt;/filterchain&gt;
 *   &lt;/copy&gt;
 * </pre>
 *
 * <p>
 * Sort all files <code>*.txt</code> from <i>src</i> location into reverse
 * order and copy them into <i>build</i> location. If reverse parameter has
 * value <code>true</code> (default value), then the output line of the files
 * will be in ascendant order.
 * </p>
 *
 * @author    Siad.ardroumli <siad.ardroumli@gmail.com>
 *
 * @see       BaseParamFilterReader
 *
 * @package   phing.filters
 */
class SortFilter extends BaseParamFilterReader implements ChainableReader
{
    /** Parameter name for reverse order. */
    private static $REVERSE_KEY = "reverse";

    /**
     * Controls if the sorting process will be in ascendant/descendant order. If
     * If has value <code>true</code>, then the line of the file will be
     * sorted on descendant order. Default value: <code>false</code>. It will
     * be considered only if <code>comparator</code> is <code>null</code>.
     */
    private $reverse;

    /**
     * Stores the lines to be sorted.
     */
    private $lines;

    /**
     * Creates a new filtered reader.
     *
     * @param Reader $in
     *            A Reader object providing the underlying stream. Must not be
     *            <code>null</code>.
     */
    public function __construct(Reader $in = null)
    {
        parent::__construct($in);
    }

    /**
     * Returns the next character in the filtered stream. If the desired number
     * of lines have already been read, the resulting stream is effectively at
     * an end. Otherwise, the next character from the underlying stream is read
     * and returned.
     *
     * @param int $len
     * @return string the next character in the resulting stream, or -1 if the end of
     *         the resulting stream has been reached
     * @throws BuildException
     * @exception IOException
     *                if the underlying stream throws an IOException during
     *                reading
     */
    public function read($len = null)
    {
        if (!$this->getInitialized()) {
            $this->initialize();
            $this->setInitialized(true);
        }

        $buffer = $this->in->read($len);

        if ($buffer === -1) {
            return -1;
        }

        $this->lines = explode("\n", $buffer);

        $this->sort();

        $filtered_buffer = implode("\n", $this->lines);

        return $filtered_buffer;
    }

    /**
     * Creates a new SortReader using the passed in Reader for instantiation.
     *
     * @param Reader $rdr
     *            A Reader object providing the underlying stream. Must not be
     *            <code>null</code>.
     *
     * @return SortFilter a new filter based on this configuration, but filtering the
     *         specified reader
     */
    public function chain(Reader $rdr)
    {
        $newFilter = new SortFilter($rdr);
        $newFilter->setReverse($this->isReverse());
        $newFilter->setInitialized(true);
        return $newFilter;
    }

    /**
     * Returns <code>true</code> if the sorting process will be in reverse
     * order, otherwise the sorting process will be in ascendant order.
     *
     * @return boolean <code>true</code> if the sorting process will be in reverse
     *                 order, otherwise the sorting process will be in ascendant order.
     */
    public function isReverse()
    {
        return $this->reverse;
    }

    /**
     * Sets the sorting process will be in ascendant (<code>reverse=false</code>)
     * or to descendant (<code>reverse=true</code>).
     *
     * @param boolean $reverse
     *            Boolean representing reverse ordering process.
     */
    public function setReverse($reverse)
    {
        $this->reverse = $reverse;
    }

    /**
     * Scans the parameters list
     */
    private function initialize()
    {
        // get parameters
        $params = $this->getParameters();

        foreach ($params as $param) {
            $paramName = $param->getName();
            if (self::$REVERSE_KEY === $paramName) {
                $this->setReverse(StringHelper::booleanValue($param->getValue()));
                continue;
            }
        }
    }

    /**
     * Sorts the read lines (<code>$this->lines</code>) according to the sorting
     * criteria defined by the user.
     */
    private function sort()
    {
        if ($this->reverse) {
            rsort($this->lines);
        } else {
            sort($this->lines);
        }
    }
}
