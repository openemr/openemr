<?php
/*
 *  $Id: 398bedf237ea340a7a02d4c77a480a4cd7a34e0c $
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
 * Convenience class for reading console input.
 *
 * @author Hans Lellelid <hans@xmpl.org>
 * @author Matthew Hershberger <matthewh@lightsp.com>
 * @version $Id: 398bedf237ea340a7a02d4c77a480a4cd7a34e0c $
 * @package phing.system.io
 */
class ConsoleReader extends Reader
{

    /**
     * @return string
     */
    public function readLine()
    {

        $out = fgets(STDIN); // note: default maxlen is 1kb
        $out = rtrim($out);

        return $out;
    }

    /**
     *
     * @param  int    $len Num chars to read.
     * @return string chars read or -1 if eof.
     */
    public function read($len = null)
    {

        $out = fread(STDIN, $len);

        return $out;
        // FIXME
        // read by chars doesn't work (yet?) with PHP stdin.  Maybe
        // this is just a language feature, maybe there's a way to get
        // ability to read chars w/o <enter> ?

    }

    public function close()
    {
        // STDIN is always open
    }

    public function open()
    {
        // STDIN is always open
    }

    /**
     * Whether eof has been reached with stream.
     * @return boolean
     */
    public function eof()
    {
        return feof(STDIN);
    }

    /**
     * Returns path to file we are reading.
     * @return string
     */
    public function getResource()
    {
        return "console";
    }
}
