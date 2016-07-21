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

include_once 'phing/system/io/PhingFile.php';
include_once 'phing/system/io/Reader.php';

/**
 * Writer class for OutputStream objects.
 *
 * Unlike the Java counterpart, this class does not (yet) handle
 * character set transformations.  This will be an important function
 * of this class with move to supporting PHP6.
 *
 * @package   phing.system.io
 */
class InputStreamReader extends Reader
{

    /**
     * @var InputStream
     */
    protected $inStream;

    /**
     * Construct a new InputStreamReader.
     * @param InputStream $inStream
     * @internal param $InputStream $$inStream InputStream to read from
     */
    public function __construct(InputStream $inStream)
    {
        $this->inStream = $inStream;
    }

    /**
     * Close the stream.
     */
    public function close()
    {
        return $this->inStream->close();
    }

    /**
     * Skip over $n bytes.
     * @param int $n
     * @return int|void
     */
    public function skip($n)
    {
        return $this->inStream->skip($n);
    }

    /**
     * Read data from file.
     * @param  int    $len Num chars to read.
     * @return string chars read or -1 if eof.
     */
    public function read($len = null)
    {
        return $this->inStream->read($len);
    }

    /**
     * Marks the current position in this input stream.
     * @throws IOException - if the underlying stream doesn't support this method.
     */
    public function mark()
    {
        $this->inStream->mark();
    }

    /**
     * Whether the attached stream supports mark/reset.
     * @return boolean
     */
    public function markSupported()
    {
        return $this->inStream->markSupported();
    }

    /**
     * Repositions this stream to the position at the time the mark method was last called on this input stream.
     * @throws IOException - if the underlying stream doesn't support this method.
     */
    public function reset()
    {
        $this->inStream->reset();
    }

    /**
     * Whether eof has been reached with stream.
     * @return boolean
     */
    public function eof()
    {
        return $this->inStream->eof();
    }

    /**
     * Reads a entire file and stores the data in the variable
     * passed by reference.
     *
     * @param object &$rBuffer Reference. Variable of where to put contents.
     *
     * @return bool TRUE on success. Err object on failure.
     * @author  Charlie Killian, charlie@tizac.com
     * @deprecated Use read() or BufferedReader instead.
     */
    public function readInto(&$rBuffer)
    {
        return $this->inStream->readInto($rBuffer);
    }

    /**
     * Returns string representation of attached stream.
     * @return string
     */
    public function getResource()
    {
        return $this->inStream->__toString();
    }
}
