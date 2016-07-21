<?php

/*
 * $Id: 443e4a1ad090c819a7a6060a7f791d2d0d6e4469 $
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

require_once 'phing/types/selectors/BaseSelector.php';

/**
 * Selector that filters files based on whether they are newer than
 * a matching file in another directory tree. It can contain a mapper
 * element, so isn't available as an ExtendSelector (since those
 * parameters can't hold other elements).
 *
 * @author    Hans Lellelid <hans@xmpl.org> (Phing)
 * @author    Bruce Atherton <bruce@callenish.com> (Ant)
 * @version   $Id: 443e4a1ad090c819a7a6060a7f791d2d0d6e4469 $
 * @package   phing.types.selectors
 */
class DependSelector extends BaseSelector
{

    private $targetdir = null;
    private $mapperElement = null;
    private $map = null;
    private $granularity = 0;

    /**
     *
     */
    public function __construct()
    {
        // not yet supported:
        //if (Os.isFamily("dos")) {
        //    $this->granularity = 2000;
        //}
    }

    /**
     * @return string
     */
    public function toString()
    {
        $buf = "{dependselector targetdir: ";
        if ($this->targetdir === null) {
            $buf .= "NOT YET SET";
        } else {
            $buf .= $this->targetdir->getName();
        }
        $buf .= " granularity: ";
        $buf .= $this->granularity;
        if ($this->map !== null) {
            $buf .= " mapper: ";
            $buf .= $this->map->toString();
        } elseif ($this->mapperElement !== null) {
            $buf .= " mapper: ";
            $buf .= $this->mapperElement->toString();
        }
        $buf .= "}";

        return $buf;
    }

    /**
     * The name of the file or directory which is checked for out-of-date
     * files.
     *
     * @param PhingFile|the $targetdir
     * @internal param the $targetdir directory to scan looking for files.
     */
    public function setTargetdir(PhingFile $targetdir)
    {
        $this->targetdir = $targetdir;
    }

    /**
     * Sets the number of milliseconds leeway we will give before we consider
     * a file out of date.
     * @param $granularity
     */
    public function setGranularity($granularity)
    {
        $this->granularity = (int) $granularity;
    }

    /**
     * Defines the FileNameMapper to use (nested mapper element).
     * @throws BuildException
     */
    public function createMapper()
    {
        if ($this->mapperElement !== null) {
            throw new BuildException("Cannot define more than one mapper");
        }
        $this->mapperElement = new Mapper($this->project);

        return $this->mapperElement;
    }

    /**
     * Checks to make sure all settings are kosher. In this case, it
     * means that the dest attribute has been set and we have a mapper.
     */
    public function verifySettings()
    {
        if ($this->targetdir === null) {
            $this->setError("The targetdir attribute is required.");
        }
        if ($this->mapperElement === null) {
            $this->map = new IdentityMapper();
        } else {
            $this->map = $this->mapperElement->getImplementation();
        }
        if ($this->map === null) {
            $this->setError("Could not set <mapper> element.");
        }
    }

    /**
     * The heart of the matter. This is where the selector gets to decide
     * on the inclusion of a file in a particular fileset.
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

        // Determine file whose out-of-dateness is to be checked
        $destfiles = $this->map->main($filename);

        // If filename does not match the To attribute of the mapper
        // then filter it out of the files we are considering
        if ($destfiles === null) {
            return false;
        }
        // Sanity check
        if (count($destfiles) !== 1 || $destfiles[0] === null) {
            throw new BuildException("Invalid destination file results for " . $this->targetdir . " with filename " . $filename);
        }
        $destname = $destfiles[0];
        $destfile = new PhingFile($this->targetdir, $destname);

        return SelectorUtils::isOutOfDate($file, $destfile, $this->granularity);
    }
}
