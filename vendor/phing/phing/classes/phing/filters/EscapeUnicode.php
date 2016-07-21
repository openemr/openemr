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

require_once 'phing/filters/BaseFilterReader.php';
require_once 'phing/filters/ChainableReader.php';

/**
 * UTF-8 to Unicode Code Points
 *
 * This method converts non-latin characters to unicode escapes.
 * Useful to load properties containing non latin.
 *
 * Example:
 *
 * `<escapeunicode>`
 *
 * Or:
 *
 * `<filterreader classname="phing.filters.EscapeUnicode"/>`
 *
 * @author    Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package   phing.filters
 */
class EscapeUnicode extends BaseFilterReader implements ChainableReader
{
    /**
     * Returns the next line in the filtered stream, converting non latin
     * characters to unicode escapes.
     *
     * @param int $len     optional
     * @return string      the converted lines in the resulting stream, or -1
     *                     if the end of the resulting stream has been reached
     * @throws IOException if the underlying stream throws
     *                     an IOException during reading
     */
    public function read($len = null)
    {
        if (!$this->getInitialized()) {
            $this->initialize();
            $this->setInitialized(true);
        }

        // Process whole text at once.
        $text = null;
        while (($data = $this->in->read($len)) !== -1) {
            $text .= $data;
        }

        // At the end.
        if (null === $text) {
            return -1;
        }

        $textArray = preg_split("~\R~", $text);

        $lines = array();
        foreach ($textArray as $offset => $line) {
            $lines[] = trim(json_encode($line), '"');
            if (strlen($line) !== strlen($lines[$offset])) {
                $this->log(
                    "Escape unicode chars on line " . ($offset + 1)
                    . " from " . $line . " to " . $lines[$offset],
                    Project::MSG_VERBOSE
                );
            }
        }

        $escaped = implode(PHP_EOL, $lines);

        return $escaped;
    }

    /**
     * Creates a new EscapeUnicode using the passed in
     * Reader for instantiation.
     *
     * @param rdr A Reader object providing the underlying stream.
     *            Must not be <code>null</code>.
     *
     * @return a new filter based on this configuration, but filtering
     *         the specified reader
     */
    public function chain(Reader $rdr)
    {
        $newFilter = new EscapeUnicode($rdr);
        $newFilter->setInitialized(true);
        $newFilter->setProject($this->getProject());

        return $newFilter;
    }

    /**
     * Parses the parameters (currently unused)
     */
    private function initialize()
    {
    }
}
