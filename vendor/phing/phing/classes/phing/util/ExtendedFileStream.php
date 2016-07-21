<?php

include_once 'phing/system/io/PhingFile.php';

/**
 * $Id: 9f3deadd6a2b93620d27fe8959f9f688b4c58171 $
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
 * Extended file stream wrapper class which auto-creates directories
 *
 * @author Michiel Rook <mrook@php.net>
 * @version $Id: 9f3deadd6a2b93620d27fe8959f9f688b4c58171 $
 * @package phing.util
 */
class ExtendedFileStream
{
    private $fp = null;

    public static function registerStream()
    {
        if (!in_array("efile", stream_get_wrappers())) {
            stream_wrapper_register("efile", "ExtendedFileStream");
        }
    }

    public static function unregisterStream()
    {
        stream_wrapper_unregister("efile");
    }

    /**
     * @param $path
     */
    private function createDirectories($path)
    {
        $f = new PhingFile($path);
        if (!$f->exists()) {
            $f->mkdirs();
        }
    }

    /**
     * @param $path
     * @param $mode
     * @param $options
     * @param $opened_path
     * @return bool
     * @throws IOException
     */
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        // if we're on Windows, urldecode() the path again
        if (FileSystem::getFileSystem()->getSeparator() == '\\') {
            $path = urldecode($path);
        }

        $filepath = substr($path, 8);

        $this->createDirectories(dirname($filepath));

        $this->fp = fopen($filepath, $mode);

        if (! $this->fp) {
            throw new BuildException("Unable to open stream for path {$path}");
        }

        return true;
    }

    public function stream_close()
    {
        fclose($this->fp);
        $this->fp = null;
    }

    /**
     * @param $count
     * @return string
     */
    public function stream_read($count)
    {
        return fread($this->fp, $count);
    }

    /**
     * @param $data
     * @return int
     */
    public function stream_write($data)
    {
        return fwrite($this->fp, $data);
    }

    /**
     * @return bool
     */
    public function stream_eof()
    {
        return feof($this->fp);
    }

    /**
     * @return int
     */
    public function stream_tell()
    {
        return ftell($this->fp);
    }

    /**
     * @param $offset
     * @param $whence
     * @return int
     */
    public function stream_seek($offset, $whence)
    {
        return fseek($this->fp, $offset, $whence);
    }

    /**
     * @return bool
     */
    public function stream_flush()
    {
        return fflush($this->fp);
    }

    /**
     * @return array
     */
    public function stream_stat()
    {
        return fstat($this->fp);
    }

    /**
     * @param $path
     * @return bool
     */
    public function unlink($path)
    {
        return false;
    }

    /**
     * @param $path_from
     * @param $path_to
     * @return bool
     */
    public function rename($path_from, $path_to)
    {
        return false;
    }

    /**
     * @param $path
     * @param $mode
     * @param $options
     * @return bool
     */
    public function mkdir($path, $mode, $options)
    {
        return false;
    }

    /**
     * @param $path
     * @param $options
     * @return bool
     */
    public function rmdir($path, $options)
    {
        return false;
    }
}

;
