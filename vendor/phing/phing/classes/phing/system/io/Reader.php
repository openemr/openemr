<?php
/*
 *  $Id: 7acaed79c1abfad8021979dc7c81d086dfb58e3a $
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
 * Abstract class for reading character streams.
 *
 * @author Hans Lellelid <hans@xmpl.org>
 * @author Yannick Lecaillez <yl@seasonfive.com>
 * @version $Id: 7acaed79c1abfad8021979dc7c81d086dfb58e3a $
 * @package phing.system.io
 */
abstract class Reader
{

    /**
     * Read data from source.
     *
     * If length is specified, then only that number of chars is read,
     * otherwise stream is read until EOF.
     *
     * @param int $len
     */
    abstract public function read($len = null);

    /**
     * Close stream.
     * @throws IOException if there is an error closing stream
     */
    abstract public function close();

    /**
     * Returns the filename, url, etc. that is being read from.
     * This is critical for, e.g., ExpatParser's ability to know
     * the filename that is throwing an ExpatParserException, etc.
     * @return string
     */
    abstract public function getResource();

    /**
     * Move stream position relative to current pos.
     * @param int $n
     */
    public function skip($n)
    {
    }

    /**
     * Reset the current position in stream to beginning or last mark (if supported).
     */
    public function reset()
    {
    }

    /**
     * If supported, places a "marker" (like a bookmark) at current stream position.
     * A subsequent call to reset() will move stream position back
     * to last marker (if supported).
     */
    public function mark()
    {
    }

    /**
     * Whether marking is supported.
     * @return boolean
     */
    public function markSupported()
    {
        return false;
    }

    /**
     * Is stream ready for reading.
     * @return boolean
     */
    public function ready()
    {
        return true;
    }

}
