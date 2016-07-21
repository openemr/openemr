<?php
/*
 *  $Id: d60cc0674b74f234c03334f5c60905c44de64476 $
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

require_once 'phing/system/io/InputStream.php';
require_once 'phing/system/io/PhingFile.php';

/**
 * Input stream subclass for file streams.
 *
 * @package   phing.system.io
 */
class FileInputStream extends InputStream
{

    /**
     * The associated file.
     * @var PhingFile
     */
    protected $file;

    /**
     * Construct a new FileInputStream.
     *
     * @param  PhingFile|string $file   Path to the file
     * @param  boolean          $append Whether to append (ignored)
     * @throws Exception        - if invalid argument specified.
     * @throws IOException      - if unable to open file.
     */
    public function __construct($file, $append = false)
    {
        if ($file instanceof PhingFile) {
            $this->file = $file;
        } elseif (is_string($file)) {
            $this->file = new PhingFile($file);
        } else {
            throw new Exception("Invalid argument type for \$file.");
        }

        $stream = @fopen($this->file->getAbsolutePath(), "rb");
        if ($stream === false) {
            throw new IOException("Unable to open " . $this->file->__toString() . " for reading: " . $php_errormsg);
        }

        parent::__construct($stream);
    }

    /**
     * Returns a string representation of the attached file.
     * @return string
     */
    public function __toString()
    {
        return $this->file->getPath();
    }

    /**
     * Mark is supported by FileInputStream.
     * @return boolean TRUE
     */
    public function markSupported()
    {
        return true;
    }
}
