<?php

/*
 *  $Id: 4f9916cf83e322464568d6b0d4946f131ecf1c32 $
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

require_once 'phing/filters/BaseFilterReader.php';
include_once 'phing/filters/ChainableReader.php';
include_once 'phing/types/RegularExpression.php';

/**
 * Performs a regexp find/replace on stream.
 * <p>
 * Example:<br>
 * <pre>
 * <replaceregexp>
 *    <regexp pattern="\r\n" replace="\n"/>
 *    <regexp pattern="(\w+)\.xml" replace="\1.php" ignoreCase="true"/>
 * </replaceregexp>
 * </pre>
 *
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Id: 4f9916cf83e322464568d6b0d4946f131ecf1c32 $
 * @package   phing.filters
 */
class ReplaceRegexp extends BaseFilterReader implements ChainableReader
{

    /**
     * @var array RegularExpression[]
     */
    private $regexps = array();

    /**
     * Creator method handles nested <regexp> tags.
     * @return RegularExpression
     */
    public function createRegexp()
    {
        $num = array_push($this->regexps, new RegularExpression());

        return $this->regexps[$num - 1];
    }

    /**
     * Sets the current regexps.
     * (Used when, e.g., cloning/chaining the method.)
     * @param array RegularExpression[]
     */
    public function setRegexps($regexps)
    {
        $this->regexps = $regexps;
    }

    /**
     * Gets the current regexps.
     * (Used when, e.g., cloning/chaining the method.)
     * @return array RegularExpression[]
     */
    public function getRegexps()
    {
        return $this->regexps;
    }

    /**
     * Returns the filtered stream.
     * The original stream is first read in fully, and the regex replace is performed.
     *
     * @param int $len Required $len for Reader compliance.
     *
     * @return mixed The filtered stream, or -1 if the end of the resulting stream has been reached.
     *
     * @exception IOException if the underlying stream throws an IOException
     * during reading
     */
    public function read($len = null)
    {

        $buffer = $this->in->read($len);

        if ($buffer === -1) {
            return -1;
        }

        // perform regex replace here ...
        foreach ($this->regexps as $exptype) {
            $regexp = $exptype->getRegexp($this->project);
            try {
                $buffer = $regexp->replace($buffer);
                $this->log(
                    "Performing regexp replace: /" . $regexp->getPattern() . "/" . $regexp->getReplace(
                    ) . "/g" . $regexp->getModifiers(),
                    Project::MSG_VERBOSE
                );
            } catch (Exception $e) {
                // perhaps mismatch in params (e.g. no replace or pattern specified)
                $this->log("Error performing regexp replace: " . $e->getMessage(), Project::MSG_WARN);
            }
        }

        return $buffer;
    }

    /**
     * Creates a new ReplaceRegExp filter using the passed in
     * Reader for instantiation.
     *
     * @param Reader $reader A Reader object providing the underlying stream.
     *                       Must not be <code>null</code>.
     *
     * @return ReplaceRegExp A new filter based on this configuration, but filtering
     *                       the specified reader
     */
    public function chain(Reader $reader)
    {
        $newFilter = new ReplaceRegExp($reader);
        $newFilter->setProject($this->getProject());
        $newFilter->setRegexps($this->getRegexps());

        return $newFilter;
    }

}
