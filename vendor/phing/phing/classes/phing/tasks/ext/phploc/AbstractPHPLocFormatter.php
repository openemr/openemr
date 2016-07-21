<?php

/**
 * $Id: a2989958865651cca9c94d8b17c5ecf041f78614 $
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

/**
 * @author Michiel Rook <mrook@php.net>
 * @version $Id: a2989958865651cca9c94d8b17c5ecf041f78614 $
 * @package phing.tasks.ext.phploc
 */
abstract class AbstractPHPLocFormatter
{
    /**
     * @param array $count
     * @param bool $countTests
     * @return mixed
     */
    abstract public function printResult(array $count, $countTests = false);

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
    protected $outfile = "";

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
        if (!is_dir($toDir)) {
            $toDir = new PhingFile($toDir);
            $toDir->mkdirs();
        }

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