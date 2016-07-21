<?php
/*
 *  $Id: 65f63c742244bc32b89cc48618b9f6a38a37eea1 $
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

/**
 * Wrapper class for PHP stream that supports read operations.
 *
 * @package   phing.system.io
 */
class InputStream
{

    /**
     * @var resource The attached PHP stream.
     */
    protected $stream;

    /**
     * @var int Position of stream cursor.
     */
    protected $currentPosition = 0;

    /**
     * @var int Marked position of stream cursor.
     */
    protected $mark = 0;

    /**
     * Construct a new InputStream.
     * @param resource $stream Configured PHP stream for writing.
     * @throws IOException
     */
    public function __construct($stream)
    {
        if (!is_resource($stream)) {
            throw new IOException("Passed argument is not a valid stream.");
        }
        $this->stream = $stream;
    }

    /**
     * Skip over $n bytes.
     * @param int $n
     * @return int
     */
    public function skip($n)
    {
        $start = $this->currentPosition;

        $ret = @fseek($this->stream, $n, SEEK_CUR);
        if ($ret === -1) {
            return -1;
        }

        $this->currentPosition = ftell($this->stream);

        if ($start > $this->currentPosition) {
            $skipped = $start - $this->currentPosition;
        } else {
            $skipped = $this->currentPosition - $start;
        }

        return $skipped;
    }

    /**
     * Read data from stream until $len chars or EOF.
     * @param  int    $len Num chars to read.  If not specified this stream will read until EOF.
     * @return string chars read or -1 if eof.
     */
    public function read($len = null)
    {

        if ($this->eof()) {
            return -1;
        }

        if ($len === null) { // we want to keep reading until we get an eof
            $out = "";
            while (!$this->eof()) {
                $out .= fread($this->stream, 8192);
                $this->currentPosition = ftell($this->stream);
            }
        } else {
            $out = fread($this->stream, $len); // adding 1 seems to ensure that next call to read() will return EOF (-1)
            $this->currentPosition = ftell($this->stream);
        }

        return $out;
    }

    /**
     * Marks the current position in this input stream.
     * @throws IOException - if the underlying stream doesn't support this method.
     */
    public function mark()
    {
        if (!$this->markSupported()) {
            throw new IOException(get_class($this) . " does not support mark() and reset() methods.");
        }
        $this->mark = $this->currentPosition;
    }

    /**
     * Whether the input stream supports mark and reset methods.
     * @return boolean
     */
    public function markSupported()
    {
        return false;
    }

    /**
     * Repositions this stream to the position at the time the mark method was last called on this input stream.
     * @throws IOException - if the underlying stream doesn't support this method.
     */
    public function reset()
    {
        if (!$this->markSupported()) {
            throw new IOException(get_class($this) . " does not support mark() and reset() methods.");
        }
        // goes back to last mark, by default this would be 0 (i.e. rewind file).
        fseek($this->stream, SEEK_SET, $this->mark);
        $this->mark = 0;
    }

    /**
     * Closes stream.
     * @throws IOException if stream cannot be closed (note that calling close() on an already-closed stream will not raise an exception)
     */
    public function close()
    {
        if ($this->stream === null) {
            return;
        }
        if (false === @fclose($this->stream)) {
            // FAILED.
            $msg = "Cannot fclose " . $this->file->__toString() . " $php_errormsg";
            throw new IOException($msg);
        }
        $this->stream = null;
    }

    /**
     * Whether eof has been reached with stream.
     * @return boolean
     */
    public function eof()
    {
        return feof($this->stream);
    }

    /**
     * Reads a entire until EOF and places contents in passed-in variable.  Stream is closed after read.
     *
     * @param  string      &$rBuffer String variable where read contents will be put.
     * @return TRUE        on success.
     * @author  Charlie Killian, charlie@tizac.com
     * @throws IOException - if there is an error reading from stream.
     * @deprecated - Instead, use the read() method or a BufferedReader.
     */
    public function readInto(&$rBuffer)
    {
        $rBuffer = $this->read();
        $this->close();
    }

    /**
     * Returns string representation of attached stream.
     * @return string
     */
    public function __toString()
    {
        return (string) $this->stream;
    }
}
