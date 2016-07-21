<?php
/*
 *  $Id: 738a0e1b2434adba882fd21fa59f8a2f41377980 $
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
include_once 'phing/types/RegularExpression.php';
include_once 'phing/filters/ChainableReader.php';

/**
 * Filter which includes only those lines that contain the user-specified
 * regular expression matching strings.
 *
 * Example:
 * <pre><linecontainsregexp>
 *   <regexp pattern="foo*">
 * </linecontainsregexp></pre>
 *
 * Or:
 *
 * <pre><filterreader classname="phing.filters.LineContainsRegExp">
 *    <param type="regexp" value="foo*"/>
 * </filterreader></pre>
 *
 * This will fetch all those lines that contain the pattern <code>foo</code>
 *
 * @author    Yannick Lecaillez <yl@seasonfive.com>
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Id: 738a0e1b2434adba882fd21fa59f8a2f41377980 $
 * @see       FilterReader
 * @package   phing.filters
 */
class LineContainsRegexp extends BaseParamFilterReader implements ChainableReader
{

    /**
     * Parameter name for regular expression.
     * @var string
     */
    const REGEXP_KEY = "regexp";

    /**
     * Regular expressions that are applied against lines.
     * @var array
     */
    private $_regexps = array();

    /**
     * Returns all lines in a buffer that contain specified strings.
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

        $lines = explode("\n", $buffer);
        $matched = array();

        $regexpsSize = count($this->_regexps);
        foreach ($lines as $line) {
            for ($i = 0; $i < $regexpsSize; $i++) {
                $regexp = $this->_regexps[$i];
                $re = $regexp->getRegexp($this->getProject());
                $matches = $re->matches($line);
                if (!$matches) {
                    $line = null;
                    break;
                }
            }
            if ($line !== null) {
                $matched[] = $line;
            }
        }
        $filtered_buffer = implode("\n", $matched);

        return $filtered_buffer;
    }

    /**
     * Adds a <code>regexp</code> element.
     *
     * @return object regExp The <code>regexp</code> element added.
     */
    public function createRegexp()
    {
        $num = array_push($this->_regexps, new RegularExpression());

        return $this->_regexps[$num - 1];
    }

    /**
     * Sets the vector of regular expressions which must be contained within
     * a line read from the original stream in order for it to match this
     * filter.
     *
     * @param An $regexps
     * @throws Exception
     * @internal param An $regexps array of regular expressions which must be contained
     *                within a line in order for it to match in this filter. Must not be
     *                <code>null</code>.
     */
    public function setRegexps($regexps)
    {
        // type check, error must never occur, bad code of it does
        if (!is_array($regexps)) {
            throw new Exception("Excpected an 'array', got something else");
        }
        $this->_regexps = $regexps;
    }

    /**
     * Returns the array of regular expressions which must be contained within
     * a line read from the original stream in order for it to match this
     * filter.
     *
     * @return array The array of regular expressions which must be contained within
     *               a line read from the original stream in order for it to match this
     *               filter. The returned object is "live" - in other words, changes made to
     *               the returned object are mirrored in the filter.
     */
    public function getRegexps()
    {
        return $this->_regexps;
    }

    /**
     * Creates a new LineContainsRegExp using the passed in
     * Reader for instantiation.
     *
     * @param Reader $reader
     * @throws Exception
     * @internal param A $object Reader object providing the underlying stream.
     *               Must not be <code>null</code>.
     *
     * @return object A new filter based on this configuration, but filtering
     *                the specified reader
     */
    public function chain(Reader $reader)
    {
        $newFilter = new LineContainsRegExp($reader);
        $newFilter->setRegexps($this->getRegexps());
        $newFilter->setInitialized(true);
        $newFilter->setProject($this->getProject());

        return $newFilter;
    }

    /**
     * Parses parameters to add user defined regular expressions.
     */
    private function _initialize()
    {
        $params = $this->getParameters();
        if ($params !== null) {
            for ($i = 0; $i < count($params); $i++) {
                if (self::REGEXP_KEY === $params[$i]->getType()) {
                    $pattern = $params[$i]->getValue();
                    $regexp = new RegularExpression();
                    $regexp->setPattern($pattern);
                    array_push($this->_regexps, $regexp);
                }
            }
        }
    }
}
