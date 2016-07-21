<?php
/*
 *  $Id: 860064af39647023c2644e87267184447ceb93ae $
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

include_once 'phing/system/io/Reader.php';

/**
 * Convenience class for reading files.
 *
 * @author    <a href="mailto:yl@seasonfive.com">Yannick Lecaillez</a>
 *
 * @see       FilterReader
 *
 * @package   phing.system.io
 */
class BufferedReader extends Reader
{

    private $bufferSize = 0;
    private $buffer = null;
    private $bufferPos = 0;

    /**
     * The Reader we are buffering for.
     */
    private $in;

    /**
     *
     * @param Reader  $reader   The reader (e.g. FileReader).
     * @param integer $buffsize The size of the buffer we should use for reading files.
     *                          A large buffer ensures that most files (all scripts?) are parsed in 1 buffer.
     */
    public function __construct(Reader $reader, $buffsize = 65536)
    {
        $this->in = $reader;
        $this->bufferSize = $buffsize;
    }

    /**
     * Reads and returns a chunk of data.
     * @param  int   $len Number of bytes to read.  Default is to read configured buffer size number of bytes.
     * @return mixed buffer or -1 if EOF.
     */
    public function read($len = null)
    {

        // if $len is specified, we'll use that; otherwise, use the configured buffer size.
        if ($len === null) {
            $len = $this->bufferSize;
        }

        if (($data = $this->in->read($len)) !== -1) {

            // not all files end with a newline character, so we also need to check EOF
            if (!$this->in->eof()) {

                $notValidPart = strrchr($data, "\n");
                $notValidPartSize = strlen($notValidPart);

                if ($notValidPartSize > 1) {
                    // Block doesn't finish on a EOL
                    // Find the last EOL and forget all following stuff
                    $dataSize = strlen($data);
                    $validSize = $dataSize - $notValidPartSize + 1;

                    $data = substr($data, 0, $validSize);

                    // Rewind to the beginning of the forgotten stuff.
                    $this->in->skip(-$notValidPartSize + 1);
                }

            } // if !EOF
        }

        return $data;
    }

    /**
     * @param int $n
     */
    public function skip($n)
    {
        return $this->in->skip($n);
    }

    public function reset()
    {
        return $this->in->reset();
    }

    /**
     * @return mixed
     */
    public function close()
    {
        return $this->in->close();
    }

    /**
     * @return mixed
     */
    public function open()
    {
        return $this->in->open();
    }

    /**
     * Read a line from input stream.
     */
    public function readLine()
    {
        $line = null;
        while (($ch = $this->readChar()) !== -1) {
            if ($ch === "\n") {
                break;
            }
            $line .= $ch;
        }

        // Warning : Not considering an empty line as an EOF
        if ($line === null && $ch !== -1) {
            return "";
        }

        return $line;
    }

    /**
     * Reads a single char from the reader.
     * @return string single char or -1 if EOF.
     */
    public function readChar()
    {

        if ($this->buffer === null) {
            // Buffer is empty, fill it ...
            $read = $this->in->read($this->bufferSize);
            if ($read === -1) {
                $ch = -1;
            } else {
                $this->buffer = $read;

                return $this->readChar(); // recurse
            }
        } else {
            // Get next buffered char ...
            // handle case where buffer is read-in, but is empty.  The next readChar() will return -1 EOF,
            // so we just return empty string (char) at this point.  (Probably could also return -1 ...?)
            $ch = ($this->buffer !== "") ? $this->buffer{$this->bufferPos} : '';
            $this->bufferPos++;
            if ($this->bufferPos >= strlen($this->buffer)) {
                $this->buffer = null;
                $this->bufferPos = 0;
            }
        }

        return $ch;
    }

    /**
     * Returns whether eof has been reached in stream.
     * This is important, because filters may want to know if the end of the file (and not just buffer)
     * has been reached.
     * @return boolean
     */
    public function eof()
    {
        return $this->in->eof();
    }

    /**
     * @return string
     */
    public function getResource()
    {
        return $this->in->getResource();
    }
}
