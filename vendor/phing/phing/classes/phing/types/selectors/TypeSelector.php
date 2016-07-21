<?php

/*
 * $Id: a2775fcd20fded1d1bf890e4d77286dd0325ef22 $
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

require_once 'phing/types/selectors/BaseExtendSelector.php';

/**
 * Selector that selects a certain kind of file: directory or regular file.
 *
 * @author    Hans Lellelid <hans@xmpl.org> (Phing)
 * @author    Jeff Turner <jefft@apache.org> (Ant)
 * @version   $Id: a2775fcd20fded1d1bf890e4d77286dd0325ef22 $
 * @package   phing.types.selectors
 */
class TypeSelector extends BaseExtendSelector
{

    private $type;

    /** Key to used for parameterized custom selector */
    const TYPE_KEY = "type";

    /** Valid types */
    private static $types = array('file', 'dir', 'link');

    /**
     * @return string A string describing this object
     */
    public function toString()
    {
        $buf = "{typeselector type: " . $this->type . "}";

        return $buf;
    }

    /**
     * Set the type of file to require.
     * @param string $type The type of file - 'file' or 'dir'
     */
    public function setType($type)
    {
        $this->type = $type;
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
                if (self::TYPE_KEY == strtolower($paramname)) {
                    $this->setType($parameters[$i]->getValue());
                } else {
                    $this->setError("Invalid parameter " . $paramname);
                }
            }
        }
    }

    /**
     * Checks to make sure all settings are kosher. In this case, it
     * means that the pattern attribute has been set.
     *
     */
    public function verifySettings()
    {
        if ($this->type === null) {
            $this->setError("The type attribute is required");
        } elseif (!in_array($this->type, self::$types, true)) {
            $this->setError("Invalid type specified; must be one of (" . implode(self::$types) . ")");
        }
    }

    /**
     * The heart of the matter. This is where the selector gets to decide
     * on the inclusion of a file in a particular fileset.
     *
     * @param  PhingFile $basedir  the base directory the scan is being done from
     * @param  string    $filename is the name of the file to check
     * @param  PhingFile $file     is a PhingFile object the selector can use
     * @return boolean   Whether the file should be selected or not
     */
    public function isSelected(PhingFile $basedir, $filename, PhingFile $file)
    {
        // throw BuildException on error
        $this->validate();

        if ($file->isLink()) {
            if ($this->type == 'link') {
                return true;
            }

            $this->log(
                $file->getAbsolutePath() . " is a link, proceeding with " . $file->getCanonicalPath() . " instead.",
                Project::MSG_DEBUG
            );
            $file = new PhingFile($file->getCanonicalPath());
        }

        if ($file->isDirectory()) {
            return $this->type === 'dir';
        } else {
            return $this->type === 'file';
        }
    }

}
