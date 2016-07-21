<?php

/*
 *  $Id: 33628948aa5dc0ffef8f645df415c37b4a6268f2 $
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

require_once 'phing/system/io/PhingFile.php';
require_once 'phing/system/io/FileWriter.php';

/**
 * An abstract representation of file and directory pathnames.
 *
 * @package phing.util
 * @author  Michiel Rook <mrook@php.net>
 * @version $Id: 33628948aa5dc0ffef8f645df415c37b4a6268f2 $
 */
class DataStore
{
    private $data = array();
    private $file = null;

    /**
     * Constructs a new data store
     *
     * @param PhingFile $file object pointing to the data store on disk
     */
    public function __construct(PhingFile $file)
    {
        $this->file = $file;

        if ($this->file->exists()) {
            $this->read();
        }
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->commit();
    }

    /**
     * Retrieves a value from the data store
     *
     * @param string $key the key
     *
     * @return mixed the value
     */
    public function get($key)
    {
        if (!isset($this->data[$key])) {
            return null;
        } else {
            return $this->data[$key];
        }
    }

    /**
     * Adds a value to the data store
     *
     * @param string  $key        the key
     * @param mixed   $value      the value
     * @param boolean $autocommit whether to auto-commit (write)
     *                            the data store to disk
     *
     * @return none
     */
    public function put($key, $value, $autocommit = false)
    {
        $this->data[$key] = $value;

        if ($autocommit) {
            $this->commit();
        }
    }

    /**
     * Remove a value from the data store
     *
     * @param string  $key        the key
     * @param boolean $autocommit whether to auto-commit (write)
     *                            the data store to disk
     */
    public function remove($key, $autocommit = false)
    {
        unset($this->data[$key]);

        if ($autocommit) {
            $this->commit();
        }
    }

    /**
     * Commits data store to disk
     *
     * @return none
     */
    public function commit()
    {
        $this->write();
    }

    /**
     * Internal function to read data store from file
     *
     * @throws BuildException
     * @return none
     */
    private function read()
    {
        if (!$this->file->canRead()) {
            throw new BuildException("Can't read data store from '" .
                $this->file->getPath() . "'");
        } else {
            $serializedData = $this->file->contents();

            $this->data = unserialize($serializedData);
        }
    }

    /**
     * Internal function to write data store to file
     *
     * @throws BuildException
     * @return none
     */
    private function write()
    {
        if (!$this->file->canWrite()) {
            throw new BuildException("Can't write data store to '" .
                $this->file->getPath() . "'");
        } else {
            $serializedData = serialize($this->data);

            $writer = new FileWriter($this->file);
            $writer->write($serializedData);
            $writer->close();
        }
    }
}

;
