<?php
/*
 *  $Id: 0c7982f0908210ce92301f7f79ed35f8ad7cdbe2 $
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

include_once 'phing/system/io/PhingFile.php';
require_once 'phing/system/io/Writer.php';

/**
 * Writer class for OutputStream objects.
 *
 * Unlike the Java counterpart, this class does not (yet) handle
 * character set transformations.  This will be an important function
 * of this class with move to supporting PHP6.
 *
 * @package   phing.system.io
 */
class OutputStreamWriter extends Writer
{

    /**
     * @var OutputStream
     */
    protected $outStream;

    /**
     * Construct a new OutputStreamWriter.
     * @param OutputStream $outStream OutputStream to write to
     */
    public function __construct(OutputStream $outStream)
    {
        $this->outStream = $outStream;
    }

    /**
     * Close the stream.
     * @return void
     */
    public function close()
    {
        $this->outStream->close();
    }

    /**
     * Write char data to stream.
     *
     * @param  string $buf
     * @param  int $off
     * @param  int $len
     *
     * @return void
     */
    public function write($buf, $off = null, $len = null)
    {
        return $this->outStream->write($buf, $off, $len);
    }

    /**
     * Flush output to the stream.
     */
    public function flush()
    {
        $this->outStream->flush();
    }

    /**
     * Gets a string representation of attached stream resource.
     *
     * @return string String representation of output stream
     */
    public function getResource()
    {
        return $this->outStream->__toString();
    }
}
