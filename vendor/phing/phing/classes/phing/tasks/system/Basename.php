<?php
/**
 *  $Id: c96657cf752403a437366423508e8df77b743109 $
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

include_once 'phing/BuildException.php';
include_once 'phing/Task.php';
include_once 'phing/system/io/PhingFile.php';
include_once 'phing/util/StringHelper.php';

/**
 * Task that changes the permissions on a file/directory.
 *
 * @author    Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package   phing.tasks.system
 */
class Basename extends Task
{
    /** @var PhingFile $file */
    private $file;

    /** @var string $property */
    private $property;

    /** @var string $suffix */
    private $suffix;

    /**
     * file or directory to get base name from
     * @param PhingFile $file file or directory to get base name from
     */
    public function setFile($file)
    {
        if (is_string($file)) {
            $this->file = new PhingFile($file);
        } else {
            $this->file = $file;
        }
    }

    /**
     * Property to set base name to.
     * @param string $property name of property
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }

    /**
     * Optional suffix to remove from base name.
     * @param string $suffix suffix to remove from base name
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
    }

    /**
     * do the work
     * @throws BuildException if required attributes are not supplied
     *                        property and attribute are required attributes
     */
    public function main()
    {
        if ($this->property === null) {
            throw new BuildException("property attribute required", $this->getLocation());
        }

        if ($this->file == null) {
            throw new BuildException("file attribute required", $this->getLocation());
        }

        $value = $this->file->getName();
        if ($this->suffix != null && StringHelper::endsWith($this->suffix, $value)) {
            // if the suffix does not starts with a '.' and the
            // char preceding the suffix is a '.', we assume the user
            // wants to remove the '.' as well
            $pos = strlen($value) - strlen($this->suffix) - 1;
            if ($pos > 0 && $this->suffix{0} !== '.' && $value{$pos} === '.') {
                $pos--;
            }
            $value = StringHelper::substring($value, 0, $pos);

        }
        $this->getProject()->setNewProperty($this->property, $value);
    }
}
