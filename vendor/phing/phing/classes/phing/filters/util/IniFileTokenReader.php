<?php
/*
 *  $Id: 1787bbe5c2ac3fc35faf21652eed36899bff61b6 $
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

include_once 'phing/types/TokenReader.php';
include_once 'phing/system/io/IOException.php';
include_once 'phing/filters/ReplaceTokens.php'; // For class Token

/**
 * Class that allows reading tokens from INI files.
 *
 * @author    Manuel Holtgewe
 * @version   $Id: 1787bbe5c2ac3fc35faf21652eed36899bff61b6 $
 * @package   phing.filters.util
 */
class IniFileTokenReader extends TokenReader
{

    /**
     * Holds the path to the INI file that is to be read.
     * @var object  Reference to a PhingFile Object representing
     *              the path to the INI file.
     */
    private $file = null;

    /**
     * @var string  Sets the section to load from the INI file.
     *              if omitted, all sections are loaded.
     */
    private $section = null;

    /**
     * @var array
     */
    private $tokens = null;

    /**
     * Reads the next token from the INI file
     *
     * @throws BuildException
     * @return Token
     */
    public function readToken()
    {
        if ($this->file === null) {
            throw new BuildException("No File set for IniFileTokenReader");
        }

        if ($this->tokens === null) {
            $this->processFile();
        }

        if (count($this->tokens) > 0) {
            return array_pop($this->tokens);
        } else {
            return null;
        }
    }

    /**
     * Parse & process the ini file
     */
    protected function processFile()
    {
        $arr = parse_ini_file($this->file->getAbsolutePath(), true);

        if ($this->section !== null) {
            if (isset($arr[$this->section])) {
                $this->processSection($arr[$this->section]);
            }

            return;
        }

        $values = array_values($arr);

        if (!is_array($values[0])) {
            $this->processSection($arr);

            return;
        }

        foreach ($values as $subArr) {
            $this->processSection($subArr);
        }
    }

    /**
     * Process an individual section
     *
     * @param array $section
     */
    protected function processSection(array $section)
    {
        foreach ($section as $key => $value) {
            $tok = new Token();
            $tok->setKey($key);
            $tok->setValue($value);
            $this->tokens[] = $tok;
        }
    }

    /**
     * @param string|PhingFile $file
     * @throws BuildException
     */
    public function setFile($file)
    {
        if (is_string($file)) {
            $this->file = new PhingFile($file);

            return;
        }

        if (is_object($file) && $file instanceof PhingFile) {
            $this->file = $file;

            return;
        }

        throw new BuildException("Unsupported value " . (string) $file);
    }

    /**
     * @param $str
     */
    public function setSection($str)
    {
        $this->section = (string) $str;
    }
}
