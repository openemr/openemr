<?php

/*
 *  $Id $
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

include_once 'phing/filters/BaseFilterReader.php';
include_once 'phing/filters/ChainableReader.php';

/**
 * Strips whitespace from [php] files using PHP stripwhitespace() method.
 *
 * @author    Hans Lellelid, hans@velum.net
 * @version   $Id: 9e156ae0693bd973b8e0f89490814171365197da $
 * @see       FilterReader
 * @package   phing.filters
 * @todo -c use new PHP functions to perform this instead of regex.
 */
class StripWhitespace extends BaseFilterReader implements ChainableReader
{

    private $processed = false;

    /**
     * Returns the  stream without Php comments and whitespace.
     *
     * @param null $len
     * @return the resulting stream, or -1
     *             if the end of the resulting stream has been reached
     *
     */
    public function read($len = null)
    {

        if ($this->processed === true) {
            return -1; // EOF
        }

        // Read XML
        $php = null;
        while (($buffer = $this->in->read($len)) !== -1) {
            $php .= $buffer;
        }

        if ($php === null) { // EOF?

            return -1;
        }

        if (empty($php)) {
            $this->log("PHP file is empty!", Project::MSG_WARN);

            return ''; // return empty string, don't attempt to strip whitespace
        }

        // write buffer to a temporary file, since php_strip_whitespace() needs a filename
        $file = new PhingFile(tempnam(PhingFile::getTempDir(), 'stripwhitespace'));
        file_put_contents($file->getAbsolutePath(), $php);
        $output = php_strip_whitespace($file->getAbsolutePath());
        unlink($file->getAbsolutePath());

        $this->processed = true;

        return $output;
    }

    /**
     * Creates a new StripWhitespace using the passed in
     * Reader for instantiation.
     *
     * @param A|Reader $reader
     * @internal param A $reader Reader object providing the underlying stream.
     *               Must not be <code>null</code>.
     *
     * @return a new filter based on this configuration, but filtering
     *           the specified reader
     */
    public function chain(Reader $reader)
    {
        $newFilter = new StripWhitespace($reader);
        $newFilter->setProject($this->getProject());

        return $newFilter;
    }
}
