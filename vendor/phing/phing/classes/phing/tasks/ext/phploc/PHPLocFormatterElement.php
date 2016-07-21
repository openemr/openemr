<?php
/**
 * $Id: 5911dc47d5037c87f6c0c11d815c05361cad9bc0 $
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
 * @author Michiel Rook <mrook@php.net>
 * @version $Id: 5911dc47d5037c87f6c0c11d815c05361cad9bc0 $
 * @package phing.tasks.ext.phploc
 */
class PHPLocFormatterElement
{
    /**
     * @var string
     */
    protected $type = "";

    /**
     * @var bool
     */
    protected $useFile = true;

    /**
     * @var string
     */
    protected $toDir = ".";

    /**
     * @var string
     */
    protected $outfile = "phploc-report";

    /**
     * Loads a specific formatter type
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets whether to store formatting results in a file
     * @param $useFile
     */
    public function setUseFile($useFile)
    {
        $this->useFile = $useFile;
    }

    /**
     * Returns whether to store formatting results in a file
     */
    public function getUseFile()
    {
        return $this->useFile;
    }

    /**
     * Sets output directory
     * @param string $toDir
     */
    public function setToDir($toDir)
    {
        $this->toDir = $toDir;
    }

    /**
     * Returns output directory
     * @return string
     */
    public function getToDir()
    {
        return $this->toDir;
    }

    /**
     * Sets output filename
     * @param string $outfile
     */
    public function setOutfile($outfile)
    {
        $this->outfile = $outfile;
    }

    /**
     * Returns output filename
     * @return string
     */
    public function getOutfile()
    {
        return $this->outfile;
    }
}
