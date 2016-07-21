<?php

/*
 * $Id: eb9a1bff3c3a35a383e003cae076c0ca3e0d8ac7 $
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

include_once 'phing/types/selectors/BaseExtendSelector.php';

/**
 * Selector that filters files based on whether they contain a
 * particular string.
 *
 * @author Hans Lellelid <hans@xmpl.org> (Phing)
 * @author Bruce Atherton <bruce@callenish.com> (Ant)
 * @package phing.types.selectors
 */
class ContainsSelector extends BaseExtendSelector
{

    private $contains = null;
    private $casesensitive = true;
    const CONTAINS_KEY = "text";
    const CASE_KEY = "casesensitive";

    /**
     * @return string
     */
    public function toString()
    {
        $buf = "{containsselector text: ";
        $buf .= $this->contains;
        $buf .= " casesensitive: ";
        if ($this->casesensitive) {
            $buf .= "true";
        } else {
            $buf .= "false";
        }
        $buf .= "}";

        return $buf;
    }

    /**
     * The string to search for within a file.
     *
     * @param string $contains the string that a file must contain to be selected.
     */
    public function setText($contains)
    {
        $this->contains = $contains;
    }

    /**
     * Whether to ignore case in the string being searched.
     *
     * @param boolean $casesensitive whether to pay attention to case sensitivity
     */
    public function setCasesensitive($casesensitive)
    {
        $this->casesensitive = $casesensitive;
    }

    /**
     * When using this as a custom selector, this method will be called.
     * It translates each parameter into the appropriate setXXX() call.
     *
     * @param array $parameters the complete set of parameters for this selector
     * @return mixed|void
     */
    public function setParameters($parameters)
    {
        parent::setParameters($parameters);
        if ($parameters !== null) {
            for ($i = 0, $size = count($parameters); $i < $size; $i++) {
                $paramname = $parameters[$i]->getName();
                switch (strtolower($paramname)) {
                    case self::CONTAINS_KEY:
                        $this->setText($parameters[$i]->getValue());
                        break;
                    case self::CASE_KEY:
                        $this->setCasesensitive($parameters[$i]->getValue());
                        break;
                    default:
                        $this->setError("Invalid parameter " . $paramname);
                }
            } // for each param
        } // if params
    }

    /**
     * Checks to make sure all settings are kosher. In this case, it
     * means that the pattern attribute has been set.
     *
     */
    public function verifySettings()
    {
        if ($this->contains === null) {
            $this->setError("The text attribute is required");
        }
    }

    /**
     * The heart of the matter. This is where the selector gets to decide
     * on the inclusion of a file in a particular fileset.
     *
     * @param PhingFile $basedir
     * @param string $filename
     * @param PhingFile $file
     *
     * @throws BuildException
     *
     * @internal param the $basedir base directory the scan is being done from
     * @internal param is $filename the name of the file to check
     * @internal param a $file PhingFile object the selector can use
     *
     * @return bool whether the file should be selected or not
     */
    public function isSelected(PhingFile $basedir, $filename, PhingFile $file)
    {

        $this->validate();

        if ($file->isDirectory()) {
            return true;
        }

        $userstr = $this->contains;
        if (!$this->casesensitive) {
            $userstr = strtolower($this->contains);
        }

        $in = null;
        try {
            $in = new BufferedReader(new FileReader($file));
            $teststr = $in->readLine();
            while ($teststr !== null) {
                if (!$this->casesensitive) {
                    $teststr = strtolower($teststr);
                }
                if (strpos($teststr, $userstr) !== false) {
                    return true;
                }
                $teststr = $in->readLine();
            }

            $in->close();

            return false;
        } catch (IOException $ioe) {
            if ($in) {
                $in->close();
            }
            throw new BuildException("Could not read file " . $filename);
        }
    }
}
