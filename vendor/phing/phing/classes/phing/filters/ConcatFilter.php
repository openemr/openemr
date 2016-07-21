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

include_once 'phing/filters/BaseParamFilterReader.php';
include_once 'phing/filters/ChainableReader.php';
include_once 'phing/system/io/PhingFile.php';
include_once 'phing/system/io/BufferedReader.php';
include_once 'phing/system/io/FileReader.php';

/**
 * Concats a file before and/or after the file.
 *
 * Example:
 * ```
 * <copy todir="build">
 *     <fileset dir="src" includes="*.php"/>
 *     <filterchain>
 *         <concatfilter prepend="license.txt"/>
 *     </filterchain>
 * </copy>
 * ```
 *
 * Copies all php sources from `src` to `build` and adds the
 * content of `license.txt` add the beginning of each
 * file.
 *
 * @author  Siad.ardroumli <siad.ardroumli@gmail.com>
 * @package phing.filters
 */
class ConcatFilter extends BaseParamFilterReader implements ChainableReader
{
    /**
     * File to add before the content.
     *
     * @var PhingFile $prepend
     */
    private $prepend;

    /**
     * File to add after the content.
     *
     * @var PhingFile $append
     */
    private $append;

    /**
     * Reader for prepend-file.
     * @var BufferedReader
     */
    private $prependReader;

    /**
     * Reader for append-file.
     * @var BufferedReader
     */
    private $appendReader;

    /**
     * @param Reader $in
     */
    public function __construct(Reader $in = null)
    {
        parent::__construct($in);
    }

    /**
     * Returns the next character in the filtered stream. If the desired
     * number of lines have already been read, the resulting stream is
     * effectively at an end. Otherwise, the next character from the
     * underlying stream is read and returned.
     *
     * @param int $len
     * @return int|string the next character in the resulting stream, or -1
     * if the end of the resulting stream has been reached
     *
     * @throws IOException if the underlying stream throws an IOException
     *                     during reading
     * @throws BuildException
     */
    public function read($len = 0)
    {
        // do the "singleton" initialization
        if (!$this->getInitialized()) {
            $this->initialize();
            $this->setInitialized(true);
        }

        $ch = -1;

        // The readers return -1 if they end. So simply read the "prepend"
        // after that the "content" and at the end the "append" file.
        if ($this->prependReader !== null) {
            $ch = $this->prependReader->read();
            if ($ch === -1) {
                // I am the only one so I have to close the reader
                $this->prependReader->close();
                $this->prependReader = null;
            }
        }
        if ($ch === -1) {
            $ch = parent::read();
        }
        if ($ch === -1 && $this->appendReader !== null) {
            $ch = $this->appendReader->read();
            if ($ch === -1) {
                // I am the only one so I have to close the reader
                $this->appendReader->close();
                $this->appendReader = null;
            }
        }

        return $ch;
    }

    /**
     * Scans the parameters list for the "lines" parameter and uses
     * it to set the number of lines to be returned in the filtered stream.
     * also scan for skip parameter.
     *
     * @throws BuildException
     */
    private function initialize()
    {
        // get parameters
        $params = $this->getParameters();
        if ($params !== null) {
            /** @var Parameter $param */
            foreach ($params as $param) {
                if ('prepend' === $param->getName()) {
                    $this->setPrepend(new PhingFile($param->getValue()));
                    continue;
                }
                if ('append' === $param->getName()) {
                    $this->setAppend(new PhingFile($param->getValue()));
                    continue;
                }
            }
        }
        if ($this->prepend !== null) {
            if (!$this->prepend->isAbsolute()) {
                $this->prepend = new PhingFile($this->getProject()->getBasedir(), $this->prepend->getPath());
            }
            $this->prependReader = new BufferedReader(new FileReader($this->prepend));
        }
        if ($this->append !== null) {
            if (!$this->append->isAbsolute()) {
                $this->append = new PhingFile($this->getProject()->getBasedir(), $this->append->getPath());
            }
            $this->appendReader = new BufferedReader(new FileReader($this->append));
        }
    }

    /**
     * Creates a new ConcatReader using the passed in
     * Reader for instantiation.
     *
     * @param Reader $rdr A Reader object providing the underlying stream.
     *                    Must not be <code>null</code>.
     *
     * @return ConcatFilter a new filter based on this configuration, but filtering
     *                      the specified reader
     */
    public function chain(Reader $rdr)
    {
        $newFilter = new ConcatFilter($rdr);
        $newFilter->setProject($this->getProject());
        $newFilter->setPrepend($this->getPrepend());
        $newFilter->setAppend($this->getAppend());

        return $newFilter;
    }

    /**
     * Returns `prepend` attribute.
     * @return PhingFile prepend attribute
     */
    public function getPrepend()
    {
        return $this->prepend;
    }

    /**
     * Sets `prepend` attribute.
     * @param PhingFile|string prepend new value
     */
    public function setPrepend($prepend)
    {
        if ($prepend instanceof PhingFile) {
            $this->prepend = $prepend;
        } else {
            $this->prepend = new PhingFile($prepend);
        }
    }

    /**
     * Returns `append` attribute.
     * @return PhingFile append attribute
     */
    public function getAppend()
    {
        return $this->append;
    }

    /**
     * Sets `append` attribute.
     * @param PhingFile|string append new value
     */
    public function setAppend($append)
    {
        $this->append = $append;
    }
}
