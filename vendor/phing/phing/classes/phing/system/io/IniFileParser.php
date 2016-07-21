<?php
/*
 *  $Id: 2611d65fac6d1b7d90d8d6ae76e4afbfe36c1d58 $
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
include_once 'phing/system/io/FileParserInterface.php';

/**
 * Implements an IniFileParser. The logic is coming from th Properties.php, but I don't know who's the author.
 *
 * FIXME
 *  - Add support for arrays (separated by ',')
 *
 * @author Mike Lohmann <mike.lohmann@deck36.de>
 * @package phing.system.io
 */
class IniFileParser implements FileParserInterface
{
    /**
     * {@inheritDoc}
     */
    public function parseFile(PhingFile $file)
    {
        if (($lines = @file($file, FILE_IGNORE_NEW_LINES)) === false) {
            throw new IOException("Unable to parse contents of $file");
        }

        // concatenate lines ending with backslash
        $linesCount = count($lines);
        for ($i = 0; $i < $linesCount; $i++) {
            if (substr($lines[$i], -1, 1) === '\\') {
                $lines[$i + 1] = substr($lines[$i], 0, -1) . ltrim($lines[$i + 1]);
                $lines[$i] = '';
            }
        }

        $properties = array();
        foreach ($lines as $line) {
            // strip comments and leading/trailing spaces
            $line = trim(preg_replace("/\s+[;#]\s.+$/", "", $line));

            if (empty($line) || $line[0] == ';' || $line[0] == '#') {
                continue;
            }

            $pos = strpos($line, '=');
            $property = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));
            $properties[$property] = $this->inVal($value);

        } // for each line

        return $properties;
    }

    /**
     * Process values when being read in from properties file.
     * does things like convert "true" => true
     * @param string $val Trimmed value.
     * @return mixed The new property value (may be boolean, etc.)
     */
    protected function inVal($val)
    {
        if ($val === "true") {
            $val = true;
        } elseif ($val === "false") {
            $val = false;
        }
        return $val;
    }
}
