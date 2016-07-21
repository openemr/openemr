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

require_once 'phing/types/selectors/BaseExtendSelector.php';

/**
 * Selector that filters files based on the how deep in the directory
 * tree they are.
 *
 * @author    Hans Lellelid <hans@xmpl.org> (Phing)
 * @author    Bruce Atherton <bruce@callenish.com> (Ant)
 *
 * @package   phing.types.selectors
 */
class DepthSelector extends BaseExtendSelector
{
    /** @var int $min */
    public $min = -1;

    /** @var int $max */
    public $max = -1;

    const MIN_KEY = "min";
    const MAX_KEY = "max";

    /**
     * @return string
     */
    public function toString()
    {
        $buf = "{depthselector min: ";
        $buf .= $this->min;
        $buf .= " max: ";
        $buf .= $this->max;
        $buf .= "}";

        return $buf;
    }

    /**
     * The minimum depth below the basedir before a file is selected.
     *
     * @param int $min minimum directory levels below basedir to go
     *
     * @return void
     */
    public function setMin($min)
    {
        $this->min = (int) $min;
    }

    /**
     * The minimum depth below the basedir before a file is selected.
     *
     * @param int $max maximum directory levels below basedir to go
     *
     * @return void
     */
    public function setMax($max)
    {
        $this->max = (int) $max;
    }

    /**
     * When using this as a custom selector, this method will be called.
     * It translates each parameter into the appropriate setXXX() call.
     *
     * {@inheritdoc}
     *
     * @param array $parameters the complete set of parameters for this selector
     *
     * @return mixed|void
     */
    public function setParameters($parameters)
    {
        parent::setParameters($parameters);
        if ($parameters !== null) {
            for ($i = 0, $size = count($parameters); $i < $size; $i++) {
                $paramname = $parameters[$i]->getName();
                switch (strtolower($paramname)) {
                    case self::MIN_KEY:
                        $this->setMin($parameters[$i]->getValue());
                        break;
                    case self::MAX_KEY:
                        $this->setMax($parameters[$i]->getValue());
                        break;

                    default:
                        $this->setError("Invalid parameter " . $paramname);
                } // switch
            }
        }
    }

    /**
     * Checks to make sure all settings are kosher. In this case, it
     * means that the max depth is not lower than the min depth.
     *
     * {@inheritdoc}
     *
     * @return void
     */
    public function verifySettings()
    {
        if ($this->min < 0 && $this->max < 0) {
            $this->setError(
                "You must set at least one of the min or the " .
                "max levels."
            );
        }
        if ($this->max < $this->min && $this->max > -1) {
            $this->setError("The maximum depth is lower than the minimum.");
        }
    }

    /**
     * The heart of the matter. This is where the selector gets to decide
     * on the inclusion of a file in a particular fileset. Most of the work
     * for this selector is offloaded into SelectorUtils, a static class
     * that provides the same services for both FilenameSelector and
     * DirectoryScanner.
     *
     * {@inheritdoc}
     *
     * @param PhingFile $basedir base directory the scan is being done from
     * @param string $filename the name of the file to check
     * @param PhingFile $file a PhingFile object the selector can use
     *
     * @throws BuildException
     *
     * @return bool whether the file should be selected or not
     */
    public function isSelected(PhingFile $basedir, $filename, PhingFile $file)
    {

        $this->validate();

        $depth = -1;
        // If you felt daring, you could cache the basedir absolute path
        $abs_base = $basedir->getAbsolutePath();
        $abs_file = $file->getAbsolutePath();

        $tok_base = explode(DIRECTORY_SEPARATOR, $abs_base);
        $tok_file = explode(DIRECTORY_SEPARATOR, $abs_file);

        for ($i = 0, $size = count($tok_file); $i < $size; $i++) {
            $filetoken = $tok_file[$i];
            if (isset($tok_base[$i])) {
                $basetoken = $tok_base[$i];
                // Sanity check. Ditch it if you want faster performance
                if ($basetoken !== $filetoken) {
                    throw new BuildException("File " . $filename .
                        " does not appear within " . $abs_base . "directory");
                }
            } else { // no more basepath tokens
                $depth++;
                if ($this->max > -1 && $depth > $this->max) {
                    return false;
                }
            }
        }
        if (isset($tok_base[$i + 1])) {
            throw new BuildException("File " . $filename .
                " is outside of " . $abs_base . "directory tree");
        }
        if ($this->min > -1 && $depth < $this->min) {
            return false;
        }

        return true;
    }
}
