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

/**
 * Wrapper class for PHP stream that supports write operations.
 *
 * @package   phing.system.io
 */
class OutputStream
{
    /**
     * @var resource The configured PHP stream.
     */
    protected $stream;

    /**
     * Construct a new OutputStream.
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
     * Closes attached stream, flushing output first.
     * @throws IOException if cannot close stream (note that attempting to close an already closed stream will not raise an IOException)
     * @return void
     */
    public function close()
    {
        if ($this->stream === null) {
            return;
        }
        $this->flush();
        if (false === @fclose($this->stream)) {
            $metaData = stream_get_meta_data($this->stream);
            $resource = $metaData["uri"];
            $msg = "Cannot close " . $resource . ": $php_errormsg";
            throw new IOException($msg);
        }
        $this->stream = null;
    }

    /**
     * Flushes stream.
     *
     * @throws IOException if unable to flush data (e.g. stream is not open).
     */
    public function flush()
    {
        if (false === @fflush($this->stream)) {
            throw new IOException("Could not flush stream: " . $php_errormsg);
        }
    }

    /**
     * Writes data to stream.
     *
     * @param  string      $buf Binary/character data to write.
     * @param  int         $off (Optional) offset.
     * @param  int         $len (Optional) number of bytes/chars to write.
     * @return void
     * @throws IOException - if there is an error writing to stream
     */
    public function write($buf, $off = null, $len = null)
    {
        if ($off === null && $len === null) {
            $to_write = $buf;
        } elseif ($off !== null && $len === null) {
            $to_write = substr($buf, $off);
        } elseif ($off === null && $len !== null) {
            $to_write = substr($buf, 0, $len);
        } else {
            $to_write = substr($buf, $off, $len);
        }

        $result = @fwrite($this->stream, $to_write);

        if ($result === false) {
            throw new IOException("Error writing to stream.");
        }
    }

    /**
     * Returns a string representation of the attached PHP stream.
     * @return string
     */
    public function __toString()
    {
        return (string) $this->stream;
    }
}
